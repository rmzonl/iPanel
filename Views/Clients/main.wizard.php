<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-users me-2"></i>Müşteriler</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('clients/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Yeni Müşteri
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-header">
          <div class="input-group input-group-sm" style="max-width:300px">
            <span class="input-group-text"><i class="ti ti-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Müşteri ara..."/>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Ad Soyad</th>
                <th>Firma</th>
                <th>E-posta</th>
                <th>Telefon</th>
                <th>Durum</th>
                <th>Kayıt</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody id="clientTable">
              @forelse($clients as $client)
                <tr>
                  <td class="text-muted">{{ $client->id }}</td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="avatar avatar-sm bg-primary text-white">
                        {[ echo strtoupper(substr($client->first_name, 0, 1) . substr($client->last_name, 0, 1)); ]}
                      </span>
                      {{ $client->first_name }} {{ $client->last_name }}
                    </div>
                  </td>
                  <td class="text-muted">{{ $client->company_name ?: '—' }}</td>
                  <td>{{ $client->email }}</td>
                  <td class="text-muted">{{ $client->phone ?: '—' }}</td>
                  <td>
                    @if($client->status === 'active')
                      <span class="badge bg-success-lt">Aktif</span>
                    @elseif($client->status === 'suspended')
                      <span class="badge bg-warning-lt">Askıya Alındı</span>
                    @else
                      <span class="badge bg-danger-lt">Sonlandırıldı</span>
                    @endif
                  </td>
                  <td class="text-muted">{[ echo date('d.m.Y', strtotime($client->created_at)); ]}</td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="{{ URL::base('sites/main?client_id=' . $client->id) }}" class="btn btn-outline-secondary" title="Siteler">
                        <i class="ti ti-world"></i>
                      </a>
                      <a href="{{ URL::base('clients/edit/' . $client->id) }}" class="btn btn-outline-primary" title="Düzenle">
                        <i class="ti ti-edit"></i>
                      </a>
                      <a href="{{ URL::base('clients/delete/' . $client->id) }}" class="btn btn-outline-danger" title="Sil"
                         onclick="return confirm('Bu müşteriyi silmek istediğinizden emin misiniz?')">
                        <i class="ti ti-trash"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center py-5">
                    <div class="empty">
                      <div class="empty-icon"><i class="ti ti-users" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
                      <p class="empty-title">Henüz müşteri yok</p>
                      <p class="empty-subtitle text-muted">İlk müşterinizi oluşturmak için butona tıklayın.</p>
                      <a href="{{ URL::base('clients/create') }}" class="btn btn-primary mt-3">
                        <i class="ti ti-plus me-1"></i> Yeni Müşteri Ekle
                      </a>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
  var val = this.value.toLowerCase();
  document.querySelectorAll('#clientTable tr').forEach(function(row) {
    row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
  });
});
</script>
