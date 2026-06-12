<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-api me-2"></i>API Token Yönetimi</h2>
          <div class="text-muted mt-1">REST API erişimi için token oluşturun ve yönetin.</div>
        </div>
        <div class="col-auto">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTokenModal">
            <i class="ti ti-plus me-2"></i>Yeni Token
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">

      @if(!empty($success))
        <div class="alert alert-success alert-dismissible"><i class="ti ti-check me-2"></i>{[ echo $success; ]}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif
      @if(!empty($error))
        <div class="alert alert-danger alert-dismissible"><i class="ti ti-alert-circle me-2"></i>{{ $error }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif

      {[
        $newToken = Session::select('new_token');
        if ($newToken) { Session::delete('new_token'); }
      ]}
      @if(!empty($newToken))
        <div class="alert alert-warning">
          <h4 class="alert-title"><i class="ti ti-alert-triangle me-2"></i>Token'ınızı Kopyalayın!</h4>
          <p class="mb-2">Bu token bir daha gösterilmeyecek. Güvenli bir yere kaydedin.</p>
          <div class="input-group">
            <input type="text" class="form-control font-monospace" id="newTokenInput" value="{{ $newToken }}" readonly>
            <button class="btn btn-warning" onclick="copyToken()">
              <i class="ti ti-copy"></i> Kopyala
            </button>
          </div>
        </div>
      @endif

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">API Kullanımı</h3>
        </div>
        <div class="card-body">
          <p class="text-muted mb-2">Token'ı her API isteğinde <code>Authorization</code> başlığına ekleyin:</p>
          <pre class="bg-dark text-white p-3 rounded"><code>curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://your-panel.com/api/clients</code></pre>
        </div>
      </div>

      <div class="card mt-3">
        <div class="table-responsive">
          <table class="table table-vcenter card-table">
            <thead>
              <tr>
                <th>Token Adı</th>
                <th>Oluşturulma</th>
                <th>Son Kullanım</th>
                <th>Sona Erme</th>
                <th>Durum</th>
                <th></th>
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

<!-- Yeni Token Modal -->
<div class="modal modal-blur fade" id="createTokenModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Yeni API Token</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ URL::base('apitokens/create') }}">
        {[ echo $csrfField ?? ""; ]}
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Token Adı <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="Örn: Monitoring Script" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Sona Erme (gün)</label>
            <input type="number" name="expires_days" class="form-control" placeholder="0 = süresiz" min="0" max="3650">
            <div class="form-text">0 girilirse token süresiz geçerlidir.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary me-auto" data-bs-dismiss="modal">İptal</button>
          <button type="submit" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Oluştur
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function() {
  var base = '{{ URL::base("") }}';
  var csrf = '{{ $csrfToken ?? "" }}';
  var tbody = document.getElementById('tableBody');

  fetch(base + 'apitokens/rows', { headers: { 'X-Requested-With':'XMLHttpRequest' } })
    .then(function(r){ return r.json(); })
    .then(function(json){
      var rows = json.data || [];
      if (!rows.length) { tbody.innerHTML = emptyHtml(); return; }
      tbody.innerHTML = rows.map(renderRow).join('');
    })
    .catch(function(){ tbody.innerHTML = errorHtml(); });

  function renderRow(r) {
    var status = r.status === 'active'
      ? '<span class="badge bg-success">Aktif</span>'
      : '<span class="badge bg-danger">İptal Edildi</span>';
    var revokeBtn = r.status === 'active'
      ? '<form method="POST" action="' + base + 'apitokens/revoke" class="d-inline">'
        + '<input type="hidden" name="_csrf" value="' + esc(csrf) + '">'
        + '<input type="hidden" name="token_id" value="' + r.id + '">'
        + '<button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Bu token iptal edilsin mi?\')">' 
        + '<i class="ti ti-ban"></i> İptal</button></form>'
      : '';
    return '<tr>'
      + '<td><strong>' + esc(r.name) + '</strong></td>'
      + '<td class="text-muted">' + esc(r.created_at || '—') + '</td>'
      + '<td class="text-muted">' + esc(r.last_used || 'Henüz kullanılmadı') + '</td>'
      + '<td class="text-muted">' + esc(r.expires_at || 'Süresiz') + '</td>'
      + '<td>' + status + '</td>'
      + '<td>' + revokeBtn + '</td>'
      + '</tr>';
  }

  function emptyHtml() {
    return '<tr><td colspan="6" class="text-center text-muted py-4">'
      + '<i class="ti ti-api me-2"></i>Henüz API token yok.</td></tr>';
  }

  function errorHtml() {
    return '<tr><td colspan="6" class="text-center text-danger py-4"><i class="ti ti-alert-circle me-2"></i>Liste yüklenemedi</td></tr>';
  }

  function esc(v) {
    return String(v == null ? '' : v)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();

function copyToken() {
  var input = document.getElementById('newTokenInput');
  if (!input) return;
  input.select();
  navigator.clipboard.writeText(input.value).then(function() {
    alert('Token kopyalandı!');
  });
}
</script>