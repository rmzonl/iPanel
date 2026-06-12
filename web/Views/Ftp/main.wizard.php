<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-folder me-2"></i>FTP Hesapları</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('ftp/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Yeni FTP
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
            <input type="text" id="searchInput" class="form-control" placeholder="FTP hesabı ara..."/>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Kullanıcı</th>
                <th>Site</th>
                <th>Home Dizin</th>
                <th>Kota (MB)</th>
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

  fetch(base + 'ftp/rows', { headers: { 'X-Requested-With':'XMLHttpRequest' } })
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
    var search = [r.username, r.site_domain, r.home_dir].join(' ').toLowerCase();
    return '<tr data-search="' + esc(search) + '">'
      + '<td><strong>' + esc(r.username) + '</strong></td>'
      + '<td class="text-muted">' + esc(r.site_domain || '—') + '</td>'
      + '<td class="text-muted font-monospace">' + esc(r.home_dir || '—') + '</td>'
      + '<td class="text-muted">' + esc(r.quota || '∞') + '</td>'
      + '<td>' + status + '</td>'
      + '<td><div class="btn-group btn-group-sm">'
      + '<a href="' + base + 'ftp/edit/' + r.id + '" class="btn btn-outline-primary" title="Düzenle"><i class="ti ti-edit"></i></a>'
      + '<a href="' + base + 'ftp/delete/' + r.id + '" class="btn btn-outline-danger" title="Sil"'
      + ' onclick="return confirm(\'Bu FTP hesabını silmek istediğinizden emin misiniz?\')"><i class="ti ti-trash"></i></a>'
      + '</div></td></tr>';
  }

  function emptyHtml() {
    return '<tr><td colspan="6" class="text-center py-5">'
      + '<div class="empty"><div class="empty-icon"><i class="ti ti-folder" style="font-size:3rem;color:var(--tblr-muted)"></i></div>'
      + '<p class="empty-title">Henüz FTP hesabı yok</p>'
      + '<a href="' + base + 'ftp/create" class="btn btn-primary mt-3"><i class="ti ti-plus me-1"></i> FTP Ekle</a>'
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