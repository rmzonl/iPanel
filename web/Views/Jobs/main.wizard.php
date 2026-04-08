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
              @forelse($jobs as $job)
                {[ $statusColors = ['pending'=>'secondary','running'=>'azure','completed'=>'success','failed'=>'danger','cancelled'=>'muted'];
                   $statusLabels = ['pending'=>'Bekliyor','running'=>'Çalışıyor','completed'=>'Tamamlandı','failed'=>'Başarısız','cancelled'=>'İptal'];
                   $sc = $statusColors[$job->status] ?? 'secondary';
                   $sl = $statusLabels[$job->status] ?? $job->status;
                ]}
                <tr data-status="{{ $job->status }}">
                  <td><code class="small">{{ substr($job->uuid, 0, 8) }}…</code></td>
                  <td><span class="badge bg-blue-lt">{{ $job->type }}</span></td>
                  <td><span class="badge bg-{{ $sc }}-lt text-{{ $sc }}">{{ $sl }}</span></td>
                  <td class="text-muted">{{ $job->username ?? '—' }}</td>
                  <td class="text-muted small">{{ $job->created_at }}</td>
                  <td class="text-muted small">{{ $job->started_at ?? '—' }}</td>
                  <td class="text-muted small">{{ $job->completed_at ?? '—' }}</td>
                  <td>
                    @if(in_array($job->status, ['pending']))
                      <button class="btn btn-sm btn-ghost-danger"
                              onclick="cancelJob('{{ $job->uuid }}')">
                        <i class="ti ti-x"></i>
                      </button>
                    @endif
                    @if($job->result)
                      <button class="btn btn-sm btn-ghost-secondary"
                              onclick="showResult('{{ addslashes($job->result) }}')">
                        <i class="ti ti-eye"></i>
                      </button>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="8" class="text-center text-muted py-5">Görev bulunamadı</td></tr>
              @endforelse
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

// Otomatik yenileme (çalışan iş varsa her 5 sn)
(function autoRefresh() {
  const hasActive = [...document.querySelectorAll('#jobs-tbody tr[data-status="running"], #jobs-tbody tr[data-status="pending"]')].length > 0;
  if (hasActive) setTimeout(() => location.reload(), 5000);
  document.getElementById('jobs-last-refresh').textContent = 'Son: ' + new Date().toLocaleTimeString('tr-TR');
})();
</script>
