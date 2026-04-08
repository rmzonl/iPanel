<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-brand-php me-2"></i>PHP Yönetimi</h2>
          <div class="text-muted mt-1">PHP sürümleri, eklentiler ve FPM servisleri.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">

      @if(!empty($success))
        <div class="alert alert-success alert-dismissible">{[ echo $success; ]}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif
      @if(!empty($error))
        <div class="alert alert-danger alert-dismissible"><pre class="mb-0">{{ $error }}</pre><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif

      <div class="row">

        <!-- Sol: Sürüm listesi + FPM -->
        <div class="col-lg-5">

          <!-- Kurulu Sürümler -->
          <div class="card mb-3">
            <div class="card-header">
              <h3 class="card-title">Kurulu PHP Sürümleri</h3>
            </div>
            <div class="card-body p-0">
              <table class="table table-vcenter mb-0">
                <thead>
                  <tr>
                    <th>Sürüm</th>
                    <th>PHP-FPM</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($phpInfo['versions'] as $v)
                    {[ $fpmState = $phpInfo['fpm_status'][$v] ?? 'unknown'; ]}
                    <tr>
                      <td><strong>PHP {{ $v }}</strong></td>
                      <td>
                        @if($fpmState === 'active')
                          <span class="badge bg-success">Çalışıyor</span>
                        @elseif($fpmState === 'inactive')
                          <span class="badge bg-warning">Durdu</span>
                        @else
                          <span class="badge bg-secondary">{{ $fpmState }}</span>
                        @endif
                      </td>
                      <td>
                        <form method="POST" action="{{ URL::base('phpmanager/restartFpm') }}" class="d-inline">
                          {[ echo $csrfField ?? ""; ]}
                          <input type="hidden" name="php_version" value="{{ $v }}">
                          <button type="submit" class="btn btn-sm btn-secondary" title="FPM Yeniden Başlat">
                            <i class="ti ti-refresh"></i>
                          </button>
                        </form>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="text-center text-muted py-3">Kurulu PHP sürümü bulunamadı.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <!-- Yeni Sürüm Kur -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Yeni PHP Sürümü Kur</h3>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ URL::base('phpmanager/installVersion') }}">
                {[ echo $csrfField ?? ""; ]}
                <div class="input-group">
                  <select name="php_version" class="form-select">
                    @foreach(['7.4','8.0','8.1','8.2','8.3'] as $ver)
                      <option value="{{ $ver }}">PHP {{ $ver }}</option>
                    @endforeach
                  </select>
                  <button type="submit" class="btn btn-success"
                          onclick="return confirm('PHP kurulumu başlayacak. Bu işlem birkaç dakika sürebilir.')">
                    <i class="ti ti-download me-1"></i>Kur
                  </button>
                </div>
                <div class="form-text">{{ $phpInfo['os'] === 'debian' ? 'PPA:ondrej/php üzerinden kurulur' : 'Remi Repository üzerinden kurulur' }}</div>
              </form>
            </div>
          </div>
        </div>

        <!-- Sağ: Eklentiler -->
        <div class="col-lg-7">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Yüklü Eklentiler (Aktif PHP)</h3>
              <div class="card-options">
                <span class="badge bg-blue">PHP {{ $phpInfo['active'] }}</span>
              </div>
            </div>
            <div class="card-body" style="max-height:300px; overflow-y:auto;">
              <div class="row g-1">
                @foreach($phpInfo['extensions'] as $ext)
                  <div class="col-auto">
                    <span class="badge bg-azure-lt">{{ $ext }}</span>
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          <!-- Eklenti Kur/Kaldır -->
          <div class="card mt-3">
            <div class="card-header">
              <h3 class="card-title">Eklenti Yönetimi</h3>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ URL::base('phpmanager/toggleExtension') }}">
                {[ echo $csrfField ?? ""; ]}
                <div class="row g-2">
                  <div class="col-4">
                    <label class="form-label">PHP Sürümü</label>
                    <select name="php_version" class="form-select">
                      @foreach($phpInfo['versions'] as $v)
                        <option value="{{ $v }}">PHP {{ $v }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-4">
                    <label class="form-label">Eklenti Adı</label>
                    <input type="text" name="extension" class="form-control" placeholder="Örn: redis, imagick">
                  </div>
                  <div class="col-4">
                    <label class="form-label">İşlem</label>
                    <div class="d-flex gap-2">
                      <button type="submit" name="action" value="install" class="btn btn-success flex-fill">
                        <i class="ti ti-plus"></i> Kur
                      </button>
                      <button type="submit" name="action" value="remove" class="btn btn-danger flex-fill"
                              onclick="return confirm('Bu eklenti kaldırılsın mı?')">
                        <i class="ti ti-minus"></i> Kaldır
                      </button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- Yaygın Eklentiler -->
          <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">Yaygın Eklentiler</h3></div>
            <div class="card-body">
              <div class="row g-1">
                {[
                  $common = ['redis','imagick','memcached','gmp','bcmath','soap','xsl','zip','intl','mbstring','xml','curl','pdo_mysql','gd','opcache'];
                  foreach ($common as $ext):
                    $loaded = in_array($ext, $phpInfo['extensions']);
                ]}
                <div class="col-auto">
                  <span class="badge {{ $loaded ? 'bg-green' : 'bg-secondary' }}">
                    {{ $ext }} {{ $loaded ? '✓' : '' }}
                  </span>
                </div>
                {[ endforeach; ]}
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
