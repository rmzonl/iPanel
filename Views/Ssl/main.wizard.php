<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-lock me-2"></i>SSL Sertifikaları</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('ssl/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Sertifika Ekle
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
                <th>Tür</th>
                <th>Veriliş</th>
                <th>Bitiş</th>
                <th>Otomatik Yenileme</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($certificates as $cert)
                {[
                  $daysLeft = $cert->expires_at ? ceil((strtotime($cert->expires_at) - time()) / 86400) : null;
                  $expClass = $daysLeft !== null && $daysLeft < 30 ? 'text-danger' : 'text-muted';
                ]}
                <tr>
                  <td><strong>{{ $cert->domain_name ?? '—' }}</strong></td>
                  <td>
                    @if($cert->type === 'letsencrypt')
                      <span class="badge bg-green-lt">Let's Encrypt</span>
                    @elseif($cert->type === 'paid')
                      <span class="badge bg-blue-lt">Ücretli</span>
                    @else
                      <span class="badge bg-secondary-lt">Self-Signed</span>
                    @endif
                  </td>
                  <td class="text-muted">
                    {[ echo $cert->issued_at ? date('d.m.Y', strtotime($cert->issued_at)) : '—'; ]}
                  </td>
                  <td class="{{ $expClass }}">
                    {[
                      if ($cert->expires_at) {
                          echo date('d.m.Y', strtotime($cert->expires_at));
                          if ($daysLeft !== null) echo " ($daysLeft gün)";
                      } else {
                          echo '—';
                      }
                    ]}
                  </td>
                  <td>
                    @if($cert->auto_renew)
                      <span class="text-success"><i class="ti ti-check"></i> Evet</span>
                    @else
                      <span class="text-muted"><i class="ti ti-x"></i> Hayır</span>
                    @endif
                  </td>
                  <td>
                    {[
                      $statusMap = ['active'=>['bg-success-lt','Aktif'],'expired'=>['bg-danger-lt','Süresi Doldu'],'pending'=>['bg-yellow-lt','Bekliyor'],'failed'=>['bg-red-lt','Başarısız']];
                      $s = $statusMap[$cert->status] ?? ['bg-secondary-lt', $cert->status];
                    ]}
                    <span class="badge {{ $s[0] }}">{{ $s[1] }}</span>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="{{ URL::base('ssl/renew/' . $cert->id) }}" class="btn btn-outline-success" title="Yenile"><i class="ti ti-refresh"></i></a>
                      <a href="{{ URL::base('ssl/delete/' . $cert->id) }}" class="btn btn-outline-danger" title="Sil"
                         onclick="return confirm('Sertifikayı silmek istediğinizden emin misiniz?')"><i class="ti ti-trash"></i></a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-5">
                    <div class="empty">
                      <div class="empty-icon"><i class="ti ti-lock" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
                      <p class="empty-title">Henüz SSL sertifikası yok</p>
                      <a href="{{ URL::base('ssl/create') }}" class="btn btn-primary mt-3">
                        <i class="ti ti-plus me-1"></i> Sertifika Ekle
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
