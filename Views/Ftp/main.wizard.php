<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-folder me-2"></i>FTP Hesapları</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('ftp/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Yeni FTP
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
                <th>Kullanıcı</th>
                <th>Site</th>
                <th>Home Dizin</th>
                <th>Kota (MB)</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($ftpAccounts as $ftp)
                <tr>
                  <td><strong>{{ $ftp->username }}</strong></td>
                  <td class="text-muted">{{ $ftp->site_domain ?? '—' }}</td>
                  <td class="text-muted font-monospace">{{ $ftp->home_dir ?: '—' }}</td>
                  <td class="text-muted">{{ $ftp->quota ?: '∞' }}</td>
                  <td>
                    @if($ftp->status === 'active')
                      <span class="badge bg-success-lt">Aktif</span>
                    @else
                      <span class="badge bg-warning-lt">Askıda</span>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="{{ URL::base('ftp/edit/' . $ftp->id) }}" class="btn btn-outline-primary" title="Düzenle"><i class="ti ti-edit"></i></a>
                      <a href="{{ URL::base('ftp/delete/' . $ftp->id) }}" class="btn btn-outline-danger" title="Sil"
                         onclick="return confirm('Bu FTP hesabını silmek istediğinizden emin misiniz?')"><i class="ti ti-trash"></i></a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-5">
                    <div class="empty">
                      <div class="empty-icon"><i class="ti ti-folder" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
                      <p class="empty-title">Henüz FTP hesabı yok</p>
                      <a href="{{ URL::base('ftp/create') }}" class="btn btn-primary mt-3">
                        <i class="ti ti-plus me-1"></i> FTP Ekle
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
