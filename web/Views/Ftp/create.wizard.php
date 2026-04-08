<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ URL::base('ftp/main') }}">FTP</a></li>
              <li class="breadcrumb-item active">Yeni FTP</li>
            </ol>
          </nav>
          <h2 class="page-title"><i class="ti ti-folder me-2"></i>Yeni FTP Hesabı</h2>
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

          <form method="POST" action="{{ URL::base('ftp/store') }}">
        {[ echo $csrfField ?? ""; ]}
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
                    <input type="text" name="username" class="form-control" required/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required">Şifre</label>
                    <input type="password" name="password" class="form-control" required/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Kota (MB)</label>
                    <input type="number" name="quota" class="form-control" value="0" min="0"/>
                    <small class="text-muted">0 = Sınırsız</small>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Home Dizin</label>
                    <input type="text" name="home_dir" class="form-control font-monospace" placeholder="/var/www/ornek.com"/>
                    <small class="text-muted">Boş bırakılırsa site kökü kullanılır</small>
                  </div>
                </div>
              </div>
              <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ URL::base('ftp/main') }}" class="btn btn-ghost-secondary">İptal</a>
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
