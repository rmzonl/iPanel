<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ URL::base('dns/main') }}">DNS</a></li>
              <li class="breadcrumb-item"><a href="{{ URL::base('dns/records/' . $zone->id) }}">{{ $zone->domain_name ?? 'Zone' }}</a></li>
              <li class="breadcrumb-item active">Yeni Kayıt</li>
            </ol>
          </nav>
          <h2 class="page-title">DNS Kaydı Ekle</h2>
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

          <form method="POST" action="{{ URL::base('dns/storeRecord') }}">
        {[ echo $csrfField ?? ""; ]}
            <input type="hidden" name="zone_id" value="{{ $zone->id }}"/>
            <div class="card">
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label required">Kayıt Türü</label>
                    <select name="type" class="form-select" id="dnsType" onchange="togglePriority(this.value)">
                      <option value="A">A</option>
                      <option value="AAAA">AAAA</option>
                      <option value="CNAME">CNAME</option>
                      <option value="MX">MX</option>
                      <option value="TXT">TXT</option>
                      <option value="NS">NS</option>
                      <option value="SRV">SRV</option>
                      <option value="CAA">CAA</option>
                      <option value="PTR">PTR</option>
                    </select>
                  </div>
                  <div class="col-md-8">
                    <label class="form-label required">Ad (Host)</label>
                    <input type="text" name="name" class="form-control font-monospace" placeholder="@ veya subdomain" required/>
                  </div>
                  <div class="col-12">
                    <label class="form-label required">Değer (Points To)</label>
                    <input type="text" name="value" class="form-control font-monospace" placeholder="IP adresi veya hostname" required/>
                  </div>
                  <div class="col-md-6" id="priorityGroup" style="display:none">
                    <label class="form-label">Öncelik</label>
                    <input type="number" name="priority" class="form-control" value="10" min="0"/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">TTL (saniye)</label>
                    <select name="ttl" class="form-select">
                      <option value="300">300 (5 dk)</option>
                      <option value="3600" selected>3600 (1 saat)</option>
                      <option value="14400">14400 (4 saat)</option>
                      <option value="86400">86400 (1 gün)</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ URL::base('dns/records/' . $zone->id) }}" class="btn btn-ghost-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">
                  <i class="ti ti-device-floppy me-1"></i> Ekle
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
function togglePriority(val) {
  document.getElementById('priorityGroup').style.display = (val === 'MX' || val === 'SRV') ? 'block' : 'none';
}
</script>
