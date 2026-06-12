<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-network me-2"></i>IP Adresleri</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('ipaddresses/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> IP Ekle
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">

      <div class="alert alert-info mb-3">
        <i class="ti ti-info-circle me-2"></i>
        <strong>Sunucu IP'si</strong> tüm siteler için varsayılan IP'dir.
        <strong>Paylaşımlı IP</strong> birden fazla site tarafından kullanılabilir.
        <strong>Özel IP</strong> yalnızca tek bir müşteriye tahsis edilir.
      </div>

      <div class="card">
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>IP Adresi</th>
                <th>Netmask</th>
                <th>Gateway</th>
                <th>Tür</th>
                <th>Müşteri</th>
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
  var typeMap = {
    server:    ['bg-red-lt',   'Sunucu'],
    shared:    ['bg-blue-lt',  'Paylaşımlı'],
    dedicated: ['bg-green-lt', 'Özel']
  };

  fetch(base + 'ipaddresses/rows', { headers: { 'X-Requested-With':'XMLHttpRequest' } })
    .then(function(r){ return r.json(); })
    .then(function(json){
      var rows = json.data || [];
      if (!rows.length) { tbody.innerHTML = emptyHtml(); return; }
      tbody.innerHTML = rows.map(renderRow).join('');
    })
    .catch(function(){ tbody.innerHTML = errorHtml(); });

  function renderRow(r) {
    var tm = typeMap[r.type] || ['bg-secondary-lt', r.type];
    var status = r.status === 'active'
      ? '<span class="badge bg-success-lt">Aktif</span>'
      : '<span class="badge bg-secondary-lt">Pasif</span>';
    return '<tr>'
      + '<td><strong class="font-monospace">' + esc(r.ip) + '</strong></td>'
      + '<td class="text-muted font-monospace">' + esc(r.netmask || '—') + '</td>'
      + '<td class="text-muted font-monospace">' + esc(r.gateway || '—') + '</td>'
      + '<td><span class="badge ' + tm[0] + '">' + esc(tm[1]) + '</span></td>'
      + '<td class="text-muted">' + esc(r.client_name || '—') + '</td>'
      + '<td>' + status + '</td>'
      + '<td><div class="btn-group btn-group-sm">'
      + '<a href="' + base + 'ipaddresses/edit/' + r.id + '" class="btn btn-outline-primary" title="Düzenle"><i class="ti ti-edit"></i></a>'
      + '<a href="' + base + 'ipaddresses/delete/' + r.id + '" class="btn btn-outline-danger" title="Sil"'
      + ' onclick="return confirm(\'Bu IP adresini silmek istediğinizden emin misiniz?\')"><i class="ti ti-trash"></i></a>'
      + '</div></td></tr>';
  }

  function emptyHtml() {
    return '<tr><td colspan="7" class="text-center py-5">'
      + '<div class="empty"><div class="empty-icon"><i class="ti ti-network" style="font-size:3rem;color:var(--tblr-muted)"></i></div>'
      + '<p class="empty-title">Henüz IP adresi yok</p>'
      + '<a href="' + base + 'ipaddresses/create" class="btn btn-primary mt-3"><i class="ti ti-plus me-1"></i> IP Ekle</a>'
      + '</div></td></tr>';
  }

  function errorHtml() {
    return '<tr><td colspan="7" class="text-center text-danger py-4"><i class="ti ti-alert-circle me-2"></i>Liste yüklenemedi</td></tr>';
  }

  function esc(v) {
    return String(v == null ? '' : v)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
</script>