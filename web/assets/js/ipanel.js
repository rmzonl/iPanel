/**
 * iPanel — Ana JavaScript Modülü
 * Toast bildirimleri, AJAX form interceptor, iş kuyruğu widget'ı
 */
(function () {
  'use strict';

  /* ============================================================
   * TOAST BİLDİRİM SİSTEMİ
   * ============================================================ */
  const Toast = (() => {
    let container = null;

    function getContainer() {
      if (!container) {
        container = document.createElement('div');
        container.id = 'ipanel-toast-container';
        container.style.cssText =
          'position:fixed;top:1rem;right:1rem;z-index:10000;display:flex;flex-direction:column;gap:.5rem;min-width:300px;max-width:420px';
        document.body.appendChild(container);
      }
      return container;
    }

    function show(message, type = 'info', duration = 4000) {
      const c = getContainer();
      const colors = {
        success: '#2fb344', error: '#d63939', warning: '#f76707', info: '#206bc4'
      };
      const icons = {
        success: 'ti-circle-check', error: 'ti-alert-circle',
        warning: 'ti-alert-triangle', info: 'ti-info-circle'
      };

      const toast = document.createElement('div');
      toast.style.cssText = `
        background:#fff;border-left:4px solid ${colors[type] || colors.info};
        border-radius:4px;box-shadow:0 4px 16px rgba(0,0,0,.12);
        padding:.75rem 1rem;display:flex;align-items:center;gap:.625rem;
        animation:ipanel-slide-in .25s ease;font-size:.875rem;
      `;
      toast.innerHTML = `
        <i class="ti ${icons[type] || icons.info}" style="color:${colors[type]};font-size:1.2rem;flex-shrink:0"></i>
        <span style="flex:1">${escapeHtml(message)}</span>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;padding:0;color:#999;font-size:1.1rem;">&times;</button>
      `;
      c.appendChild(toast);

      if (duration > 0) {
        setTimeout(() => toast.remove(), duration);
      }
      return toast;
    }

    return {
      success: (m, d) => show(m, 'success', d),
      error:   (m, d) => show(m, 'error',   d),
      warning: (m, d) => show(m, 'warning', d),
      info:    (m, d) => show(m, 'info',    d),
    };
  })();

  window.Toast = Toast;

  /* ============================================================
   * AJAX FORM INTERCEPTOR
   * data-ajax="true" olan formları fetch ile gönderir.
   * Sunucudan beklenen JSON: {success, message, redirect?, job_uuid?}
   * ============================================================ */
  document.addEventListener('submit', async function (e) {
    const form = e.target.closest('form[data-ajax]');
    if (!form) return;
    e.preventDefault();

    const btn = form.querySelector('[type=submit]');
    const originalText = btn ? btn.innerHTML : '';
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>İşleniyor…';
    }

    // Inline hata mesajlarını temizle
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

    try {
      const fd  = new FormData(form);
      const res = await fetch(form.action || window.location.href, {
        method:  form.method || 'POST',
        body:    fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });

      const json = await res.json();

      if (json.success) {
        Toast.success(json.message || 'İşlem başarılı.');

        // Kuyruğa eklendiyse job widget'ını güncelle
        if (json.job_uuid) {
          JobWidget.trackJob(json.job_uuid, json.message);
        }

        if (json.redirect) {
          setTimeout(() => { window.location.href = json.redirect; }, 800);
        } else if (form.dataset.resetOnSuccess !== 'false') {
          form.reset();
          form.querySelectorAll('.select2-hidden-accessible')
              .forEach(el => { if (window.jQuery) jQuery(el).trigger('change'); });
        }

        // Özel callback
        if (form.dataset.onSuccess) {
          const cb = window[form.dataset.onSuccess];
          if (typeof cb === 'function') cb(json);
        }
      } else {
        Toast.error(json.message || 'Bir hata oluştu.');
        // Inline alan hataları
        if (json.errors) {
          for (const [field, msg] of Object.entries(json.errors)) {
            const el = form.querySelector(`[name="${field}"]`);
            if (el) {
              el.classList.add('is-invalid');
              const fb = document.createElement('div');
              fb.className = 'invalid-feedback';
              fb.textContent = msg;
              el.insertAdjacentElement('afterend', fb);
            }
          }
        }
      }
    } catch (err) {
      Toast.error('Sunucuya bağlanılamadı. Lütfen tekrar deneyin.');
      console.error('[iPanel AJAX]', err);
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    }
  });

  /* ============================================================
   * İŞ KUYRUĞU SIDEBAR WİDGET'I (Plesk tarzı)
   * ============================================================ */
  const JobWidget = (() => {
    let panel, badge, list;
    let pollingTimer = null;
    const tracked = new Map();  // uuid → {toastEl, type}

    function init() {
      // Sidebar'ın altına "Çalışan İşler" butonu ekle
      const nav = document.querySelector('.navbar-nav.pt-lg-3');
      if (!nav) return;

      // Badge + toggle butonu
      const li = document.createElement('li');
      li.className = 'nav-item';
      li.innerHTML = `
        <a href="#" class="nav-link" id="job-widget-toggle">
          <span class="nav-link-icon d-md-none d-lg-block">
            <i class="ti ti-queue"></i>
          </span>
          <span class="nav-link-title">Görev Kuyruğu</span>
          <span id="job-badge" class="badge bg-azure ms-auto" style="display:none">0</span>
        </a>
      `;
      nav.appendChild(li);
      badge = document.getElementById('job-badge');

      // Açılır panel
      panel = document.createElement('div');
      panel.id = 'job-panel';
      panel.style.cssText = `
        position:fixed;bottom:0;left:248px;width:340px;background:#fff;
        border:1px solid #e6e7e9;border-bottom:none;border-radius:8px 8px 0 0;
        box-shadow:0 -4px 20px rgba(0,0,0,.08);z-index:1050;display:none;
      `;
      panel.innerHTML = `
        <div style="padding:.75rem 1rem;border-bottom:1px solid #e6e7e9;display:flex;align-items:center;justify-content:space-between;background:#f8f9fa;border-radius:8px 8px 0 0;">
          <strong style="font-size:.875rem"><i class="ti ti-queue me-2 text-azure"></i>Görev Kuyruğu</strong>
          <div>
            <a href="/jobs/main" style="font-size:.75rem;margin-right:.5rem">Tümü</a>
            <button id="job-panel-close" style="background:none;border:none;cursor:pointer;padding:0;color:#999">&times;</button>
          </div>
        </div>
        <div id="job-list" style="max-height:280px;overflow-y:auto;padding:.5rem"></div>
      `;
      document.body.appendChild(panel);
      list = document.getElementById('job-list');

      document.getElementById('job-widget-toggle').addEventListener('click', e => {
        e.preventDefault();
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
      });
      document.getElementById('job-panel-close').addEventListener('click', () => {
        panel.style.display = 'none';
      });

      startPolling();
    }

    function startPolling() {
      fetchJobs();
      pollingTimer = setInterval(fetchJobs, 4000);
    }

    async function fetchJobs() {
      try {
        const res  = await fetch('/jobs/active', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json();
        if (!json.success) return;

        const count = json.data.active_count || 0;
        if (badge) {
          badge.textContent = count;
          badge.style.display = count > 0 ? 'inline' : 'none';
        }

        renderJobs(json.data.jobs || []);

        // Takip edilen işlerin durumunu kontrol et
        for (const [uuid, info] of tracked.entries()) {
          const job = (json.data.jobs || []).find(j => j.uuid === uuid);
          if (!job || job.status === 'completed') {
            Toast.success(`✓ ${info.type} tamamlandı.`);
            tracked.delete(uuid);
          } else if (job.status === 'failed') {
            Toast.error(`✗ ${info.type} başarısız oldu.`);
            tracked.delete(uuid);
          }
        }
      } catch (e) { /* sessiz hata */ }
    }

    function renderJobs(jobs) {
      if (!list) return;
      if (jobs.length === 0) {
        list.innerHTML = '<p class="text-muted text-center py-3" style="font-size:.8rem">Aktif görev yok</p>';
        return;
      }
      const statusColor = { pending: '#999', running: '#206bc4', completed: '#2fb344', failed: '#d63939', cancelled: '#666' };
      const statusLabel = { pending: 'Bekliyor', running: 'Çalışıyor', completed: 'Tamamlandı', failed: 'Başarısız', cancelled: 'İptal' };
      list.innerHTML = jobs.map(j => `
        <div style="padding:.5rem .75rem;border-bottom:1px solid #f1f3f5;font-size:.8rem">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="font-weight:500">${escapeHtml(j.type)}</span>
            <span style="color:${statusColor[j.status]};font-size:.7rem">${statusLabel[j.status] || j.status}</span>
          </div>
          <div style="color:#888;font-size:.7rem;margin-top:2px">${j.created_at}</div>
        </div>
      `).join('');
    }

    function trackJob(uuid, type) {
      tracked.set(uuid, { type });
      if (panel) panel.style.display = 'block';
    }

    return { init, trackJob };
  })();

  /* ============================================================
   * YARDIMCI FONKSİYONLAR
   * ============================================================ */
  function escapeHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
  }

  // Klavye kısayolları
  document.addEventListener('keydown', e => {
    if (e.altKey && e.key === 'j') {
      const t = document.getElementById('job-widget-toggle');
      if (t) t.click();
    }
  });

  // CSS animasyonu
  const style = document.createElement('style');
  style.textContent = `
    @keyframes ipanel-slide-in {
      from { transform: translateX(20px); opacity: 0; }
      to   { transform: translateX(0);    opacity: 1; }
    }
  `;
  document.head.appendChild(style);

  // DOMContentLoaded sonrasında init
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => JobWidget.init());
  } else {
    JobWidget.init();
  }

  // Flash mesajları otomatik kapat (mevcut sunucu-taraflı olanlar)
  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
      document.querySelectorAll('.alert-flash').forEach(el => {
        el.style.transition = 'opacity .5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
      });
    }, 4000);
  });

})();
