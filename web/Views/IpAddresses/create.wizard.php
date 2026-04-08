<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ URL::base('ipaddresses/main') }}">IP Adresleri</a></li>
              <li class="breadcrumb-item active">IP Ekle</li>
            </ol>
          </nav>
          <h2 class="page-title"><i class="ti ti-network me-2"></i>IP Adresi Ekle</h2>
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

          <form method="POST" action="{{ URL::base('ipaddresses/store') }}">
        {[ echo $csrfField ?? ""; ]}
            <div class="card">
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required">IP Adresi</label>
                    <input type="text" name="ip" class="form-control font-monospace" placeholder="192.168.1.100" required/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Netmask</label>
                    <input type="text" name="netmask" class="form-control font-monospace" placeholder="255.255.255.0"/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Gateway</label>
                    <input type="text" name="gateway" class="form-control font-monospace" placeholder="192.168.1.1"/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required">Tür</label>
                    <select name="type" class="form-select" onchange="toggleClient(this.value)">
                      <option value="server">Sunucu IP</option>
                      <option value="shared">Paylaşımlı</option>
                      <option value="dedicated">Özel (Müşteriye Tahsisli)</option>
                    </select>
                  </div>
                  <div class="col-12" id="clientGroup" style="display:none">
                    <label class="form-label">Müşteri</label>
                    <select name="client_id" class="form-select">
                      <option value="">Seçin...</option>
                      @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Notlar</label>
                    <input type="text" name="notes" class="form-control" placeholder="Opsiyonel açıklama"/>
                  </div>
                </div>
              </div>
              <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ URL::base('ipaddresses/main') }}" class="btn btn-ghost-secondary">İptal</a>
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
function toggleClient(val) {
  document.getElementById('clientGroup').style.display = val === 'dedicated' ? 'block' : 'none';
}
</script>
