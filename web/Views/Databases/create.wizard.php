<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ URL::base('databases/main') }}">Veritabanları</a></li>
              <li class="breadcrumb-item active">Yeni Veritabanı</li>
            </ol>
          </nav>
          <h2 class="page-title"><i class="ti ti-database me-2"></i>Yeni Veritabanı</h2>
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

          <form method="POST" action="{{ URL::base('databases/store') }}">
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
                  <div class="col-md-6">
                    <label class="form-label required">Veritabanı Adı</label>
                    <input type="text" name="db_name" class="form-control font-monospace" placeholder="site_db" required/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required">Kullanıcı Adı</label>
                    <input type="text" name="db_user" class="form-control font-monospace" placeholder="site_user" required/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required">Şifre</label>
                    <input type="password" name="db_password" class="form-control" required/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Karakter Seti</label>
                    <select name="charset" class="form-select">
                      <option value="utf8mb4" selected>utf8mb4 (Önerilen)</option>
                      <option value="utf8">utf8</option>
                      <option value="latin1">latin1</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ URL::base('databases/main') }}" class="btn btn-ghost-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">
                  <i class="ti ti-device-floppy me-1"></i> Oluştur
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
