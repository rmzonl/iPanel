<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-clock me-2"></i>Cron İşleri</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('cronjobs/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Yeni Cron
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-header">
          <div class="input-group input-group-sm" style="max-width:300px">
            <span class="input-group-text"><i class="ti ti-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Cron ara..."/>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Başlık</th>
                <th>Site</th>
                <th>Zamanlama</th>
                <th>Komut</th>
                <th>Son Çalışma</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody id="tableBody">
              <tr>
                <td colspan="7" class="text-center py-4">
                  <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                  <span class="ms-2 text-muted">Yükleniyor...</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  var base = '{{ URL::base("") }}';
  var tbody = document.getElementById('tableBody');

  fetch(base + 'cronjobs/rows', { headers: { 'X-Requested-With':'XMLHttpRequest' } })
    .then(function(r){ return r.json(); })
    .then(function(json){
      var rows = json.data || [];
      if (!rows.length) { tbody.innerHTML = emptyHtml(); return; }
      tbody.innerHTML = rows.map(renderRow).join('');
      initSearch();
    })
    .catch(function(){ tbody.innerHTML = errorHtml(); });

  function renderRow(r) {
    var lastRun = r.last_run ? fmtDate(r.last_run) : 'Henüz çalışmadı';
    var status = r.status === 'active'
      ? '<span class="badge bg-success-lt">Aktif</span>'
      : '<span class="badge bg-secondary-lt">Pasif</span>';
    var cmd = esc(r.command || '');
    var search = [r.title, r.site_domain, r.schedule, r.command].join(' ').toLowerCase();
    return '<tr data-search="' + esc(search) + '">'
      + '<td><strong>' + esc(r.title) + '</strong></td>'
      + '<td class="text-muted">' + esc(r.site_domain || '—') + '</td>'
      + '<td><code class="bg-secondary-lt px-2 py-1 rounded">' + esc(r.schedule) + '</code></td>'
      + '<td class="text-muted font-monospace small" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + cmd + '</td>'
      + '<td class="text-muted small">' + esc(lastRun) + '</td>'
      + '<td>' + status + '</td>'
      + '<td><div class="btn-group btn-group-sm">'
      + '<a href="' + base + 'cronjobs/toggle/' + r.id + '" class="btn btn-outline-secondary" title="Aç/Kapat"><i class="ti ti-power"></i></a>'
      + '<a href="' + base + 'cronjobs/delete/' + r.id + '" class="btn btn-outline-danger" title="Sil"'
      + ' onclick="return confirm(\'Bu cron işini silmek istediğinizden emin misiniz?\')"><i class="ti ti-trash"></i></a>'
      + '</div></td></tr>';
  }

  function fmtDate(s) {
    var d = new Date(s.replace(' ', 'T'));
    return d.toLocaleDateString('tr-TR') + ' ' + d.toLocaleTimeString('tr-TR', {hour:'2-digit',minute:'2-digit'});
  }

  function emptyHtml() {
    return '<tr><td colspan="7" class="text-center py-5">'
      + '<div class="empty"><div class="empty-icon"><i class="ti ti-clock" style="font-size:3rem;color:var(--tblr-muted)"></i></div>'
      + '<p class="empty-title">Henüz cron işi yok</p>'
      + '<a href="' + base + 'cronjobs/create" class="btn btn-primary mt-3"><i class="ti ti-plus me-1"></i> Cron Ekle</a>'
      + '</div></td></tr>';
  }

  function errorHtml() {
    return '<tr><td colspan="7" class="text-center text-danger py-4"><i class="ti ti-alert-circle me-2"></i>Liste yüklenemedi</td></tr>';
  }

  function esc(v) {
    return String(v == null ? '' : v)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function initSearch() {
    document.getElementById('searchInput').addEventListener('keyup', function() {
      var val = this.value.toLowerCase();
      tbody.querySelectorAll('tr[data-search]').forEach(function(row) {
        row.style.display = row.dataset.search.includes(val) ? '' : 'none';
      });
    });
  }
})();
</script>