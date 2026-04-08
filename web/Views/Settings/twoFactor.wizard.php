<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-shield-lock me-2"></i>İki Faktörlü Doğrulama (2FA)</h2>
          <div class="text-muted mt-1">Google Authenticator veya uyumlu uygulamalarla hesabınızı güvenli hale getirin.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">

      @if(!empty($success))
        <div class="alert alert-success alert-dismissible"><i class="ti ti-check me-2"></i>{{ $success }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif
      @if(!empty($error))
        <div class="alert alert-danger alert-dismissible"><i class="ti ti-alert-circle me-2"></i>{{ $error }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif

      <div class="row">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">2FA Durumu</h3>
              <div class="card-options">
                @if($totpEnabled)
                  <span class="badge bg-success">Etkin</span>
                @else
                  <span class="badge bg-secondary">Devre Dışı</span>
                @endif
              </div>
            </div>
            <div class="card-body">
              @if($totpEnabled)
                <p class="text-success"><i class="ti ti-shield-check me-1"></i>2FA hesabınızda etkin. Her girişte doğrulama kodu gereklidir.</p>
                <p class="text-muted">Kalan yedek kod: <strong>{{ $backupRemaining }}</strong></p>

                <hr>
                <h4>2FA Devre Dışı Bırak</h4>
                <p class="text-warning"><i class="ti ti-alert-triangle me-1"></i>Devre dışı bırakmak için mevcut şifrenizi girin.</p>
                <form method="POST" action="{{ URL::base('settings/disable2fa') }}">
                  {[ echo $csrfField ?? ""; ]}
                  <div class="mb-3">
                    <label class="form-label">Şifreniz</label>
                    <input type="password" name="password" class="form-control" required>
                  </div>
                  <button type="submit" class="btn btn-danger" onclick="return confirm('2FA devre dışı bırakılsın mı?')">
                    <i class="ti ti-shield-off me-1"></i>2FA Devre Dışı Bırak
                  </button>
                </form>
              @else
                <p class="text-muted"><i class="ti ti-info-circle me-1"></i>2FA etkin değil. Etkinleştirmek için aşağıdaki butona tıklayın.</p>
                <form method="POST" action="{{ URL::base('settings/setup2fa') }}">
                  {[ echo $csrfField ?? ""; ]}
                  <button type="submit" class="btn btn-primary">
                    <i class="ti ti-shield-plus me-1"></i>2FA Etkinleştir
                  </button>
                </form>
              @endif
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">2FA Hakkında</h3>
            </div>
            <div class="card-body">
              <ul class="list-unstyled">
                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Google Authenticator, Authy, Microsoft Authenticator desteği</li>
                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Her girişte 6 haneli tek kullanımlık kod</li>
                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>8 adet yedek kod (cihaz kaybolursa)</li>
                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Hesabınıza yetkisiz erişimi engeller</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
