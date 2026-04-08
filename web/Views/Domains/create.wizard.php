<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ URL::base('domains/main') }}">Domainler</a></li>
              <li class="breadcrumb-item active">Yeni Domain</li>
            </ol>
          </nav>
          <h2 class="page-title"><i class="ti ti-link me-2"></i>Yeni Domain Ekle</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row justify-content-center">
        <div class="col-lg-6">

          @if(!empty($error))
            <div class="alert alert-danger mb-3"><i class="ti ti-alert-circle me-2"></i>{{ $error }}</div>
          @endif

          <form method="POST" action="{{ URL::base('domains/store') }}">
        {[ echo $csrfField ?? ""; ]}
            <div class="card">
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label required">Site</label>
                    <select name="site_id" class="form-select" required>
                      <option value="">Seçin...</option>
                      @foreach($sites as $s)
                        <option value="{{ $s->id }}">{{ $s->domain }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-12">
                    <label class="form-label required">Domain Adı</label>
                    <input type="text" name="name" class="form-control" placeholder="subdomain.ornek.com" required/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required">Tür</label>
                    <select name="type" class="form-select" id="domainType" onchange="toggleRedirect(this.value)">
                      <option value="subdomain">Subdomain</option>
                      <option value="addon">Ek Domain</option>
                      <option value="alias">Alias</option>
                    </select>
                  </div>
                  <div class="col-md-6" id="redirectGroup" style="display:none">
                    <label class="form-label">Yönlendir</label>
                    <input type="text" name="redirect_to" class="form-control" placeholder="https://hedef.com"/>
                  </div>
                </div>
              </div>
              <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ URL::base('domains/main') }}" class="btn btn-ghost-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">
                  <i class="ti ti-device-floppy me-1"></i> Kaydet
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
function toggleRedirect(val) {
  document.getElementById('redirectGroup').style.display = val === 'alias' ? 'block' : 'none';
}
</script>
