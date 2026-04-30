<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title">Dashboard</h2>
          <div class="text-muted mt-1 d-flex align-items-center gap-2">
            Sunucu genel durumu
            <span class="stat-badge-live" id="stats-last-update">Bağlanıyor…</span>
          </div>
        </div>
        <div class="col-auto">
          <span class="text-muted small">Uptime: <strong id="uptime">—</strong></span>
          &nbsp;&nbsp;
          <span class="text-muted small">Load: <strong id="load-avg">—</strong></span>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">

      <!-- ── Sistem Özeti ── -->
      <div class="row row-deck row-cards mb-3">

        <!-- CPU -->
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <i class="ti ti-cpu text-blue me-2 fs-3"></i>
                <div>
                  <div class="subheader">CPU Kullanımı</div>
                  <div class="h2 mb-0" id="cpu-pct">—</div>
                </div>
              </div>
              <div class="progress mb-2" style="height:6px">
                <div class="progress-bar" id="cpu-bar" style="width:0%"></div>
              </div>
              <div class="stat-chart-wrap"><canvas id="cpu-chart"></canvas></div>
            </div>
          </div>
        </div>

        <!-- RAM -->
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <i class="ti ti-server text-green me-2 fs-3"></i>
                <div>
                  <div class="subheader">Bellek</div>
                  <div class="h2 mb-0"><span id="ram-used">—</span> <small class="text-muted fs-6">/ <span id="ram-total">—</span></small></div>
                </div>
              </div>
              <div class="progress mb-1" style="height:6px">
                <div class="progress-bar" id="ram-bar" style="width:0%"></div>
              </div>
              <small class="text-muted">Swap: <span id="swap-used">—</span> / <span id="swap-total">—</span></small>
              <div class="progress mt-1 mb-2" style="height:4px">
                <div class="progress-bar bg-warning" id="swap-bar" style="width:0%"></div>
              </div>
              <div class="stat-chart-wrap"><canvas id="ram-chart"></canvas></div>
            </div>
          </div>
        </div>

        <!-- Ağ -->
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <i class="ti ti-network text-orange me-2 fs-3"></i>
                <div class="subheader">Ağ Trafiği</div>
              </div>
              <div class="row g-1 mb-2">
                <div class="col-6">
                  <small class="text-muted d-block">↓ RX</small>
                  <div class="stat-chart-wrap" style="height:48px"><canvas id="rx-chart"></canvas></div>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block">↑ TX</small>
                  <div class="stat-chart-wrap" style="height:48px"><canvas id="tx-chart"></canvas></div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-sm table-borderless mb-0" style="font-size:.75rem">
                  <thead><tr><th>Arayüz</th><th>RX/s</th><th>TX/s</th><th>Toplam</th></tr></thead>
                  <tbody id="network-info"><tr><td colspan="4" class="text-muted">Yükleniyor…</td></tr></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Disk -->
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <i class="ti ti-database text-red me-2 fs-3"></i>
                <div class="subheader">Disk Kullanımı</div>
              </div>
              <div id="disk-info"><p class="text-muted small">Yükleniyor…</p></div>
            </div>
          </div>
        </div>

      </div>

      <!-- ── Servis Durumları ── -->
      <div class="card mb-3">
        <div class="card-header">
          <h3 class="card-title"><i class="ti ti-activity me-2"></i>Servis Durumları</h3>
        </div>
        <div class="card-body">
          <div class="row" id="service-status-grid">
            <p class="text-muted small">Yükleniyor…</p>
          </div>
        </div>
      </div>

      <!-- ── Panel İstatistikleri + Son Aktivite ── -->
      <div class="row row-deck row-cards mb-3">

        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="subheader">Toplam Müşteri</div>
              <div class="h1 mb-2">{{ $stats['clients'] ?? 0 }}</div>
              <a href="{{ URL::base('clients/main') }}" class="btn btn-sm btn-outline-primary w-100">Görüntüle</a>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="subheader">Toplam Site</div>
              <div class="h1 mb-2">{{ $stats['sites'] ?? 0 }}</div>
              <a href="{{ URL::base('sites/main') }}" class="btn btn-sm btn-outline-primary w-100">Görüntüle</a>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="subheader">Toplam Domain</div>
              <div class="h1 mb-2">{{ $stats['domains'] ?? 0 }}</div>
              <a href="{{ URL::base('domains/main') }}" class="btn btn-sm btn-outline-primary w-100">Görüntüle</a>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="subheader">Aktif SSL</div>
              <div class="h1 mb-2">{{ $stats['ssl'] ?? 0 }}</div>
              <a href="{{ URL::base('ssl/main') }}" class="btn btn-sm btn-outline-primary w-100">Görüntüle</a>
            </div>
          </div>
        </div>

      </div>

      <!-- ── Son Müşteriler + Siteler ── -->
      <div class="row row-deck row-cards">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="ti ti-users me-2"></i>Son Müşteriler</h3>
              <div class="card-options"><a href="{{ URL::base('clients/main') }}" class="btn btn-sm btn-primary">Tümü</a></div>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table">
                <thead><tr><th>Ad Soyad</th><th>E-posta</th><th>Durum</th></tr></thead>
                <tbody>
                  @forelse($recentClients as $client)
                    <tr>
                      <td>{{ $client->first_name }} {{ $client->last_name }}</td>
                      <td class="text-muted">{{ $client->email }}</td>
                      <td>
                        @if($client->status === 'active')
                        <span class="badge bg-success">Aktif</span>
                        @elseif($client->status === 'suspended')
                        <span class="badge bg-warning">Askıda</span>
                        @else
                        <span class="badge bg-danger">Sonlandı</span> @endif
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Henüz müşteri yok</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="ti ti-world me-2"></i>Son Siteler</h3>
              <div class="card-options"><a href="{{ URL::base('sites/main') }}" class="btn btn-sm btn-primary">Tümü</a></div>
            </div>
            <div class="table-responsive">
              <table class="table table-vcenter card-table">
                <thead><tr><th>Domain</th><th>PHP</th><th>Durum</th></tr></thead>
                <tbody>
                  @forelse($recentSites as $site)
                    <tr>
                      <td>{{ $site->domain }}</td>
                      <td class="text-muted">{{ $site->php_version }}</td>
                      <td>
                        @if($site->status === 'active')
                        <span class="badge bg-success">Aktif</span>
                        @elseif($site->status === 'suspended')
                        <span class="badge bg-warning">Askıda</span>
                        @else
                        <span class="badge bg-danger">Silindi</span> @endif
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Henüz site yok</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Chart.js + canlı stats -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="{{ URL::base('assets/js/stats.js') }}"></script>
