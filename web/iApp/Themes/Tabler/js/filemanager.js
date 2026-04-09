/**
 * iPanel — Dosya Yöneticisi JS
 * Import::usable AJAX pattern + ZN JsonResponse entegrasyonu
 */
(function (FM) {
  'use strict';

  // ── Mevcut yol state'i ────────────────────────────────
  FM.currentPath = document.querySelector('[data-fm-path]')
    ? document.querySelector('[data-fm-path]').dataset.fmPath
    : '/home';

  // ── CSRF token (meta'dan al + yanıtta yenile) ────────
  function csrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }
  function updateCsrf(json) {
    if (json && json._csrf) {
      var m = document.querySelector('meta[name="csrf-token"]');
      if (m) m.setAttribute('content', json._csrf);
    }
  }

  // ── Genel POST yardımcısı ─────────────────────────────
  function post(url, data, onSuccess, onError) {
    data._csrf = csrfToken();
    fetch(url, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(data).toString()
    })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        updateCsrf(json);
        if (json.success) {
          if (typeof onSuccess === 'function') onSuccess(json);
        } else {
          Toast.error(json.message || 'Hata oluştu.');
          if (typeof onError === 'function') onError(json);
        }
      })
      .catch(function () { Toast.error('Sunucuya bağlanılamadı.'); });
  }

  // ── Genel GET yardımcısı ──────────────────────────────
  function get(url, onSuccess) {
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        updateCsrf(json);
        if (json.success) {
          if (typeof onSuccess === 'function') onSuccess(json);
        } else {
          Toast.error(json.message || 'Hata oluştu.');
        }
      })
      .catch(function () { Toast.error('Sunucuya bağlanılamadı.'); });
  }

  // ── Import::usable HTML inject ────────────────────────
  function injectList(html, newPath) {
    var container = document.getElementById('fm-list-container');
    if (!container) return;
    container.innerHTML = html;
    // Gömülü script varsa eval et
    container.querySelectorAll('script').forEach(function (s) {
      var ns = document.createElement('script');
      ns.textContent = s.textContent;
      document.head.appendChild(ns).parentNode.removeChild(ns);
    });
    if (newPath) {
      FM.currentPath = newPath;
      updateBreadcrumb(newPath);
    }
    resetCheckboxes();
  }

  // ── Breadcrumb güncelle ───────────────────────────────
  function updateBreadcrumb(path) {
    var ol = document.querySelector('nav[aria-label="breadcrumb"] ol');
    if (!ol) return;
    var parts = path.replace(/\/+$/, '').split('/').filter(Boolean);
    var html = '<li class="breadcrumb-item"><a href="#" onclick="fmBrowse(\'/\'); return false;">/</a></li>';
    var acc = '';
    parts.forEach(function (p, i) {
      acc += '/' + p;
      var safePath = acc;
      if (i === parts.length - 1) {
        html += '<li class="breadcrumb-item active">' + escHtml(p) + '</li>';
      } else {
        html += '<li class="breadcrumb-item"><a href="#" onclick="fmBrowse(\'' + escJs(safePath) + '\'); return false;">' + escHtml(p) + '</a></li>';
      }
    });
    ol.innerHTML = html;
  }

  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function escJs(s) {
    return String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'");
  }

  // ── Checkbox yönetimi ─────────────────────────────────
  function resetCheckboxes() {
    var bar = document.getElementById('fm-bulk-bar');
    if (bar) bar.classList.add('d-none');
    var all = document.getElementById('fm-check-all');
    if (all) all.checked = false;
  }

  // Genel: onCheckboxChange (event delegation)
  document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('fm-row-check') && e.target.id !== 'fm-check-all') return;
    var count = document.querySelectorAll('.fm-row-check:checked').length;
    var bar = document.getElementById('fm-bulk-bar');
    var cnt = document.getElementById('fm-selected-count');
    if (bar) bar.classList.toggle('d-none', count === 0);
    if (cnt) cnt.textContent = count + ' seçili';
  });

  // ── Sürükle-bırak yükleme ─────────────────────────────
  var container = document.getElementById('fm-list-container');
  if (container) {
    container.addEventListener('dragover', function (e) { e.preventDefault(); container.classList.add('fm-drag-over'); });
    container.addEventListener('dragleave', function () { container.classList.remove('fm-drag-over'); });
    container.addEventListener('drop', function (e) {
      e.preventDefault();
      container.classList.remove('fm-drag-over');
      if (e.dataTransfer.files.length) fmUpload(e.dataTransfer.files);
    });
  }

  // ────────────────────────────────────────────────────────
  // Public API — global fonksiyonlar
  // ────────────────────────────────────────────────────────

  /** Dizin aç — Import::usable ile list.wizard.php HTML döner */
  window.fmBrowse = function (path) {
    var lc = document.getElementById('fm-list-container');
    if (lc) lc.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-muted"></div></div>';
    get('/filemanager/browse?path=' + encodeURIComponent(path), function (json) {
      injectList(json.html, json.message);
    });
  };

  /** Editörü aç — Import::usable ile editor.wizard.php HTML döner */
  window.fmEdit = function (path) {
    var body = document.getElementById('editor-body');
    if (body) body.innerHTML = '<div class="d-flex align-items-center justify-content-center py-5"><div class="spinner-border text-muted"></div></div>';
    var modal = new bootstrap.Modal(document.getElementById('modal-editor'));
    modal.show();
    get('/filemanager/editor?path=' + encodeURIComponent(path), function (json) {
      if (body) {
        body.innerHTML = json.html;
        // editor.wizard.php içindeki Script::compress çıktısını eval et
        body.querySelectorAll('script').forEach(function (s) {
          var ns = document.createElement('script');
          ns.textContent = s.textContent;
          document.head.appendChild(ns).parentNode.removeChild(ns);
        });
      }
    });
  };

  /** Editörden kaydet */
  window.fmEditorSave = function () {
    if (!window._fmEditor || !window._fmEditorPath) return;
    var content = window._fmEditor.getValue();
    post('/filemanager/save', { path: window._fmEditorPath, content: content }, function (json) {
      Toast.success(json.message || 'Kaydedildi.');
    });
  };

  /** Dosya indir */
  window.fmDownload = function (path) {
    window.location.href = '/filemanager/download?path=' + encodeURIComponent(path);
  };

  /** Sil */
  window.fmDelete = function (path, isDir) {
    var name = path.split('/').pop();
    var msg = isDir === '1' ? '"' + name + '" klasörü ve içeriği silinecek. Emin misiniz?' : '"' + name + '" dosyası silinecek. Emin misiniz?';
    if (!confirm(msg)) return;
    post('/filemanager/delete', { path: path }, function (json) {
      Toast.success(json.message);
      fmBrowse(json.data.path || FM.currentPath);
    });
  };

  /** Toplu sil */
  window.fmBulkDelete = function () {
    var checked = Array.from(document.querySelectorAll('.fm-row-check:checked')).map(function (c) { return c.value; });
    if (!checked.length) return;
    if (!confirm(checked.length + ' dosya/klasör silinecek. Emin misiniz?')) return;
    var i = 0;
    function deleteNext() {
      if (i >= checked.length) { fmBrowse(FM.currentPath); return; }
      var p = checked[i++];
      var row = document.querySelector('tr[data-path="' + p.replace(/"/g, '\\"') + '"]');
      var isDir = row ? row.dataset.isdir : '0';
      post('/filemanager/delete', { path: p }, deleteNext, deleteNext);
    }
    deleteNext();
  };

  /** Yeniden adlandır modal */
  window.fmRename = function (path, name) {
    document.getElementById('rename-path').value = path;
    document.getElementById('rename-input').value = name;
    new bootstrap.Modal(document.getElementById('modal-rename')).show();
  };
  window.fmRenameSubmit = function () {
    var path = document.getElementById('rename-path').value;
    var name = document.getElementById('rename-input').value.trim();
    if (!name) return;
    bootstrap.Modal.getInstance(document.getElementById('modal-rename')).hide();
    post('/filemanager/rename', { path: path, name: name }, function (json) {
      Toast.success(json.message);
      fmBrowse(json.data.path || FM.currentPath);
    });
  };

  /** chmod modal */
  window.fmChmod = function (path, current) {
    document.getElementById('chmod-path').value = path;
    document.getElementById('chmod-input').value = current;
    new bootstrap.Modal(document.getElementById('modal-chmod')).show();
  };
  window.fmChmodSubmit = function () {
    var path = document.getElementById('chmod-path').value;
    var mode = document.getElementById('chmod-input').value.trim();
    bootstrap.Modal.getInstance(document.getElementById('modal-chmod')).hide();
    post('/filemanager/chmod', { path: path, mode: mode }, function (json) {
      Toast.success(json.message);
      fmBrowse(FM.currentPath);
    });
  };

  /** mkdir modal */
  window.fmMkdir = function () {
    document.getElementById('mkdir-input').value = '';
    new bootstrap.Modal(document.getElementById('modal-mkdir')).show();
  };
  window.fmMkdirSubmit = function () {
    var name = document.getElementById('mkdir-input').value.trim();
    if (!name) return;
    bootstrap.Modal.getInstance(document.getElementById('modal-mkdir')).hide();
    post('/filemanager/mkdir', { path: FM.currentPath, name: name }, function (json) {
      Toast.success(json.message);
      fmBrowse(json.data.path || FM.currentPath);
    });
  };

  /** Yeni dosya modal */
  window.fmNewFile = function () {
    document.getElementById('newfile-input').value = '';
    new bootstrap.Modal(document.getElementById('modal-newfile')).show();
  };
  window.fmNewFileSubmit = function () {
    var name = document.getElementById('newfile-input').value.trim();
    if (!name) return;
    bootstrap.Modal.getInstance(document.getElementById('modal-newfile')).hide();
    post('/filemanager/newfile', { path: FM.currentPath, name: name }, function (json) {
      Toast.success(json.message);
      fmBrowse(json.data.path || FM.currentPath);
      // Editörü aç (oluşturulan dosya)
      if (json.data.file) setTimeout(function () { fmEdit(json.data.file); }, 400);
    });
  };

  /** Kopyala modal */
  window.fmCopy = function (path) {
    document.getElementById('copy-src').value = path;
    document.getElementById('copy-dest').value = FM.currentPath;
    new bootstrap.Modal(document.getElementById('modal-copy')).show();
  };
  window.fmCopySubmit = function () {
    var src  = document.getElementById('copy-src').value;
    var dest = document.getElementById('copy-dest').value.trim();
    bootstrap.Modal.getInstance(document.getElementById('modal-copy')).hide();
    post('/filemanager/copy', { path: src, dest: dest }, function (json) {
      Toast.success(json.message);
      fmBrowse(json.data.path || FM.currentPath);
    });
  };

  /** Sıkıştır */
  window.fmCompress = function (path) {
    if (!confirm('"' + path.split('/').pop() + '" zip dosyasına sıkıştırılacak.')) return;
    post('/filemanager/compress', { path: path }, function (json) {
      Toast.success(json.message);
      fmBrowse(json.data.path || FM.currentPath);
    });
  };

  /** Zip aç */
  window.fmExtract = function (path) {
    if (!confirm('"' + path.split('/').pop() + '" zip dosyası açılacak.')) return;
    post('/filemanager/extract', { path: path }, function (json) {
      Toast.success(json.message);
      fmBrowse(json.data.path || FM.currentPath);
    });
  };

  /** Checkbox hepsini seç/kaldır */
  window.fmCheckAll = function (master) {
    document.querySelectorAll('.fm-row-check').forEach(function (c) { c.checked = master.checked; });
    var count = master.checked ? document.querySelectorAll('.fm-row-check').length : 0;
    var bar = document.getElementById('fm-bulk-bar');
    var cnt = document.getElementById('fm-selected-count');
    if (bar) bar.classList.toggle('d-none', count === 0);
    if (cnt) cnt.textContent = count + ' seçili';
  };

  /** Dosya yükleme */
  window.fmUpload = function (files) {
    if (!files || !files.length) return;
    var fd = new FormData();
    fd.append('_csrf', csrfToken());
    fd.append('path', FM.currentPath);
    for (var i = 0; i < files.length; i++) fd.append('files[]', files[i]);

    var progress  = document.getElementById('fm-upload-progress');
    var bar       = document.getElementById('fm-progress-bar');
    var label     = document.getElementById('fm-progress-label');
    if (progress) progress.style.display = '';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/filemanager/upload');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.upload.onprogress = function (e) {
      if (!e.lengthComputable) return;
      var pct = Math.round((e.loaded / e.total) * 100);
      if (bar)   bar.style.width = pct + '%';
      if (label) label.textContent = 'Yükleniyor… ' + pct + '%';
    };
    xhr.onload = function () {
      if (progress) progress.style.display = 'none';
      try {
        var json = JSON.parse(xhr.responseText);
        updateCsrf(json);
        if (json.success) {
          Toast.success(json.message);
          fmBrowse(FM.currentPath);
        } else {
          Toast.error(json.message || 'Yükleme başarısız.');
        }
      } catch (ex) { Toast.error('Sunucu yanıtı geçersiz.'); }
    };
    xhr.onerror = function () {
      if (progress) progress.style.display = 'none';
      Toast.error('Yükleme sırasında ağ hatası.');
    };
    xhr.send(fd);

    // input'u sıfırla (aynı dosya tekrar seçilebilsin)
    var inp = document.getElementById('fm-upload-input');
    if (inp) inp.value = '';
  };

  // ── Drag-over CSS (dinamik ekle) ─────────────────────
  var style = document.createElement('style');
  style.textContent = '#fm-list-container.fm-drag-over { outline: 2px dashed var(--tblr-primary); outline-offset:-4px; background: rgba(var(--tblr-primary-rgb),.04); }';
  document.head.appendChild(style);

}(window.FM = window.FM || {}));
