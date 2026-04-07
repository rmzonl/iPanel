<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ URL::base('dns/main') }}">DNS</a></li>
              <li class="breadcrumb-item active">{{ $zone->domain_name ?? 'Zone' }}</li>
            </ol>
          </nav>
          <h2 class="page-title"><i class="ti ti-server me-2"></i>DNS Kayıtları — {{ $zone->domain_name ?? '' }}</h2>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ URL::base('dns/createRecord/' . $zone->id) }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Kayıt Ekle
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
                <th>Tür</th>
                <th>Ad</th>
                <th>Değer</th>
                <th>Öncelik</th>
                <th>TTL</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($records as $record)
                <tr>
                  <td>
                    {[
                      $typeColors = ['A'=>'blue','AAAA'=>'indigo','CNAME'=>'cyan','MX'=>'green','TXT'=>'yellow','NS'=>'orange','SRV'=>'pink','CAA'=>'purple','PTR'=>'teal'];
                      $tc = $typeColors[$record->type] ?? 'secondary';
                    ]}
                    <span class="badge bg-{{ $tc }}-lt font-monospace">{{ $record->type }}</span>
                  </td>
                  <td class="font-monospace">{{ $record->name }}</td>
                  <td class="text-muted font-monospace" style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    {{ $record->value }}
                  </td>
                  <td class="text-muted">
                    @if(in_array($record->type, ['MX', 'SRV']))
                      {{ $record->priority }}
                    @else
                      —
                    @endif
                  </td>
                  <td class="text-muted">{{ $record->ttl }}</td>
                  <td>
                    <a href="{{ URL::base('dns/deleteRecord/' . $record->id) }}" class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Bu kaydı silmek istediğinizden emin misiniz?')">
                      <i class="ti ti-trash"></i>
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">
                    <i class="ti ti-info-circle me-1"></i> Bu zona ait kayıt bulunamadı.
                    <a href="{{ URL::base('dns/createRecord/' . $zone->id) }}">Kayıt ekle</a>
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
