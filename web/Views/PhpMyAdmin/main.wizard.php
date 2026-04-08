<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-database me-2"></i>PHPMyAdmin Yönetimi</h2>
          <div class="text-muted mt-1">PHPMyAdmin kurulumu ve güvenli erişim yönetimi.</div>
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

        <!-- Durum Kartı -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Kurulum Durumu</h3>
              <div class="card-options">
                @if($status['installed'])
                  <span class="badge bg-success">Kurulu</span>
                @else
                  <span class="badge bg-secondary">Kurulu Değil</span>
                @endif
              </div>
            </div>
            <div class="card-body">
              <dl class="row">
                <dt class="col-5">Durum:</dt>
                <dd class="col-7">
                  @if($status['installed'])
                    <span class="text-success"><i class="ti ti-check me-1"></i>Kurulu</span>
                  @else
                    <span class="text-muted"><i class="ti ti-x me-1"></i>Kurulu değil</span>
                  @endif
                </dd>

                @if($status['installed'])
                  <dt class="col-5">Kurulum Yolu:</dt>
                  <dd class="col-7"><code>{{ $status['path'] }}</code></dd>

                  <dt class="col-5">Sürüm:</dt>
                  <dd class="col-7">{{ $status['version'] ?? 'Bilinmiyor' }}</dd>

                  <dt class="col-5">Nginx Konfigürasyonu:</dt>
                  <dd class="col-7">
                    @if($status['has_nginx'])
                      <span class="text-success">Var</span>
                    @else
                      <span class="text-warning">Yok (Manuel gerekli)</span>
                    @endif
                  </dd>
                @endif

                <dt class="col-5">İşletim Sistemi:</dt>
                <dd class="col-7"><code>{{ $status['os'] }}</code></dd>
              </dl>
            </div>
            <div class="card-footer d-flex gap-2">
              @if(!$status['installed'])
                <form method="POST" action="{{ URL::base('phpmyadmin/install') }}">
                  {[ echo $csrfField ?? ""; ]}
                  <button type="submit" class="btn btn-success"
                          onclick="return confirm('PHPMyAdmin kurulacak. Bu işlem birkaç dakika sürebilir. Devam edilsin mi?')">
                    <i class="ti ti-download me-1"></i>Kur
                  </button>
                </form>
              @else
                <form method="POST" action="{{ URL::base('phpmyadmin/remove') }}">
                  {[ echo $csrfField ?? ""; ]}
                  <button type="submit" class="btn btn-danger"
                          onclick="return confirm('PHPMyAdmin kaldırılacak. Devam edilsin mi?')">
                    <i class="ti ti-trash me-1"></i>Kaldır
                  </button>
                </form>
              @endif
            </div>
          </div>
        </div>

        <!-- Güvenli Erişim -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Güvenli Geçici Erişim</h3>
            </div>
            <div class="card-body">
              @if(!$status['installed'])
                <p class="text-muted">PHPMyAdmin kurulu olmadan erişim oluşturulamaz.</p>
              @else
                @if(!empty($status['access_token']))
                  <div class="alert alert-info">
                    <i class="ti ti-clock me-2"></i>Aktif geçici erişim mevcut (30 dk).
                  </div>
                  <p class="text-muted small">Erişim token'ı: <code>{{ $status['access_token'] }}</code></p>
                  <p class="text-muted small">PHPMyAdmin'i <code>/phpmyadmin?token={{ $status['access_token'] }}</code> URL'sinden ziyaret edin.</p>
                @else
                  <p class="text-muted">30 dakikalık tek kullanımlık güvenli erişim URL'si oluşturun. Token olmadan PHPMyAdmin erişimi engellenmelidir (nginx yapılandırması).</p>
                  <form method="POST" action="{{ URL::base('phpmyadmin/generateAccess') }}">
                    {[ echo $csrfField ?? ""; ]}
                    <button type="submit" class="btn btn-primary">
                      <i class="ti ti-link me-1"></i>Geçici Erişim Oluştur
                    </button>
                  </form>
                @endif
              @endif
            </div>
          </div>

          <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">Önerilen Nginx Yapılandırması</h3></div>
            <div class="card-body">
              <pre class="bg-dark text-white p-3 rounded" style="font-size:.75rem;"><code># /etc/nginx/conf.d/phpmyadmin.conf
location /phpmyadmin {
    root /usr/share;
    index index.php;

    # Token bazlı erişim (iPanel üretir)
    if ($arg_token = "") {
        return 403;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}</code></pre>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
