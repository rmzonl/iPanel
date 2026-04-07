<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ URL::base('email/main') }}">E-posta</a></li>
              <li class="breadcrumb-item active">Yeni E-posta</li>
            </ol>
          </nav>
          <h2 class="page-title"><i class="ti ti-mail me-2"></i>Yeni E-posta Hesabı</h2>
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

          <form method="POST" action="{{ URL::base('email/store') }}">
            <div class="card">
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required">Site</label>
                    <select name="site_id" class="form-select" required>
                      <option value="">Seçin...</option>
                      @foreach($sites as $s)
                        <option value="{{ $s->id }}">{{ $s->domain }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required">Kullanıcı Adı</label>
                    <div class="input-group">
                      <input type="text" name="username" class="form-control" placeholder="info" required/>
                      <span class="input-group-text text-muted">@ domain</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required">Şifre</label>
                    <input type="password" name="password" class="form-control" required/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Kota (MB)</label>
                    <input type="number" name="quota" class="form-control" value="1024" min="0"/>
                    <small class="text-muted">0 = Sınırsız</small>
                  </div>
                </div>
              </div>
              <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ URL::base('email/main') }}" class="btn btn-ghost-secondary">İptal</a>
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
