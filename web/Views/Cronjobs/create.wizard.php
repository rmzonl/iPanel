<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ URL::base('cronjobs/main') }}">Cron İşleri</a></li>
              <li class="breadcrumb-item active">Yeni Cron</li>
            </ol>
          </nav>
          <h2 class="page-title"><i class="ti ti-clock me-2"></i>Yeni Cron İşi</h2>
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

          <form method="POST" action="{{ URL::base('cronjobs/store') }}">
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
                    <label class="form-label required">Başlık</label>
                    <input type="text" name="title" class="form-control" placeholder="Günlük temizlik" required/>
                  </div>
                  <div class="col-12">
                    <label class="form-label required">Zamanlama (Cron İfadesi)</label>
                    <input type="text" name="schedule" class="form-control font-monospace" placeholder="0 3 * * *" required/>
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                      {[
                        $presets = ['Her dakika'=>'* * * * *','Her saat'=>'0 * * * *','Günlük 03:00'=>'0 3 * * *','Haftalık'=>'0 3 * * 0','Aylık'=>'0 3 1 * *'];
                        foreach ($presets as $label => $expr) {
                            echo "<button type='button' class='btn btn-sm btn-outline-secondary' onclick=\"document.querySelector('[name=schedule]').value='$expr'\">$label</button>";
                        }
                      ]}
                    </div>
                  </div>
                  <div class="col-12">
                    <label class="form-label required">Komut</label>
                    <textarea name="command" class="form-control font-monospace" rows="3" placeholder="/usr/bin/php /var/www/site.com/artisan schedule:run" required></textarea>
                  </div>
                </div>
              </div>
              <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ URL::base('cronjobs/main') }}" class="btn btn-ghost-secondary">İptal</a>
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
