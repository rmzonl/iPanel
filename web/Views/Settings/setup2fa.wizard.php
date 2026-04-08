<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-shield-plus me-2"></i>2FA Kurulumu</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row justify-content-center">
        <div class="col-lg-7">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Adım 1 — QR Kodu Tarayın</h3>
            </div>
            <div class="card-body text-center">
              <p class="text-muted mb-3">Google Authenticator uygulamasını açın ve bu QR kodu tarayın.</p>

              <!-- QR Kod (qrcode.js ile dinamik) -->
              <div id="qrcode" class="d-inline-block p-3 bg-white border rounded mb-3"></div>

              <p class="text-muted small mb-0">QR kod tarayamıyorsanız bu kodu elle girin:</p>
              <code class="fs-5 fw-bold d-block mb-3" id="secretCode">{{ $totpSecret }}</code>
            </div>
          </div>

          <div class="card mt-3">
            <div class="card-header">
              <h3 class="card-title">Adım 2 — Kodu Doğrulayın</h3>
            </div>
            <div class="card-body">
              <p class="text-muted">Uygulamanızın gösterdiği 6 haneli kodu girin.</p>
              <form method="POST" action="{{ URL::base('settings/enable2fa') }}">
                {[ echo $csrfField ?? ""; ]}
                <div class="mb-3">
                  <label class="form-label">Doğrulama Kodu</label>
                  <input type="text" name="code" class="form-control form-control-lg text-center"
                         placeholder="000000" maxlength="6" autofocus
                         inputmode="numeric" pattern="[0-9]*"
                         style="letter-spacing:.5em; font-size:1.8rem;">
                </div>
                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-primary">
                    <i class="ti ti-check me-1"></i>Doğrula ve Etkinleştir
                  </button>
                  <a href="{{ URL::base('settings/twoFactor') }}" class="btn btn-secondary">İptal</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
QRCode.toCanvas(
  document.createElement('canvas'),
  {[ echo json_encode($otpUri ?? ''); ]},
  { width: 200, errorCorrectionLevel: 'M' },
  function(err, canvas) {
    if (err) {
      document.getElementById('qrcode').innerHTML = '<p class="text-danger">QR kod oluşturulamadı. Kodu elle girin.</p>';
    } else {
      document.getElementById('qrcode').appendChild(canvas);
    }
  }
);
</script>
