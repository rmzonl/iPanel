<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-database me-2"></i>Veritabanları</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('databases/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Yeni Veritabanı
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
            <input type="text" id="searchInput" class="form-control" placeholder="Veritabanı ara..."/>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Veritabanı</th>
                <th>Kullanıcı</th>
                <th>Site</th>
                <th>Karakter Seti</th>
                <th>Boyut (MB)</th>
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

  fetch(base + 'databases/rows', { headers: { 'X-Requested-With':'XMLHttpRequest' } })
    .then(function(r){ return r.json(); })
    .then(function(json){
      var rows = json.data || [];
      if (!rows.length) { tbody.innerHTML = emptyHtml(); return; }
      tbody.innerHTML = rows.map(renderRow).join('');
      initSearch();
    })
    .catch(function(){ tbody.innerHTML = errorHtml(); });

  function renderRow(r) {
    var status = r.status === 'active'
      ? '<span class="badge bg-success-lt">Aktif</span>'
      : '<span class="badge bg-warning-lt">Askıda</span>';
    var search = [r.db_name, r.db_user, r.site_domain].join(' ').toLowerCase();
    return '<tr data-search="' + esc(search) + '">'
      + '<td><strong class="font-monospace">' + esc(r.db_name) + '</strong></td>'
      + '<td class="text-muted font-monospace">' + esc(r.db_user) + '</td>'
      + '<td class="text-muted">' + esc(r.site_domain || '—') + '</td>'
      + '<td><span class="badge bg-secondary-lt">' + esc(r.charset||'—') + '</span></td>'
      + '<td class="text-muted">' + esc(r.size_mb || '—') + '</td>'
      + '<td>' + status + '</td>'
      + '<td><div class="btn-group btn-group-sm">'
      + '<a href="' + base + 'databases/edit/' + r.id + '" class="btn btn-outline-primary" title="Düzenle"><i class="ti ti-edit"></i></a>'
      + '<a href="' + base + 'databases/delete/' + r.id + '" class="btn btn-outline-danger" title="Sil"'
      + ' onclick="return confirm(\'Bu veritabanını silmek istediğinizden emin misiniz?\')"><i class="ti ti-trash"></i></a>'
      + '</div></td></tr>';
  }

  function emptyHtml() {
    return '<tr><td colspan="7" class="text-center py-5">'
      + '<div class="empty"><div class="empty-icon"><i class="ti ti-database" style="font-size:3rem;color:var(--tblr-muted)"></i></div>'
      + '<p class="empty-title">Henüz veritabanı yok</p>'
      + '<a href="' + base + 'databases/create" class="btn btn-primary mt-3"><i class="ti ti-plus me-1"></i> Veritabanı Ekle</a>'
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