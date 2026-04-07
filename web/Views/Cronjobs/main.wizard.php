<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-clock me-2"></i>Cron İşleri</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('cronjobs/create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Yeni Cron
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
                <th>Başlık</th>
                <th>Site</th>
                <th>Zamanlama</th>
                <th>Komut</th>
                <th>Son Çalışma</th>
                <th>Durum</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($cronJobs as $job)
                <tr>
                  <td><strong>{{ $job->title }}</strong></td>
                  <td class="text-muted">{{ $job->site_domain ?? '—' }}</td>
                  <td><code class="bg-secondary-lt px-2 py-1 rounded">{{ $job->schedule }}</code></td>
                  <td class="text-muted font-monospace small" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    {{ $job->command }}
                  </td>
                  <td class="text-muted small">
                    {[ echo $job->last_run ? date('d.m.Y H:i', strtotime($job->last_run)) : 'Henüz çalışmadı'; ]}
                  </td>
                  <td>
                    @if($job->status === 'active')
                      <span class="badge bg-success-lt">Aktif</span>
                    @else
                      <span class="badge bg-secondary-lt">Pasif</span>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="{{ URL::base('cronjobs/toggle/' . $job->id) }}" class="btn btn-outline-secondary" title="Aç/Kapat"><i class="ti ti-power"></i></a>
                      <a href="{{ URL::base('cronjobs/delete/' . $job->id) }}" class="btn btn-outline-danger" title="Sil"
                         onclick="return confirm('Bu cron işini silmek istediğinizden emin misiniz?')"><i class="ti ti-trash"></i></a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-5">
                    <div class="empty">
                      <div class="empty-icon"><i class="ti ti-clock" style="font-size:3rem;color:var(--tblr-muted)"></i></div>
                      <p class="empty-title">Henüz cron işi yok</p>
                      <a href="{{ URL::base('cronjobs/create') }}" class="btn btn-primary mt-3">
                        <i class="ti ti-plus me-1"></i> Cron Ekle
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
