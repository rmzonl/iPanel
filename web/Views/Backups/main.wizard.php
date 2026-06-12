<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-archive me-2"></i>Yedeklemeler</h2>
        </div>
        <div class="col-auto ms-auto">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackupModal">
            <i class="ti ti-plus me-1"></i> Yeni Yedek
          </button>
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
                <th>Dosya</th>
                <th>Site / Müşteri</th>
                <th>Tür</th>
                <th>Boyut (MB)</th>
                <th>Başlangıç</th>
                <th>Tamamlanma</th>
                <th>Durum</th>
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

<!-- Create Backup Modal -->
<div class="modal modal-blur fade" id="createBackupModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ URL::base('backups/create') }}">
        {[ echo $csrfField ?? ""; ]}
        <div class="modal-header">
          <h5 class="modal-title">Yeni Yedek Oluştur</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Site</label>
            <select name="site_id" class="form-select">
              <option value="">Tüm siteler</option>
              @foreach($sites as $s)
                <option value="{{ $s->id }}">{{ $s->domain }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Yedek Türü</label>
            <select name="type" class="form-select">
              <option value="full">Tam Yedek</option>
              <option value="database">Yalnızca Veritabanı</option>
              <option value="files">Yalnızca Dosyalar</option>
              <option value="email">Yalnızca E-posta</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">İptal</button>
          <button type="submit" class="btn btn-primary">
            <i class="ti ti-archive me-1"></i> Başlat
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function() {
  var base = '{{ URL::base("") }}';
  var tbody = document.getElementById('tableBody');
  var typeMap = {full:'Tam', database:'Veritabanı', files:'Dosyalar', email:'E-posta'};
  var statusMap = {
    pending:   ['bg-secondary-lt', 'Bekliyor'],
    running:   ['bg-yellow-lt',    'Çalışıyor'],
    completed: ['bg-success-lt',   'Tamamlandı'],
    failed:    ['bg-danger-lt',    'Başarısız']
  };

  fetch(base + 'backups/rows', { headers: { 'X-Requested-With':'XMLHttpRequest' } })
    .then(function(r){ return r.json(); })
    .then(function(json){
      var rows = json.data || [];
      if (!rows.length) { tbody.innerHTML = emptyHtml(); return; }
      tbody.innerHTML = rows.map(renderRow).join('');
    })
    .catch(function(){ tbody.innerHTML = errorHtml(); });

  function renderRow(r) {
    var sm = statusMap[r.status] || ['bg-secondary-lt', r.status];
    var target = esc(r.site_domain || r.client_name || '—');
    var downloadBtn = r.status === 'completed'
      ? '<a href="' + base + 'backups/download/' + r.id + '" class="btn btn-outline-success" title="İndir"><i class="ti ti-download"></i></a>'
      : '';
    return '<tr>'
      + '<td class="text-muted font-monospace small">' + esc(r.filename || '—') + '</td>'
      + '<td class="text-muted">' + target + '</td>'
      + '<td><span class="badge bg-secondary-lt">' + esc(typeMap[r.type] || r.type) + '</span></td>'
      + '<td class="text-muted">' + esc(r.size_mb || '—') + '</td>'
      + '<td class="text-muted small">' + esc(r.started_at ? fmtDate(r.started_at) : '—') + '</td>'
      + '<td class="text-muted small">' + esc(r.completed_at ? fmtDate(r.completed_at) : '—') + '</td>'
      + '<td><span class="badge ' + sm[0] + '">' + esc(sm[1]) + '</span></td>'
      + '<td><div class="btn-group btn-group-sm">' + downloadBtn
      + '<a href="' + base + 'backups/delete/' + r.id + '" class="btn btn-outline-danger" title="Sil"'
      + ' onclick="return confirm(\'Bu yedeği silmek istediğinizden emin misiniz?\')"><i class="ti ti-trash"></i></a>'
      + '</div></td></tr>';
  }

  function fmtDate(s) {
    var d = new Date(s.replace(' ', 'T'));
    return d.toLocaleDateString('tr-TR') + ' ' + d.toLocaleTimeString('tr-TR', {hour:'2-digit',minute:'2-digit'});
  }

  function emptyHtml() {
    return '<tr><td colspan="8" class="text-center py-5">'
      + '<div class="empty"><div class="empty-icon"><i class="ti ti-archive" style="font-size:3rem;color:var(--tblr-muted)"></i></div>'
      + '<p class="empty-title">Henüz yedek yok</p></div></td></tr>';
  }

  function errorHtml() {
    return '<tr><td colspan="8" class="text-center text-danger py-4"><i class="ti ti-alert-circle me-2"></i>Liste yüklenemedi</td></tr>';
  }

  function esc(v) {
    return String(v == null ? '' : v)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
</script>