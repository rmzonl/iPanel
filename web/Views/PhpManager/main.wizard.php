<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-brand-php me-2"></i>PHP Yönetimi</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('phpmanager/obfuscation') }}" class="btn btn-outline-secondary">
            <i class="ti ti-lock me-1"></i> Koruma Yönetimi
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">

      @if(!empty($success))
        <div class="alert alert-success alert-dismissible fade show mb-3">
          <i class="ti ti-circle-check me-2"></i>{{ $success }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif
      @if(!empty($error))
        <div class="alert alert-danger alert-dismissible fade show mb-3">
          <i class="ti ti-alert-circle me-2"></i>{{ $error }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      {[
        $info       = $phpInfo;
        $versions   = $info['versions']   ?? [];
        $active     = $info['active']     ?? 'Bilinmiyor';
        $extensions = $info['extensions'] ?? [];
        $fpmStatus  = $info['fpm_status'] ?? [];
        $os         = $info['os']         ?? 'unknown';
      ]}

      <!-- ── Sistem Bilgisi ── -->
      <div class="row row-deck row-cards mb-3">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Sistem Durumu</h3>
            </div>
            <div class="card-body">
              <dl class="row mb-0">
                <dt class="col-5 text-muted">Aktif PHP</dt>
                <dd class="col-7"><code>{{ $active }}</code></dd>
                <dt class="col-5 text-muted">İşletim Sistemi</dt>
                <dd class="col-7"><span class="badge bg-blue-lt">{{ strtoupper($os) }}</span></dd>
                <dt class="col-5 text-muted">Kurulu Sürümler</dt>
                <dd class="col-7">{{ count($versions) }} adet</dd>
              </dl>
            </div>
          </div>
        </div>

        <!-- ── PHP Sürümü Kur ── -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="ti ti-download me-2"></i>PHP Sürümü Kur</h3>
            </div>
            <div class="card-body">
              <form method="post" action="{{ URL::base('phpmanager/installversion') }}" data-ajax>
                {{ $csrfField }}
                <div class="input-group">
                  <select name="php_version" class="form-select" required>
                    <option value="">Sürüm seç…</option>
                    <option value="8.4">PHP 8.4</option>
                    <option value="8.3">PHP 8.3</option>
                    <option value="8.2">PHP 8.2</option>
                    <option value="8.1">PHP 8.1</option>
                    <option value="8.0">PHP 8.0</option>
                    <option value="7.4">PHP 7.4</option>
                  </select>
                  <button type="submit" class="btn btn-primary">
                    <i class="ti ti-download me-1"></i> Kur
                  </button>
                </div>
                <small class="text-muted mt-1 d-block">Kurulum birkaç dakika sürebilir.</small>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Kurulu PHP Sürümleri & FPM Durumu ── -->
      <div class="card mb-3">
        <div class="card-header">
          <h3 class="card-title"><i class="ti ti-list me-2"></i>Kurulu PHP Sürümleri</h3>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table">
            <thead>
              <tr>
                <th>Sürüm</th>
                <th>PHP-FPM Durumu</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($versions as $v)
                {[
                  $fpmSt  = $fpmStatus[$v] ?? 'unknown';
                  $fpmClr = $fpmSt === 'active' ? 'success' : ($fpmSt === 'inactive' ? 'secondary' : 'danger');
                ]}
                <tr>
                  <td><strong>PHP {{ $v }}</strong> @if(strpos($active, $v) === 0) <span class="badge bg-green-lt ms-1">Aktif</span> @endif</td>
                  <td><span class="badge bg-{{ $fpmClr }}-lt text-{{ $fpmClr }}">{{ $fpmSt }}</span></td>
                  <td>
                    <form method="post" action="{{ URL::base('phpmanager/restartfpm') }}" class="d-inline" data-ajax>
                      {{ $csrfField }}
                      <input type="hidden" name="php_version" value="{{ $v }}">
                      <button type="submit" class="btn btn-sm btn-ghost-warning" title="FPM Yeniden Başlat">
                        <i class="ti ti-refresh"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted py-4">Kurulu PHP sürümü bulunamadı</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── Yüklü Eklentiler ── -->
      <div class="card mb-3">
        <div class="card-header d-flex align-items-center gap-2">
          <h3 class="card-title mb-0"><i class="ti ti-puzzle me-2"></i>Yüklü Eklentiler (Aktif PHP)</h3>
          <span class="badge bg-blue-lt ms-auto">{{ count($extensions) }} eklenti</span>
        </div>
        <div class="card-body">
          <div class="d-flex flex-wrap gap-1">
            @foreach($extensions as $ext)
              <span class="badge bg-secondary-lt">{{ $ext }}</span>
            @endforeach
          </div>
        </div>
      </div>

      <!-- ── Eklenti Yönetimi ── -->
      @if(!empty($versions))
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="ti ti-plus me-2"></i>Eklenti Kur / Kaldır</h3>
        </div>
        <div class="card-body">
          <form method="post" action="{{ URL::base('phpmanager/toggleextension') }}" data-ajax>
            {{ $csrfField }}
            <div class="row g-2">
              <div class="col-md-3">
                <select name="php_version" class="form-select" required>
                  <option value="">PHP Sürümü…</option>
                  @foreach($versions as $v)
                    <option value="{{ $v }}">PHP {{ $v }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-5">
                <input type="text" name="extension" class="form-control"
                       placeholder="Eklenti adı (örn: mbstring, curl, gd)"
                       pattern="[a-zA-Z0-9_-]+" required>
              </div>
              <div class="col-md-2">
                <select name="action" class="form-select" required>
                  <option value="install">Kur</option>
                  <option value="remove">Kaldır</option>
                </select>
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Uygula</button>
              </div>
            </div>
          </form>
        </div>
      </div>
      @endif

    </div>
  </div>
</div>
