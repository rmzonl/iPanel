<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-lock me-2"></i>PHP Koruma Yönetimi</h2>
          <div class="text-muted mt-1">ionCube Loader, PHPKoru ve Zend Guard yönetimi</div>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('phpmanager/main') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> PHP Yönetimi
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
        $ioncube = $ioncubeStatus ?? ['loaded' => false, 'version' => null];
        $phpkoru = $phpkoruStatus ?? ['loaded' => false];
        $zend    = $zendStatus    ?? ['loaded' => false];
      ]}

      <!-- ── ionCube Loader ── -->
      <div class="card mb-3">
        <div class="card-header">
          <h3 class="card-title">
            <i class="ti ti-shield-check me-2"></i>ionCube Loader
          </h3>
          <div class="card-options">
            @if($ioncube['loaded'])
              <span class="badge bg-success-lt text-success">Yüklü</span>
              @if($ioncube['version'])
                <span class="text-muted ms-2 small">v{{ $ioncube['version'] }}</span>
              @endif
            @else
              <span class="badge bg-secondary-lt text-secondary">Yüklü Değil</span>
            @endif
          </div>
        </div>
        <div class="card-body">
          <div class="row align-items-end g-3">
            <div class="col-md-8">
              <p class="text-muted mb-1">
                ionCube Loader, PHP dosyalarını ionCube Encoder ile şifrelenmiş formatta çalıştırmanızı sağlar.
                Kurulum için sunucunun internete erişimi gereklidir.
              </p>
              @if($ioncube['loaded'])
                <div class="alert alert-success py-2 mb-0">
                  <i class="ti ti-check me-1"></i> ionCube Loader bu PHP sürümü için aktif.
                </div>
              @else
                <div class="alert alert-secondary py-2 mb-0">
                  <i class="ti ti-info-circle me-1"></i> ionCube Loader yüklü değil. Kurulum için PHP sürümünü seçin.
                </div>
              @endif
            </div>
            <div class="col-md-4">
              @if(!$ioncube['loaded'])
                <form method="post" action="{{ URL::base('phpmanager/installioncube') }}" data-ajax>
                  {{ $csrfField }}
                  <div class="input-group">
                    <select name="php_version" class="form-select" required>
                      <option value="">PHP Sürümü…</option>
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
                </form>
              @else
                <form method="post" action="{{ URL::base('phpmanager/removeioncube') }}" data-ajax>
                  {{ $csrfField }}
                  <div class="input-group">
                    <select name="php_version" class="form-select" required>
                      <option value="">PHP Sürümü…</option>
                      <option value="8.4">PHP 8.4</option>
                      <option value="8.3">PHP 8.3</option>
                      <option value="8.2">PHP 8.2</option>
                      <option value="8.1">PHP 8.1</option>
                      <option value="8.0">PHP 8.0</option>
                      <option value="7.4">PHP 7.4</option>
                    </select>
                    <button type="submit" class="btn btn-danger">
                      <i class="ti ti-trash me-1"></i> Kaldır
                    </button>
                  </div>
                </form>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- ── PHPKoru ── -->
      <div class="card mb-3">
        <div class="card-header">
          <h3 class="card-title">
            <i class="ti ti-shield me-2"></i>PHPKoru / SourceGuardian
          </h3>
          <div class="card-options">
            @if($phpkoru['loaded'])
              <span class="badge bg-success-lt text-success">Yüklü</span>
            @else
              <span class="badge bg-secondary-lt text-secondary">Yüklü Değil</span>
            @endif
          </div>
        </div>
        <div class="card-body">
          <p class="text-muted mb-2">
            PHPKoru veya SourceGuardian Loader, PHP dosyalarını şifreli formatta çalıştırır.
            PHP'nin <code>extension_loaded('phpkoru')</code> veya <code>extension_loaded('sg')</code>
            ile algılanır.
          </p>
          @if($phpkoru['loaded'])
            <div class="alert alert-success py-2">
              <i class="ti ti-check me-1"></i> PHPKoru/SourceGuardian bu PHP ortamında aktif.
            </div>
          @else
            <div class="alert alert-secondary py-2">
              <i class="ti ti-info-circle me-1"></i> PHPKoru/SourceGuardian yüklü değil.
              Manuel kurulum için sağlayıcının web sitesini ziyaret edin.
            </div>
          @endif
        </div>
      </div>

      <!-- ── Zend Guard ── -->
      <div class="card mb-3">
        <div class="card-header">
          <h3 class="card-title">
            <i class="ti ti-brand-zend me-2" style="font-style:normal">Z</i>
            <span class="ms-1">Zend Guard Loader</span>
          </h3>
          <div class="card-options">
            @if($zend['loaded'])
              <span class="badge bg-success-lt text-success">Yüklü</span>
            @else
              <span class="badge bg-secondary-lt text-secondary">Yüklü Değil</span>
            @endif
          </div>
        </div>
        <div class="card-body">
          <p class="text-muted mb-2">
            Zend Guard Loader, Zend Guard ile korunan PHP dosyalarını çalıştırmak için gereklidir.
            PHP 5.x ve bazı PHP 7.x sürümleri ile uyumludur.
            <code>extension_loaded('Zend Guard Loader')</code> ile algılanır.
          </p>
          @if($zend['loaded'])
            <div class="alert alert-success py-2">
              <i class="ti ti-check me-1"></i> Zend Guard Loader bu PHP ortamında aktif.
            </div>
          @else
            <div class="alert alert-secondary py-2">
              <i class="ti ti-info-circle me-1"></i> Zend Guard Loader yüklü değil.
              <strong>Not:</strong> PHP 8.x ile uyumluluğu resmi olarak sonlandırılmıştır.
            </div>
          @endif
        </div>
      </div>

      <!-- ── Bilgi Notu ── -->
      <div class="alert alert-info">
        <h4 class="alert-title"><i class="ti ti-info-circle me-2"></i>Önemli Bilgi</h4>
        <div>
          Koruma modüllerinin durumu, şu an çalışan PHP sürümünün (<code>php-cli</code>) yüklü eklentilerine göre gösterilmektedir.
          Web sunucusundaki PHP-FPM için durum farklı olabilir. Kesin doğrulama için PHP-FPM'i yeniden başlatıp
          <code>phpinfo()</code> çıktısını kontrol edin.
        </div>
      </div>

    </div>
  </div>
</div>
