<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-shield me-2"></i>Güvenlik Duvarı</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('firewall/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Kural Ekle
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
                <th>Ad</th>
                <th>Eylem</th>
                <th>Protokol</th>
                <th>Yön</th>
                <th>Kaynak IP</th>
                <th>Hedef Port</th>
                <th>Öncelik</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($rules as $rule)
                <tr>
                  <td><strong>{{ $rule->name }}</strong></td>
                  <td>
                    @if($rule->action === 'allow')
                      <span class="badge bg-success-lt"><i class="ti ti-check me-1"></i>İzin Ver</span>
                    @else
                      <span class="badge bg-danger-lt"><i class="ti ti-ban me-1"></i>Engelle</span>
                    @endif
                  </td>
                  <td><span class="badge bg-secondary-lt">{{ strtoupper($rule->protocol) }}</span></td>
                  <td class="text-muted">
                    {[ $dirMap = ['in'=>'Gelen','out'=>'Giden','both'=>'İkisi']; ]}
                    {{ $dirMap[$rule->direction] ?? $rule->direction }}
                  </td>
                  <td class="text-muted font-monospace">{{ $rule->source_ip ?: 'Tümü' }}</td>
                  <td class="text-muted font-monospace">{{ $rule->dest_port ?: 'Tümü' }}</td>
                  <td class="text-muted">{{ $rule->priority }}</td>
                  <td>
                    @if($rule->status === 'active')
                      <span class="badge bg-success-lt">Aktif</span>
                    @else
                      <span class="badge bg-secondary-lt">Pasif</span>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="{{ URL::base('firewall/toggle/' . $rule->id) }}" class="btn btn-outline-secondary" title="Aç/Kapat"><i class="ti ti-power"></i></a>
                      <a href="{{ URL::base('firewall/delete/' . $rule->id) }}" class="btn btn-outline-danger" title="Sil"
                         onclick="return confirm('Bu kuralı silmek istediğinizden emin misiniz?')"><i class="ti ti-trash"></i></a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center py-5">
                    <div class="empty">
                      <div class="empty-icon"><i class="ti ti-shield" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
                      <p class="empty-title">Henüz güvenlik duvarı kuralı yok</p>
                      <a href="{{ URL::base('firewall/create') }}" class="btn btn-primary mt-3">
                        <i class="ti ti-plus me-1"></i> Kural Ekle
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
