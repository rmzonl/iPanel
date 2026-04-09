<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-queue me-2"></i>Görev Kuyruğu</h2>
          <div class="text-muted mt-1">
            Aktif: <strong id="active-count">{{ $activeCount }}</strong>
            işlem &nbsp;·&nbsp; <span id="jobs-last-refresh" class="text-muted small">—</span>
          </div>
        </div>
        <div class="col-auto">
          <div class="input-group input-group-sm">
            <select id="status-filter" class="form-select form-select-sm" onchange="filterJobs(this.value)">
              <option value="">Tümü</option>
              <option value="pending">Bekliyor</option>
              <option value="running">Çalışıyor</option>
              <option value="completed">Tamamlandı</option>
              <option value="failed">Başarısız</option>
              <option value="cancelled">İptal</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="table-responsive">
          <table class="table table-vcenter card-table" id="jobs-table">
            <thead>
              <tr>
                <th>UUID</th>
                <th>Tür</th>
                <th>Durum</th>
                <th>Kullanıcı</th>
                <th>Oluşturuldu</th>
                <th>Başladı</th>
                <th>Tamamlandı</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="jobs-tbody">
              @view('Jobs/rows')
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Sonuç modal -->
<div class="modal modal-blur fade" id="result-modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Görev Sonucu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <pre id="result-pre" class="bg-dark text-white p-3 rounded" style="max-height:400px;overflow:auto;font-size:.8rem"></pre>
      </div>
    </div>
  </div>
</div>

<script>
function filterJobs(status) {
  document.querySelectorAll('#jobs-tbody tr[data-status]').forEach(tr => {
    tr.style.display = (!status || tr.dataset.status === status) ? '' : 'none';
  });
}

async function cancelJob(uuid) {
  if (!confirm('Bu görevi iptal etmek istiyor musunuz?')) return;
  try {
    const fd = new FormData();
    fd.append('uuid', uuid);
    const res  = await fetch('/jobs/cancel', { method: 'POST', body: fd, headers: {'X-Requested-With':'XMLHttpRequest'} });
    const json = await res.json();
    if (json.success) { Toast.success(json.message); setTimeout(() => location.reload(), 800); }
    else Toast.error(json.message);
  } catch(e) { Toast.error('İstek başarısız.'); }
}

function showResult(raw) {
  try {
    const data = JSON.parse(raw);
    document.getElementById('result-pre').textContent = JSON.stringify(data, null, 2);
  } catch {
    document.getElementById('result-pre').textContent = raw;
  }
  bootstrap.Modal.getOrCreateInstance(document.getElementById('result-modal')).show();
}

// Otomatik yenileme — Import::usable/refreshRows endpoint ile (location.reload yerine)
(function autoRefresh() {
  function updateTimestamp() {
    document.getElementById('jobs-last-refresh').textContent = 'Son: ' + new Date().toLocaleTimeString('tr-TR');
  }

  function hasActiveJobs() {
    return document.querySelectorAll('#jobs-tbody tr[data-status="running"], #jobs-tbody tr[data-status="pending"]').length > 0;
  }

  async function refresh() {
    const filter = document.getElementById('status-filter').value;
    const url    = '/jobs/refreshrows' + (filter ? '?status=' + encodeURIComponent(filter) : '');
    try {
      const res  = await fetch(url, { headers: {'X-Requested-With':'XMLHttpRequest'} });
      const json = await res.json();
      if (json.success && json.html !== undefined) {
        document.getElementById('jobs-tbody').innerHTML = json.html;
        filterJobs(filter);
        // Sayfa içinde script varsa eval edilir
        const scripts = document.getElementById('jobs-tbody').querySelectorAll('script');
        scripts.forEach(s => eval(s.textContent));
      }
    } catch(e) { /* sessiz hata */ }
    updateTimestamp();

    // Aktif iş varsa 5 sn sonra tekrar yenile
    if (hasActiveJobs()) setTimeout(refresh, 5000);
  }

  updateTimestamp();
  if (hasActiveJobs()) setTimeout(refresh, 5000);
})();
</script>
