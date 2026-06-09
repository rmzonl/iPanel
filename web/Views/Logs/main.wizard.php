<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title">Log İzleme</h2>
          <div class="text-muted mt-1">ZN Framework ve sistem logları</div>
        </div>
        <div class="col-auto">
          <button class="btn btn-outline-secondary btn-sm" id="btn-refresh-list" onclick="location.reload()">
            <i class="ti ti-refresh me-1"></i>Listeyi Yenile
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">

      @if(!empty($error))
        <div class="alert alert-danger">{{ $error }}</div>
      @endif
      @if(!empty($success))
        <div class="alert alert-success">{{ $success }}</div>
      @endif

      <div class="row">

        <!-- Sol: Dosya Listesi -->
        <div class="col-lg-3">

          <div class="card mb-3">
            <div class="card-header">
              <h3 class="card-title"><i class="ti ti-file-text me-2"></i>ZN Framework Logları</h3>
            </div>
            <div class="list-group list-group-flush" id="zn-log-list">
              @forelse($znLogFiles as $f)
                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center log-file-item"
                   data-source="zn" data-file="{{ $f['name'] }}">
                  <div>
                    <i class="ti ti-file me-2 text-muted"></i>
                    <span class="fw-medium">{{ $f['name'] }}</span>
                    <div class="text-muted small">{{ date('d.m.Y H:i', $f['mtime']) }}</div>
                  </div>
                  <span class="badge bg-secondary">{{ number_format($f['size'] / 1024, 1) }} KB</span>
                </a>
              @empty
                <div class="list-group-item text-muted small text-center py-3">
                  <i class="ti ti-file-off me-1"></i>Log dosyası yok
                </div>
              @endforelse
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="ti ti-server me-2"></i>Sistem Logları</h3>
            </div>
            <div class="list-group list-group-flush" id="sys-log-list">
              @forelse($sysLogFiles as $f)
                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center log-file-item"
                   data-source="sys" data-file="{{ $f['name'] }}">
                  <div>
                    <i class="ti ti-file me-2 text-muted"></i>
                    <span class="fw-medium">{{ $f['name'] }}</span>
                    <div class="text-muted small">{{ date('d.m.Y H:i', $f['mtime']) }}</div>
                  </div>
                  <span class="badge bg-secondary">{{ number_format($f['size'] / 1024, 1) }} KB</span>
                </a>
              @empty
                <div class="list-group-item text-muted small text-center py-3">
                  <i class="ti ti-file-off me-1"></i>Log dosyası yok
                </div>
              @endforelse
            </div>
          </div>

        </div>

        <!-- Sağ: Log İçeriği -->
        <div class="col-lg-9">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="card-title mb-0">
                <i class="ti ti-terminal me-2"></i>
                <span id="log-viewer-title">Dosya seçin</span>
              </h3>
              <div class="d-flex gap-2 align-items-center">
                <div class="input-group input-group-sm" style="width:220px">
                  <span class="input-group-text"><i class="ti ti-search"></i></span>
                  <input type="text" id="log-search" class="form-control" placeholder="Ara…" oninput="filterLog()">
                </div>
                <button class="btn btn-sm btn-outline-secondary" id="btn-refresh-content" onclick="reloadCurrentLog()" style="display:none">
                  <i class="ti ti-refresh"></i>
                </button>
              </div>
            </div>
            <div class="card-body p-0">
              <div id="log-placeholder" class="text-center text-muted py-5">
                <i class="ti ti-file-search fs-1 d-block mb-2 opacity-50"></i>
                Sol taraftan bir log dosyası seçin
              </div>
              <div id="log-loading" class="text-center text-muted py-5" style="display:none">
                <div class="spinner-border spinner-border-sm me-2"></div> Yükleniyor…
              </div>
              <div id="log-error" class="alert alert-danger m-3" style="display:none"></div>
              <div id="log-content" style="display:none">
                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center bg-light">
                  <small class="text-muted">Son <strong id="log-entry-count">0</strong> satır gösteriliyor</small>
                  <small class="text-muted" id="log-filter-count"></small>
                </div>
                <div style="max-height:600px;overflow-y:auto;background:#1e1e2e">
                  <table class="table table-sm table-dark mb-0 log-table" id="log-table" style="font-size:.75rem;font-family:monospace">
                    <tbody id="log-tbody"></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
