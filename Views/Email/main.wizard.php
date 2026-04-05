<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-mail me-2"></i>E-posta Hesapları</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('email/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Yeni E-posta
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
                <th>E-posta</th>
                <th>Site</th>
                <th>Kota (MB)</th>
                <th>Kullanım</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($emails as $email)
                {[
                  $pct = $email->quota > 0 ? min(100, round(($email->used / $email->quota) * 100)) : 0;
                  $bar = $pct > 90 ? 'bg-danger' : ($pct > 70 ? 'bg-warning' : 'bg-primary');
                ]}
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="avatar avatar-sm bg-blue text-white"><i class="ti ti-mail"></i></span>
                      <strong>{{ $email->email }}</strong>
                    </div>
                  </td>
                  <td class="text-muted">{{ $email->site_domain ?? '—' }}</td>
                  <td class="text-muted">{{ $email->quota ?: '∞' }}</td>
                  <td style="min-width:120px">
                    <div class="d-flex align-items-center gap-2">
                      <div class="progress flex-grow-1" style="height:6px">
                        <div class="progress-bar {{ $bar }}" style="width:{{ $pct }}%"></div>
                      </div>
                      <small class="text-muted">{{ $email->used }} MB</small>
                    </div>
                  </td>
                  <td>
                    @if($email->status === 'active')
                      <span class="badge bg-success-lt">Aktif</span>
                    @else
                      <span class="badge bg-warning-lt">Askıda</span>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="{{ URL::base('email/edit/' . $email->id) }}" class="btn btn-outline-primary" title="Düzenle"><i class="ti ti-edit"></i></a>
                      <a href="{{ URL::base('email/delete/' . $email->id) }}" class="btn btn-outline-danger" title="Sil"
                         onclick="return confirm('Bu e-posta hesabını silmek istediğinizden emin misiniz?')"><i class="ti ti-trash"></i></a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-5">
                    <div class="empty">
                      <div class="empty-icon"><i class="ti ti-mail" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
                      <p class="empty-title">Henüz e-posta hesabı yok</p>
                      <a href="{{ URL::base('email/create') }}" class="btn btn-primary mt-3">
                        <i class="ti ti-plus me-1"></i> E-posta Ekle
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
