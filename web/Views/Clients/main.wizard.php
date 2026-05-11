<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-users me-2"></i>Müşteriler</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('clients/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Yeni Müşteri
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
            <input type="text" id="searchInput" class="form-control" placeholder="Müşteri ara..."/>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Ad Soyad</th>
                <th>Firma</th>
                <th>E-posta</th>
                <th>Telefon</th>
                <th>Durum</th>
                <th>Kayıt</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody id="tableBody">
              <tr>
                <td colspan="8" class="text-center py-4">
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

  fetch(base + 'clients/rows', {headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){ return r.json(); })
    .then(function(json){
      var rows = json.data || [];
      if (!rows.length) { tbody.innerHTML = emptyHtml(); return; }
      tbody.innerHTML = rows.map(renderRow).join('');
      initSearch();
    })
    .catch(function(){ tbody.innerHTML = errorHtml(); });

  function renderRow(r) {
    var initials = (r.first_name||'').charAt(0).toUpperCase() + (r.last_name||'').charAt(0).toUpperCase();
    var statusBadge = r.status === 'active'
      ? '<span class="badge bg-success-lt">Aktif</span>'
      : (r.status === 'suspended'
        ? '<span class="badge bg-warning-lt">Askıya Alındı</span>'
        : '<span class="badge bg-danger-lt">Sonlandırıldı</span>');
    var date = r.created_at ? r.created_at.substring(0,10).split('-').reverse().join('.') : '—';
    var search = [r.id, r.first_name, r.last_name, r.company_name, r.email, r.phone].join(' ').toLowerCase();
    return '<tr data-search="' + esc(search) + '">'
      + '<td class="text-muted">' + esc(r.id) + '</td>'
      + '<td><div class="d-flex align-items-center gap-2">'
      + '<span class="avatar avatar-sm bg-primary text-white">' + esc(initials) + '</span>'
      + esc((r.first_name||'') + ' ' + (r.last_name||''))
      + '</div></td>'
      + '<td class="text-muted">' + esc(r.company_name || '—') + '</td>'
      + '<td>' + esc(r.email||'—') + '</td>'
      + '<td class="text-muted">' + esc(r.phone || '—') + '</td>'
      + '<td>' + statusBadge + '</td>'
      + '<td class="text-muted">' + esc(date) + '</td>'
      + '<td><div class="btn-group btn-group-sm">'
      + '<a href="' + base + 'sites/main?client_id=' + r.id + '" class="btn btn-outline-secondary" title="Siteler"><i class="ti ti-world"></i></a>'
      + '<a href="' + base + 'clients/edit/' + r.id + '" class="btn btn-outline-primary" title="Düzenle"><i class="ti ti-edit"></i></a>'
      + '<a href="' + base + 'clients/delete/' + r.id + '" class="btn btn-outline-danger" title="Sil"'
      + ' onclick="return confirm(\'Bu müşteriyi silmek istediğinizden emin misiniz?\')"><i class="ti ti-trash"></i></a>'
      + '</div></td></tr>';
  }

  function emptyHtml() {
    return '<tr><td colspan="8" class="text-center py-5">'
      + '<div class="empty"><div class="empty-icon"><i class="ti ti-users" style="font-size:3rem;color:var(--tblr-muted)"></i></div>'
      + '<p class="empty-title">Henüz müşteri yok</p>'
      + '<p class="empty-subtitle text-muted">İlk müşterinizi oluşturmak için butona tıklayın.</p>'
      + '<a href="' + base + 'clients/create" class="btn btn-primary mt-3"><i class="ti ti-plus me-1"></i> Yeni Müşteri Ekle</a>'
      + '</div></td></tr>';
  }

  function errorHtml() {
    return '<tr><td colspan="8" class="text-center text-danger py-4"><i class="ti ti-alert-circle me-2"></i>Liste yüklenemedi</td></tr>';
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