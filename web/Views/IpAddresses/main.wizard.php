<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-network me-2"></i>IP Adresleri</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('ipaddresses/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> IP Ekle
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">

      <div class="alert alert-info mb-3">
        <i class="ti ti-info-circle me-2"></i>
        <strong>Sunucu IP'si</strong> tüm siteler için varsayılan IP'dir.
        <strong>Paylaşımlı IP</strong> birden fazla site tarafından kullanılabilir.
        <strong>Özel IP</strong> yalnızca tek bir müşteriye tahsis edilir.
      </div>

      <div class="card">
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>IP Adresi</th>
                <th>Netmask</th>
                <th>Gateway</th>
                <th>Tür</th>
                <th>Müşteri</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($ipAddresses as $ip)
                <tr>
                  <td><strong class="font-monospace">{{ $ip->ip }}</strong></td>
                  <td class="text-muted font-monospace">{{ $ip->netmask ?: '—' }}</td>
                  <td class="text-muted font-monospace">{{ $ip->gateway ?: '—' }}</td>
                  <td>
                    {[
                      $ipTypeMap = ['server'=>['bg-red-lt','Sunucu'],'shared'=>['bg-blue-lt','Paylaşımlı'],'dedicated'=>['bg-green-lt','Özel']];
                      $it = $ipTypeMap[$ip->type] ?? ['bg-secondary-lt', $ip->type];
                    ]}
                    <span class="badge {{ $it[0] }}">{{ $it[1] }}</span>
                  </td>
                  <td class="text-muted">{{ $ip->client_name ?? '—' }}</td>
                  <td>
                    @if($ip->status === 'active')
                      <span class="badge bg-success-lt">Aktif</span>
                    @else
                      <span class="badge bg-secondary-lt">Pasif</span>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="{{ URL::base('ipaddresses/edit/' . $ip->id) }}" class="btn btn-outline-primary" title="Düzenle"><i class="ti ti-edit"></i></a>
                      <a href="{{ URL::base('ipaddresses/delete/' . $ip->id) }}" class="btn btn-outline-danger" title="Sil"
                         onclick="return confirm('Bu IP adresini silmek istediğinizden emin misiniz?')"><i class="ti ti-trash"></i></a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-5">
                    <div class="empty">
                      <div class="empty-icon"><i class="ti ti-network" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
                      <p class="empty-title">Henüz IP adresi yok</p>
                      <a href="{{ URL::base('ipaddresses/create') }}" class="btn btn-primary mt-3">
                        <i class="ti ti-plus me-1"></i> IP Ekle
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
