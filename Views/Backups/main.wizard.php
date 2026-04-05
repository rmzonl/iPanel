<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-archive me-2"></i>Yedeklemeler</h2>
        </div>
        <div class="col-auto ms-auto">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackupModal">
            <i class="ti ti-plus me-1"></i> Yeni Yedek
          </button>
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
                <th>Dosya</th>
                <th>Site / Müşteri</th>
                <th>Tür</th>
                <th>Boyut (MB)</th>
                <th>Başlangıç</th>
                <th>Tamamlanma</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($backups as $backup)
                <tr>
                  <td class="text-muted font-monospace small">{{ $backup->filename ?: '—' }}</td>
                  <td class="text-muted">{{ $backup->site_domain ?? $backup->client_name ?? '—' }}</td>
                  <td>
                    {[ $typeMap = ['full'=>'Tam','database'=>'Veritabanı','files'=>'Dosyalar','email'=>'E-posta']; ]}
                    <span class="badge bg-secondary-lt">{{ $typeMap[$backup->type] ?? $backup->type }}</span>
                  </td>
                  <td class="text-muted">{{ $backup->size_mb ?: '—' }}</td>
                  <td class="text-muted small">
                    {[ echo $backup->started_at ? date('d.m.Y H:i', strtotime($backup->started_at)) : '—'; ]}
                  </td>
                  <td class="text-muted small">
                    {[ echo $backup->completed_at ? date('d.m.Y H:i', strtotime($backup->completed_at)) : '—'; ]}
                  </td>
                  <td>
                    {[
                      $bMap = ['pending'=>['bg-secondary-lt','Bekliyor'],'running'=>['bg-yellow-lt','Çalışıyor'],'completed'=>['bg-success-lt','Tamamlandı'],'failed'=>['bg-danger-lt','Başarısız']];
                      $bs = $bMap[$backup->status] ?? ['bg-secondary-lt', $backup->status];
                    ]}
                    <span class="badge {{ $bs[0] }}">{{ $bs[1] }}</span>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      @if($backup->status === 'completed')
                        <a href="{{ URL::base('backups/download/' . $backup->id) }}" class="btn btn-outline-success" title="İndir"><i class="ti ti-download"></i></a>
                      @endif
                      <a href="{{ URL::base('backups/delete/' . $backup->id) }}" class="btn btn-outline-danger" title="Sil"
                         onclick="return confirm('Bu yedeği silmek istediğinizden emin misiniz?')"><i class="ti ti-trash"></i></a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center py-5">
                    <div class="empty">
                      <div class="empty-icon"><i class="ti ti-archive" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
                      <p class="empty-title">Henüz yedek yok</p>
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

{-- Create Backup Modal --}
<div class="modal modal-blur fade" id="createBackupModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ URL::base('backups/store') }}">
        <div class="modal-header">
          <h5 class="modal-title">Yeni Yedek Oluştur</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Site</label>
            <select name="site_id" class="form-select">
              <option value="">Tüm siteler</option>
              @foreach($sites as $s)
                <option value="{{ $s->id }}">{{ $s->domain }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Yedek Türü</label>
            <select name="type" class="form-select">
              <option value="full">Tam Yedek</option>
              <option value="database">Yalnızca Veritabanı</option>
              <option value="files">Yalnızca Dosyalar</option>
              <option value="email">Yalnızca E-posta</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">İptal</button>
          <button type="submit" class="btn btn-primary">
            <i class="ti ti-archive me-1"></i> Başlat
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
