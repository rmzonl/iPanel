<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ URL::base('firewall/main') }}">Güvenlik Duvarı</a></li>
              <li class="breadcrumb-item active">Kural Ekle</li>
            </ol>
          </nav>
          <h2 class="page-title"><i class="ti ti-shield me-2"></i>Güvenlik Duvarı Kuralı Ekle</h2>
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

          <form method="POST" action="{{ URL::base('firewall/store') }}">
        {[ echo $csrfField ?? ""; ]}
            <div class="card">
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label required">Kural Adı</label>
                    <input type="text" name="name" class="form-control" placeholder="SSH erişim engeli" required/>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label required">Eylem</label>
                    <div class="form-selectgroup">
                      <label class="form-selectgroup-item">
                        <input type="radio" name="action" value="allow" class="form-selectgroup-input" checked/>
                        <span class="form-selectgroup-label text-success"><i class="ti ti-check me-1"></i>İzin Ver</span>
                      </label>
                      <label class="form-selectgroup-item">
                        <input type="radio" name="action" value="deny" class="form-selectgroup-input"/>
                        <span class="form-selectgroup-label text-danger"><i class="ti ti-ban me-1"></i>Engelle</span>
                      </label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Protokol</label>
                    <select name="protocol" class="form-select">
                      <option value="tcp">TCP</option>
                      <option value="udp">UDP</option>
                      <option value="icmp">ICMP</option>
                      <option value="all">Tümü</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Yön</label>
                    <select name="direction" class="form-select">
                      <option value="in">Gelen</option>
                      <option value="out">Giden</option>
                      <option value="both">İkisi</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Kaynak IP</label>
                    <input type="text" name="source_ip" class="form-control font-monospace" placeholder="0.0.0.0/0 veya boş=tümü"/>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Hedef Port</label>
                    <input type="text" name="dest_port" class="form-control font-monospace" placeholder="22 veya 80,443"/>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Öncelik</label>
                    <input type="number" name="priority" class="form-control" value="0" min="0"/>
                  </div>
                </div>
              </div>
              <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ URL::base('firewall/main') }}" class="btn btn-ghost-secondary">İptal</a>
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
