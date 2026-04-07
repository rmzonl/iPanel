<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-settings me-2"></i>Ayarlar</h2>
          <div class="text-muted mt-1">Sunucu, müşteri ve site bazlı ayarları yönetin. Alt seviye ayarlar üst seviyeyi geçersiz kılar.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">

      <ul class="nav nav-tabs mb-3" id="settingsTabs">
        <li class="nav-item">
          <a class="nav-link active" data-bs-toggle="tab" href="#serverTab">
            <i class="ti ti-server me-1"></i>Sunucu
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#clientTab">
            <i class="ti ti-users me-1"></i>Müşteri
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#siteTab">
            <i class="ti ti-world me-1"></i>Site
          </a>
        </li>
      </ul>

      <div class="tab-content">

        <!-- Server Settings Tab -->
        <div class="tab-pane fade show active" id="serverTab">
          <form method="POST" action="{{ URL::base('settings/saveServer') }}">
            <div class="card">
              <div class="card-header"><h3 class="card-title">Sunucu Genel Ayarları</h3></div>
              <div class="card-body">
                <div class="row g-3">
                  {[
                    $serverKeys = [
                      'panel_name'              => ['Panel Adı', 'text', 'iPanel'],
                      'server_ip'               => ['Sunucu Ana IP', 'text', ''],
                      'php_versions'            => ['PHP Versiyonları (virgülle)', 'text', '7.4,8.0,8.1,8.2,8.3'],
                      'default_php_version'     => ['Varsayılan PHP', 'text', '8.2'],
                      'ssl_email'               => ['SSL E-postası (Let\'s Encrypt)', 'email', ''],
                      'webroot_base'            => ['Web Root Dizini', 'text', '/var/www'],
                      'backup_path'             => ['Yedek Dizini', 'text', '/var/backups/ipanel'],
                      'max_sites_per_client'    => ['Müşteri Başına Max Site (0=sınırsız)', 'number', '0'],
                    ];
                  ]}
                  @foreach($serverKeys as $key => $meta)
                    {[ $val = $serverSettings[$key] ?? $meta[2]; ]}
                    <div class="col-md-6">
                      <label class="form-label">{{ $meta[0] }}</label>
                      <input type="{{ $meta[1] }}" name="settings[{{ $key }}]" class="form-control" value="{{ $val }}"/>
                    </div>
                  @endforeach
                </div>
              </div>
              <div class="card-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                  <i class="ti ti-device-floppy me-1"></i> Kaydet
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- Client Settings Tab -->
        <div class="tab-pane fade" id="clientTab">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Müşteri Bazlı Ayarlar</h3>
              <div class="card-options">
                <select id="clientSelect" class="form-select form-select-sm" onchange="loadClientSettings(this.value)">
                  <option value="">Müşteri Seçin...</option>
                  @foreach($clients as $c)
                    <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="card-body" id="clientSettingsForm">
              <div class="text-center text-muted py-4">
                <i class="ti ti-arrow-up me-1"></i> Ayarlamak için bir müşteri seçin
              </div>
            </div>
          </div>
        </div>

        <!-- Site Settings Tab -->
        <div class="tab-pane fade" id="siteTab">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Site Bazlı Ayarlar</h3>
              <div class="card-options">
                <select id="siteSelect" class="form-select form-select-sm" onchange="loadSiteSettings(this.value)">
                  <option value="">Site Seçin...</option>
                  @foreach($sites as $s)
                    <option value="{{ $s->id }}">{{ $s->domain }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="card-body" id="siteSettingsForm">
              <div class="text-center text-muted py-4">
                <i class="ti ti-arrow-up me-1"></i> Ayarlamak için bir site seçin
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
function loadClientSettings(clientId) {
  if (!clientId) return;
  var container = document.getElementById('clientSettingsForm');
  container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>';
  fetch('{{ URL::base("settings/getClientSettings/") }}' + clientId)
    .then(r => r.json())
    .then(data => {
      container.innerHTML = buildSettingsForm(data, clientId, 'client');
    });
}

function loadSiteSettings(siteId) {
  if (!siteId) return;
  var container = document.getElementById('siteSettingsForm');
  container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>';
  fetch('{{ URL::base("settings/getSiteSettings/") }}' + siteId)
    .then(r => r.json())
    .then(data => {
      container.innerHTML = buildSettingsForm(data, siteId, 'site');
    });
}

function buildSettingsForm(data, scopeId, scope) {
  var keys = {
    'php_version': 'PHP Versiyonu',
    'disk_quota': 'Disk Kotası (MB)',
    'bandwidth_quota': 'Bant Genişliği (MB)',
    'max_email_accounts': 'Max E-posta Hesabı',
    'max_ftp_accounts': 'Max FTP Hesabı',
    'max_databases': 'Max Veritabanı',
    'ssl_auto_renew': 'SSL Otomatik Yenile (1/0)',
  };
  var html = '<form method="POST" action="{{ URL::base("settings/saveScopeSettings") }}">';
  html += '<input type="hidden" name="scope" value="' + scope + '">';
  html += '<input type="hidden" name="scope_id" value="' + scopeId + '">';
  html += '<div class="row g-3">';
  for (var k in keys) {
    var val = data[k] || '';
    html += '<div class="col-md-6">';
    html += '<label class="form-label">' + keys[k] + '</label>';
    html += '<input type="text" name="settings[' + k + ']" class="form-control" value="' + val + '" placeholder="Boş = üst ayarı kullan"/>';
    html += '</div>';
  }
  html += '</div>';
  html += '<div class="d-flex justify-content-end mt-3">';
  html += '<button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Kaydet</button>';
  html += '</div></form>';
  return html;
}
</script>
