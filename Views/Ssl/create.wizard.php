<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ URL::base('ssl/main') }}">SSL</a></li>
              <li class="breadcrumb-item active">Sertifika Ekle</li>
            </ol>
          </nav>
          <h2 class="page-title"><i class="ti ti-lock me-2"></i>SSL Sertifikası Ekle</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row justify-content-center">
        <div class="col-lg-7">

          @if(!empty($error))
            <div class="alert alert-danger mb-3"><i class="ti ti-alert-circle me-2"></i>{{ $error }}</div>
          @endif

          <form method="POST" action="{{ URL::base('ssl/store') }}" id="sslForm">
            <div class="card">
              <div class="card-header"><h3 class="card-title">Temel Bilgiler</h3></div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-8">
                    <label class="form-label required">Domain</label>
                    <select name="domain_id" class="form-select" required>
                      <option value="">Seçin...</option>
                      @foreach($domains as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label required">Sertifika Türü</label>
                    <select name="type" class="form-select" id="certType" onchange="toggleCertFields(this.value)">
                      <option value="letsencrypt">Let's Encrypt (Ücretsiz)</option>
                      <option value="paid">Ücretli SSL</option>
                      <option value="self_signed">Self-Signed</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="card mt-3" id="manualCertFields" style="display:none">
              <div class="card-header"><h3 class="card-title">Sertifika Dosyaları</h3></div>
              <div class="card-body">
                <div class="mb-3">
                  <label class="form-label">Sertifika (cert.pem)</label>
                  <textarea name="cert_file" class="form-control font-monospace" rows="6" placeholder="-----BEGIN CERTIFICATE-----"></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Özel Anahtar (key.pem)</label>
                  <textarea name="key_file" class="form-control font-monospace" rows="6" placeholder="-----BEGIN PRIVATE KEY-----"></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">CA Chain (ca_bundle.pem)</label>
                  <textarea name="chain_file" class="form-control font-monospace" rows="4" placeholder="-----BEGIN CERTIFICATE-----"></textarea>
                </div>
              </div>
            </div>

            <div class="card mt-3">
              <div class="card-body">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="auto_renew" id="autoRenew" value="1" checked/>
                  <label class="form-check-label" for="autoRenew">Otomatik yenile (Let's Encrypt için)</label>
                </div>
              </div>
              <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ URL::base('ssl/main') }}" class="btn btn-ghost-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">
                  <i class="ti ti-lock me-1"></i> Sertifika Oluştur
                </button>
              </div>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
function toggleCertFields(val) {
  document.getElementById('manualCertFields').style.display = val === 'paid' ? 'block' : 'none';
}
</script>
