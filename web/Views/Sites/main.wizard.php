<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-world me-2"></i>Siteler</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('sites/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Yeni Site
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>Domain</th>
                <th>Müşteri</th>
                <th>IP</th>
                <th>PHP</th>
                <th>Disk (MB)</th>
                <th>Durum</th>
                <th>Tarih</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($sites as $site)
                <tr>
                  <td>
                    <strong>{{ $site->domain }}</strong>
                  </td>
                  <td>
                    <a href="{{ URL::base('clients/edit/' . $site->client_id) }}" class="text-decoration-none">
                      {{ $site->client_name ?? '—' }}
                    </a>
                  </td>
                  <td class="text-muted">{{ $site->ip ?? '—' }}</td>
                  <td><span class="badge bg-blue-lt">PHP {{ $site->php_version }}</span></td>
                  <td class="text-muted">
                    {[ $quota = $site->disk_quota > 0 ? $site->disk_quota : '∞'; ]}
                    {{ $site->disk_used }} / {{ $quota }}
                  </td>
                  <td>
                    @if($site->status === 'active')
                      <span class="badge bg-success-lt">Aktif</span>
                    @elseif($site->status === 'suspended')
                      <span class="badge bg-warning-lt">Askıda</span>
                    @else
                      <span class="badge bg-danger-lt">Silindi</span>
                    @endif
                  </td>
                  <td class="text-muted">{[ echo date('d.m.Y', strtotime($site->created_at)); ]}</td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="{{ URL::base('domains/main?site_id=' . $site->id) }}" class="btn btn-outline-secondary" title="Domainler"><i class="ti ti-link"></i></a>
                      <a href="{{ URL::base('sites/edit/' . $site->id) }}" class="btn btn-outline-primary" title="Düzenle"><i class="ti ti-edit"></i></a>
                      <a href="{{ URL::base('sites/delete/' . $site->id) }}" class="btn btn-outline-danger" title="Sil"
                         onclick="return confirm('Bu siteyi silmek istediğinizden emin misiniz?')"><i class="ti ti-trash"></i></a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center py-5">
                    <div class="empty">
                      <div class="empty-icon"><i class="ti ti-world" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
                      <p class="empty-title">Henüz site yok</p>
                      <a href="{{ URL::base('sites/create') }}" class="btn btn-primary mt-3">
                        <i class="ti ti-plus me-1"></i> Yeni Site Ekle
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
