<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title">Dashboard</h2>
          <div class="text-muted mt-1">Sunucu genel durumu</div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">

      {-- Stats Row --}
      <div class="row row-deck row-cards mb-4">

        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="subheader">Toplam Müşteri</div>
              </div>
              <div class="h1 mb-3">{{ $stats['clients'] ?? 0 }}</div>
              <div class="d-flex mb-2">
                <div>
                  <span class="text-success me-1"><i class="ti ti-users"></i></span>
                  <span class="text-muted">Kayıtlı müşteri</span>
                </div>
                <div class="ms-auto">
                  <a href="{{ URL::base('clients/main') }}" class="btn btn-sm btn-primary">Görüntüle</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="subheader">Toplam Site</div>
              </div>
              <div class="h1 mb-3">{{ $stats['sites'] ?? 0 }}</div>
              <div class="d-flex mb-2">
                <div>
                  <span class="text-blue me-1"><i class="ti ti-world"></i></span>
                  <span class="text-muted">Aktif site</span>
                </div>
                <div class="ms-auto">
                  <a href="{{ URL::base('sites/main') }}" class="btn btn-sm btn-primary">Görüntüle</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="subheader">Toplam Domain</div>
              </div>
              <div class="h1 mb-3">{{ $stats['domains'] ?? 0 }}</div>
              <div class="d-flex mb-2">
                <div>
                  <span class="text-orange me-1"><i class="ti ti-link"></i></span>
                  <span class="text-muted">Kayıtlı domain</span>
                </div>
                <div class="ms-auto">
                  <a href="{{ URL::base('domains/main') }}" class="btn btn-sm btn-primary">Görüntüle</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="subheader">Aktif SSL</div>
              </div>
              <div class="h1 mb-3">{{ $stats['ssl'] ?? 0 }}</div>
              <div class="d-flex mb-2">
                <div>
                  <span class="text-green me-1"><i class="ti ti-lock"></i></span>
                  <span class="text-muted">Geçerli sertifika</span>
                </div>
                <div class="ms-auto">
                  <a href="{{ URL::base('ssl/main') }}" class="btn btn-sm btn-primary">Görüntüle</a>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="row row-deck row-cards">

        {-- Recent Clients --}
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="ti ti-users me-2"></i>Son Müşteriler</h3>
              <div class="card-options">
                <a href="{{ URL::base('clients/main') }}" class="btn btn-sm btn-primary">Tümü</a>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table">
                <thead>
                  <tr>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Durum</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($recentClients as $client)
                    <tr>
                      <td>{{ $client->first_name }} {{ $client->last_name }}</td>
                      <td class="text-muted">{{ $client->email }}</td>
                      <td>
                        @if($client->status === 'active')
                          <span class="badge bg-success">Aktif</span>
                        @elseif($client->status === 'suspended')
                          <span class="badge bg-warning">Askıya Alındı</span>
                        @else
                          <span class="badge bg-danger">Sonlandırıldı</span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Henüz müşteri yok</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        {-- Recent Sites --}
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="ti ti-world me-2"></i>Son Siteler</h3>
              <div class="card-options">
                <a href="{{ URL::base('sites/main') }}" class="btn btn-sm btn-primary">Tümü</a>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table">
                <thead>
                  <tr>
                    <th>Domain</th>
                    <th>PHP</th>
                    <th>Durum</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($recentSites as $site)
                    <tr>
                      <td>{{ $site->domain }}</td>
                      <td class="text-muted">{{ $site->php_version }}</td>
                      <td>
                        @if($site->status === 'active')
                          <span class="badge bg-success">Aktif</span>
                        @elseif($site->status === 'suspended')
                          <span class="badge bg-warning">Askıda</span>
                        @else
                          <span class="badge bg-danger">Silindi</span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Henüz site yok</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
