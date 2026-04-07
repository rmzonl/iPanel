<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-database me-2"></i>Veritabanları</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('databases/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Yeni Veritabanı
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
                <th>Veritabanı</th>
                <th>Kullanıcı</th>
                <th>Site</th>
                <th>Karakter Seti</th>
                <th>Boyut (MB)</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($databases as $db)
                <tr>
                  <td><strong class="font-monospace">{{ $db->db_name }}</strong></td>
                  <td class="text-muted font-monospace">{{ $db->db_user }}</td>
                  <td class="text-muted">{{ $db->site_domain ?? '—' }}</td>
                  <td><span class="badge bg-secondary-lt">{{ $db->charset }}</span></td>
                  <td class="text-muted">{{ $db->size_mb ?: '—' }}</td>
                  <td>
                    @if($db->status === 'active')
                      <span class="badge bg-success-lt">Aktif</span>
                    @else
                      <span class="badge bg-warning-lt">Askıda</span>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="{{ URL::base('databases/edit/' . $db->id) }}" class="btn btn-outline-primary" title="Düzenle"><i class="ti ti-edit"></i></a>
                      <a href="{{ URL::base('databases/delete/' . $db->id) }}" class="btn btn-outline-danger" title="Sil"
                         onclick="return confirm('Bu veritabanını silmek istediğinizden emin misiniz?')"><i class="ti ti-trash"></i></a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-5">
                    <div class="empty">
                      <div class="empty-icon"><i class="ti ti-database" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
                      <p class="empty-title">Henüz veritabanı yok</p>
                      <a href="{{ URL::base('databases/create') }}" class="btn btn-primary mt-3">
                        <i class="ti ti-plus me-1"></i> Veritabanı Ekle
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
