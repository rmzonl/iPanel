<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ URL::base('sites/main') }}">Siteler</a></li>
              <li class="breadcrumb-item active">Yeni Site</li>
            </ol>
          </nav>
          <h2 class="page-title"><i class="ti ti-world-plus me-2"></i>Yeni Site</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row justify-content-center">
        <div class="col-lg-8">

          @if(!empty($error))
            <div class="alert alert-danger mb-3"><i class="ti ti-alert-circle me-2"></i>{{ $error }}</div>
          @endif

          <form method="POST" action="{{ URL::base('sites/store') }}">
            <div class="card">
              <div class="card-header"><h3 class="card-title">Site Bilgileri</h3></div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-8">
                    <label class="form-label required">Ana Domain</label>
                    <input type="text" name="domain" class="form-control" placeholder="ornek.com" value="{{ $old['domain'] ?? '' }}" required/>
                    <small class="text-muted">www olmadan girin</small>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label required">Müşteri</label>
                    <select name="client_id" class="form-select" required>
                      <option value="">Seçin...</option>
                      @foreach($clients as $c)
                        <option value="{{ $c->id }}" {[ echo (($old['client_id'] ?? '') == $c->id) ? 'selected' : ''; ]}>
                          {{ $c->first_name }} {{ $c->last_name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">IP Adresi</label>
                    <select name="ip_id" class="form-select">
                      <option value="">Sunucu varsayılanı</option>
                      @foreach($ipAddresses as $ip)
                        <option value="{{ $ip->id }}">{{ $ip->ip }} ({{ $ip->type }})</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">PHP Versiyonu</label>
                    <select name="php_version" class="form-select">
                      {[
                        $phpVersions = explode(',', $phpVersionsSetting ?? '7.4,8.0,8.1,8.2,8.3');
                        foreach ($phpVersions as $v) {
                            $v = trim($v);
                            $sel = (($old['php_version'] ?? '8.2') === $v) ? 'selected' : '';
                            echo "<option value=\"$v\" $sel>PHP $v</option>";
                        }
                      ]}
                    </select>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Document Root</label>
                    <input type="text" name="document_root" class="form-control" placeholder="/var/www/ornek.com/public_html" value="{{ $old['document_root'] ?? '' }}"/>
                    <small class="text-muted">Boş bırakılırsa otomatik oluşturulur</small>
                  </div>
                </div>
              </div>
            </div>

            <div class="card mt-3">
              <div class="card-header"><h3 class="card-title">Kota Ayarları</h3></div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Disk Kotası (MB)</label>
                    <input type="number" name="disk_quota" class="form-control" value="{{ $old['disk_quota'] ?? 0 }}" min="0"/>
                    <small class="text-muted">0 = Sınırsız</small>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Bant Genişliği Kotası (MB)</label>
                    <input type="number" name="bandwidth_quota" class="form-control" value="{{ $old['bandwidth_quota'] ?? 0 }}" min="0"/>
                    <small class="text-muted">0 = Sınırsız</small>
                  </div>
                </div>
              </div>
              <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ URL::base('sites/main') }}" class="btn btn-ghost-secondary">İptal</a>
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
