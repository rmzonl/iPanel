<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-mail me-2"></i>E-posta Hesapları</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('email/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Yeni E-posta
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
            <input type="text" id="searchInput" class="form-control" placeholder="E-posta ara..."/>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>E-posta</th>
                <th>Site</th>
                <th>Kota (MB)</th>
                <th>Kullanım</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody id="tableBody">
              <tr>
                <td colspan="6" class="text-center py-4">
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

  fetch(base + 'email/rows', {headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){ return r.json(); })
    .then(function(json){
      var rows = json.data || [];
      if (!rows.length) { tbody.innerHTML = emptyHtml(); return; }
      tbody.innerHTML = rows.map(renderRow).join('');
      initSearch();
    })
    .catch(function(){ tbody.innerHTML = errorHtml(); });

  function renderRow(r) {
    var used = parseInt(r.used) || 0;
    var quota = parseInt(r.quota) || 0;
    var pct = quota > 0 ? Math.min(100, Math.round(used / quota * 100)) : 0;
    var barCls = pct > 90 ? 'bg-danger' : (pct > 70 ? 'bg-warning' : 'bg-primary');
    var status = r.status === 'active'
      ? '<span class="badge bg-success-lt">Aktif</span>'
      : '<span class="badge bg-warning-lt">Askıda</span>';
    var usageBar = '<div class="d-flex align-items-center gap-2">'
      + '<div class="progress flex-grow-1" style="height:6px">'
      + '<div class="progress-bar ' + barCls + '" style="width:' + pct + '%"></div>'
      + '</div><small class="text-muted">' + esc(used) + ' MB</small></div>';
    var search = [r.email, r.site_domain].join(' ').toLowerCase();
    return '<tr data-search="' + esc(search) + '">'
      + '<td><div class="d-flex align-items-center gap-2">'
      + '<span class="avatar avatar-sm bg-blue text-white"><i class="ti ti-mail"></i></span>'
      + '<strong>' + esc(r.email) + '</strong></div></td>'
      + '<td class="text-muted">' + esc(r.site_domain || '—') + '</td>'
      + '<td class="text-muted">' + esc(r.quota || '∞') + '</td>'
      + '<td style="min-width:120px">' + usageBar + '</td>'
      + '<td>' + status + '</td>'
      + '<td><div class="btn-group btn-group-sm">'
      + '<a href="' + base + 'email/edit/' + r.id + '" class="btn btn-outline-primary" title="Düzenle"><i class="ti ti-edit"></i></a>'
      + '<a href="' + base + 'email/delete/' + r.id + '" class="btn btn-outline-danger" title="Sil"'
      + ' onclick="return confirm(\'Bu e-posta hesabını silmek istediğinizden emin misiniz?\')"><i class="ti ti-trash"></i></a>'
      + '</div></td></tr>';
  }

  function emptyHtml() {
    return '<tr><td colspan="6" class="text-center py-5">'
      + '<div class="empty"><div class="empty-icon"><i class="ti ti-mail" style="font-size:3rem;color:var(--tblr-muted)"></i></div>'
      + '<p class="empty-title">Henüz e-posta hesabı yok</p>'
      + '<a href="' + base + 'email/create" class="btn btn-primary mt-3"><i class="ti ti-plus me-1"></i> E-posta Ekle</a>'
      + '</div></td></tr>';
  }

  function errorHtml() {
    return '<tr><td colspan="6" class="text-center text-danger py-4"><i class="ti ti-alert-circle me-2"></i>Liste yüklenemedi</td></tr>';
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
