<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-server me-2"></i>DNS Yönetimi</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">DNS Zonları</h3>
          <div class="card-options text-muted small">Bir domain seçerek kayıtlarını yönetin</div>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Domain</th>
                <th>Site</th>
                <th>TTL</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody id="tableBody">
              <tr>
                <td colspan="5" class="text-center py-4">
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

  fetch(base + 'dns/rows', { headers: { 'X-Requested-With':'XMLHttpRequest' } })
    .then(function(r){ return r.json(); })
    .then(function(json){
      var rows = json.data || [];
      if (!rows.length) { tbody.innerHTML = emptyHtml(); return; }
      tbody.innerHTML = rows.map(renderRow).join('');
    })
    .catch(function(){ tbody.innerHTML = errorHtml(); });

  function renderRow(r) {
    var status = r.status === 'active'
      ? '<span class="badge bg-success-lt">Aktif</span>'
      : '<span class="badge bg-secondary-lt">Pasif</span>';
    return '<tr>'
      + '<td><strong>' + esc(r.domain_name || '—') + '</strong></td>'
      + '<td class="text-muted">' + esc(r.site_domain || '—') + '</td>'
      + '<td class="text-muted">' + esc(r.ttl) + '</td>'
      + '<td>' + status + '</td>'
      + '<td><a href="' + base + 'dns/records/' + r.id + '" class="btn btn-sm btn-primary">'
      + '<i class="ti ti-list me-1"></i> Kayıtlar</a></td>'
      + '</tr>';
  }

  function emptyHtml() {
    return '<tr><td colspan="5" class="text-center py-5">'
      + '<div class="empty"><div class="empty-icon"><i class="ti ti-server" style="font-size:3rem;color:var(--tblr-muted)"></i></div>'
      + '<p class="empty-title">DNS zonu bulunamadı</p>'
      + '<p class="empty-subtitle text-muted">Domainler eklendikçe DNS zonları otomatik oluşturulur.</p>'
      + '</div></td></tr>';
  }

  function errorHtml() {
    return '<tr><td colspan="5" class="text-center text-danger py-4"><i class="ti ti-alert-circle me-2"></i>Liste yüklenemedi</td></tr>';
  }

  function esc(v) {
    return String(v == null ? '' : v)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
</script>