let currentSource = null;
let currentFile   = null;
let allEntries    = [];

document.querySelectorAll('.log-file-item').forEach(el => {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.log-file-item').forEach(x => x.classList.remove('active'));
        this.classList.add('active');
        loadLog(this.dataset.source, this.dataset.file);
    });
});

function loadLog(source, file) {
    currentSource = source;
    currentFile   = file;

    document.getElementById('log-viewer-title').textContent = file;
    document.getElementById('log-placeholder').style.display = 'none';
    document.getElementById('log-loading').style.display     = 'block';
    document.getElementById('log-content').style.display     = 'none';
    document.getElementById('log-error').style.display       = 'none';
    document.getElementById('btn-refresh-content').style.display = '';
    document.getElementById('log-search').value = '';

    fetch(`/logs/data?source=${encodeURIComponent(source)}&file=${encodeURIComponent(file)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('log-loading').style.display = 'none';
        if (!data.success) {
            showError(data.message || 'Yükleme hatası');
            return;
        }
        allEntries = data.data.entries || [];
        renderEntries(allEntries);
        document.getElementById('log-entry-count').textContent = allEntries.length;
        document.getElementById('log-content').style.display = 'block';
    })
    .catch(err => {
        document.getElementById('log-loading').style.display = 'none';
        showError('İstek başarısız: ' + err.message);
    });
}

function renderEntries(entries) {
    const tbody = document.getElementById('log-tbody');
    tbody.innerHTML = '';

    entries.forEach((e, i) => {
        const tr = document.createElement('tr');
        if (e.subject) {
            const level = levelClass(e.subject);
            tr.innerHTML = `
              <td class="text-muted pe-2" style="white-space:nowrap;width:1%">${i + 1}</td>
              <td style="white-space:nowrap;width:1%" class="pe-2"><span class="badge ${level}">${escHtml(e.subject)}</span></td>
              <td style="white-space:nowrap;width:1%" class="text-muted pe-2">${escHtml(e.date)}</td>
              <td style="white-space:nowrap;width:1%" class="text-muted pe-2">${escHtml(e.ip)}</td>
              <td>${escHtml(e.message)}</td>`;
        } else {
            tr.innerHTML = `
              <td class="text-muted pe-2" style="white-space:nowrap;width:1%">${i + 1}</td>
              <td colspan="4">${escHtml(e.raw || '')}</td>`;
        }
        tbody.appendChild(tr);
    });

    // Sona kaydır
    const container = tbody.closest('div[style*="overflow"]');
    if (container) container.scrollTop = container.scrollHeight;
}

function filterLog() {
    const q = document.getElementById('log-search').value.toLowerCase();
    if (!q) {
        renderEntries(allEntries);
        document.getElementById('log-filter-count').textContent = '';
        return;
    }
    const filtered = allEntries.filter(e =>
        (e.raw || '').toLowerCase().includes(q) ||
        (e.message || '').toLowerCase().includes(q) ||
        (e.subject || '').toLowerCase().includes(q)
    );
    renderEntries(filtered);
    document.getElementById('log-filter-count').textContent = `${filtered.length} / ${allEntries.length} eşleşti`;
}

function reloadCurrentLog() {
    if (currentSource && currentFile) loadLog(currentSource, currentFile);
}

function showError(msg) {
    const el = document.getElementById('log-error');
    el.textContent = msg;
    el.style.display = 'block';
}

function levelClass(subject) {
    subject = subject.toLowerCase();
    if (subject.includes('error'))   return 'bg-danger';
    if (subject.includes('warning')) return 'bg-warning text-dark';
    if (subject.includes('info'))    return 'bg-info text-dark';
    return 'bg-secondary';
}

function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;');
}
</script>
