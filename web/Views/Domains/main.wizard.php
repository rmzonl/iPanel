<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-link me-2"></i>Domain Yönetimi</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('domains/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Yeni Domain
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
                <th>Site</th>
                <th>Tür</th>
                <th>Yönlendirme</th>
                <th>SSL</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($domains as $domain)
                <tr>
                  <td><strong>{{ $domain->name }}</strong></td>
                  <td class="text-muted">{{ $domain->site_domain ?? '—' }}</td>
                  <td>
                    {[
                      $typeLabels = ['main'=>'Ana','addon'=>'Ek','subdomain'=>'Subdomain','alias'=>'Alias'];
                      $typeColors = ['main'=>'primary','addon'=>'blue','subdomain'=>'cyan','alias'=>'indigo'];
                      $lbl = $typeLabels[$domain->type] ?? $domain->type;
                      $clr = $typeColors[$domain->type] ?? 'secondary';
                    ]}
                    <span class="badge bg-{{ $clr }}-lt">{{ $lbl }}</span>
                  </td>
                  <td class="text-muted">{{ $domain->redirect_to ?: '—' }}</td>
                  <td>
                    @if(!empty($domain->ssl_status))
                      <span class="badge bg-success-lt"><i class="ti ti-lock me-1"></i>{{ $domain->ssl_status }}</span>
                    @else
                      <span class="badge bg-secondary-lt">Yok</span>
                    @endif
                  </td>
                  <td>
                    @if($domain->status === 'active')
                      <span class="badge bg-success-lt">Aktif</span>
                    @else
                      <span class="badge bg-secondary-lt">Pasif</span>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="{{ URL::base('ssl/main?domain_id=' . $domain->id) }}" class="btn btn-outline-secondary" title="SSL"><i class="ti ti-lock"></i></a>
                      <a href="{{ URL::base('dns/main?domain_id=' . $domain->id) }}" class="btn btn-outline-secondary" title="DNS"><i class="ti ti-server"></i></a>
                      <a href="{{ URL::base('domains/delete/' . $domain->id) }}" class="btn btn-outline-danger" title="Sil"
                         onclick="return confirm('Bu domain\'i silmek istediğinizden emin misiniz?')"><i class="ti ti-trash"></i></a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-5">
                    <div class="empty">
                      <div class="empty-icon"><i class="ti ti-link" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
                      <p class="empty-title">Henüz domain yok</p>
                      <a href="{{ URL::base('domains/create') }}" class="btn btn-primary mt-3">
                        <i class="ti ti-plus me-1"></i> Domain Ekle
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
