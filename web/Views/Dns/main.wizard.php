<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-server me-2"></i>DNS Yönetimi</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">DNS Zonları</h3>
          <div class="card-options text-muted small">Bir domain seçerek kayıtlarını yönetin</div>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Domain</th>
                <th>Site</th>
                <th>TTL</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($zones as $zone)
                <tr>
                  <td><strong>{{ $zone->domain_name ?? '—' }}</strong></td>
                  <td class="text-muted">{{ $zone->site_domain ?? '—' }}</td>
                  <td class="text-muted">{{ $zone->ttl }}</td>
                  <td>
                    @if($zone->status === 'active')
                      <span class="badge bg-success-lt">Aktif</span>
                    @else
                      <span class="badge bg-secondary-lt">Pasif</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ URL::base('dns/records/' . $zone->id) }}" class="btn btn-sm btn-primary">
                      <i class="ti ti-list me-1"></i> Kayıtlar
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-5">
                    <div class="empty">
                      <div class="empty-icon"><i class="ti ti-server" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
                      <p class="empty-title">DNS zonu bulunamadı</p>
                      <p class="empty-subtitle text-muted">Domainler eklendikçe DNS zonları otomatik oluşturulur.</p>
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
