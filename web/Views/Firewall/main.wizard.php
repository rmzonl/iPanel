<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-shield me-2"></i>Güvenlik Duvarı</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('firewall/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Kural Ekle
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Ad</th>
                <th>Eylem</th>
                <th>Protokol</th>
                <th>Yön</th>
                <th>Kaynak IP</th>
                <th>Hedef Port</th>
                <th>Öncelik</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody id="tableBody">
              <tr>
                <td colspan="9" class="text-center py-4">
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
  var dirMap = {in:'Gelen', out:'Giden', both:'İkisi'};

  fetch(base + 'firewall/rows', {headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){ return r.json(); })
    .then(function(json){
      var rows = json.data || [];
      if (!rows.length) { tbody.innerHTML = emptyHtml(); return; }
      tbody.innerHTML = rows.map(renderRow).join('');
    })
    .catch(function(){ tbody.innerHTML = errorHtml(); });

  function renderRow(r) {
    var action = r.action === 'allow'
      ? '<span class="badge bg-success-lt"><i class="ti ti-check me-1"></i>İzin Ver</span>'
      : '<span class="badge bg-danger-lt"><i class="ti ti-ban me-1"></i>Engelle</span>';
    var status = r.status === 'active'
      ? '<span class="badge bg-success-lt">Aktif</span>'
      : '<span class="badge bg-secondary-lt">Pasif</span>';
    var dir = dirMap[r.direction] || r.direction;
    return '<tr>'
      + '<td><strong>' + esc(r.name) + '</strong></td>'
      + '<td>' + action + '</td>'
      + '<td><span class="badge bg-secondary-lt">' + esc((r.protocol||'').toUpperCase()) + '</span></td>'
      + '<td class="text-muted">' + esc(dir) + '</td>'
      + '<td class="text-muted font-monospace">' + esc(r.source_ip || 'Tümü') + '</td>'
      + '<td class="text-muted font-monospace">' + esc(r.dest_port || 'Tümü') + '</td>'
      + '<td class="text-muted">' + esc(r.priority) + '</td>'
      + '<td>' + status + '</td>'
      + '<td><div class="btn-group btn-group-sm">'
      + '<a href="' + base + 'firewall/toggle/' + r.id + '" class="btn btn-outline-secondary" title="Aç/Kapat"><i class="ti ti-power"></i></a>'
      + '<a href="' + base + 'firewall/delete/' + r.id + '" class="btn btn-outline-danger" title="Sil"'
      + ' onclick="return confirm(\'Bu kuralı silmek istediğinizden emin misiniz?\')"><i class="ti ti-trash"></i></a>'
      + '</div></td></tr>';
  }

  function emptyHtml() {
    return '<tr><td colspan="9" class="text-center py-5">'
      + '<div class="empty"><div class="empty-icon"><i class="ti ti-shield" style="font-size:3rem;color:var(--tblr-muted)"></i></div>'
      + '<p class="empty-title">Henüz güvenlik duvarı kuralı yok</p>'
      + '<a href="' + base + 'firewall/create" class="btn btn-primary mt-3"><i class="ti ti-plus me-1"></i> Kural Ekle</a>'
      + '</div></td></tr>';
  }

  function errorHtml() {
    return '<tr><td colspan="9" class="text-center text-danger py-4"><i class="ti ti-alert-circle me-2"></i>Liste yüklenemedi</td></tr>';
  }

  function esc(v) {
    return String(v == null ? '' : v)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
</script>
