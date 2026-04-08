<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-key me-2"></i>Yedek Kodlar</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-2"></i>
            <strong>Önemli:</strong> Bu kodları güvenli bir yere kaydedin. Her kod yalnızca bir kez kullanılabilir.
            Bu sayfayı kapattığınızda kodlar bir daha gösterilmeyecek.
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">2FA Etkinleştirildi</h3>
              <span class="badge bg-success ms-2">Aktif</span>
            </div>
            <div class="card-body">
              <p class="text-muted">Cihazınıza erişiminizi kaybederseniz bu yedek kodlardan birini kullanabilirsiniz.</p>

              <div class="row g-2">
                @foreach($backupCodes as $code)
                  <div class="col-6">
                    <div class="input-group">
                      <code class="form-control text-center fw-bold fs-5">{{ $code }}</code>
                    </div>
                  </div>
                @endforeach
              </div>

              <div class="mt-4 d-flex gap-2">
                <button onclick="printCodes()" class="btn btn-secondary">
                  <i class="ti ti-printer me-1"></i>Yazdır
                </button>
                <button onclick="copyCodes()" class="btn btn-secondary">
                  <i class="ti ti-copy me-1"></i>Kopyala
                </button>
                <a href="{{ URL::base('settings/twoFactor') }}" class="btn btn-primary ms-auto">
                  <i class="ti ti-check me-1"></i>Anladım, Devam Et
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function printCodes() { window.print(); }

function copyCodes() {
  var codes = [];
  document.querySelectorAll('code.fw-bold').forEach(function(el) {
    codes.push(el.textContent.trim());
  });
  navigator.clipboard.writeText(codes.join('\n')).then(function() {
    alert('Kodlar panoya kopyalandı!');
  });
}
</script>
