@forelse($jobs as $job)
  {[ $statusColors = ['pending'=>'secondary','running'=>'azure','completed'=>'success','failed'=>'danger','cancelled'=>'muted'];
     $statusLabels = ['pending'=>'Bekliyor','running'=>'Çalışıyor','completed'=>'Tamamlandı','failed'=>'Başarısız','cancelled'=>'İptal'];
     $sc = $statusColors[$job->status] ?? 'secondary';
     $sl = $statusLabels[$job->status] ?? $job->status;
  ]}
  <tr data-status="{{ $job->status }}">
    <td><code class="small">{{ substr($job->uuid, 0, 8) }}…</code></td>
    <td><span class="badge bg-blue-lt">{{ $job->type }}</span></td>
    <td><span class="badge bg-{{ $sc }}-lt text-{{ $sc }}">{{ $sl }}</span></td>
    <td class="text-muted">{{ $job->username ?? '—' }}</td>
    <td class="text-muted small">{{ $job->created_at }}</td>
    <td class="text-muted small">{{ $job->started_at ?? '—' }}</td>
    <td class="text-muted small">{{ $job->completed_at ?? '—' }}</td>
    <td>
      @if(in_array($job->status, ['pending']))
        <button class="btn btn-sm btn-ghost-danger"
                onclick="cancelJob('{{ $job->uuid }}')">
          <i class="ti ti-x"></i>
        </button>
      @endif
      @if($job->result)
        <button class="btn btn-sm btn-ghost-secondary"
                onclick="showResult('{{ addslashes($job->result) }}')">
          <i class="ti ti-eye"></i>
        </button>
      @endif
    </td>
  </tr>
@empty
  <tr><td colspan="8" class="text-center text-muted py-5">Görev bulunamadı</td></tr>
@endforelse
