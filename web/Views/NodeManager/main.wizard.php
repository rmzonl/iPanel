<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-brand-nodejs me-2"></i>Node.js Yönetimi</h2>
          <div class="text-muted mt-1">Node.js sürümleri, npm ve pm2 süreç yöneticisi.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">

      @if(!empty($success))
        <div class="alert alert-success alert-dismissible">{[ echo $success; ]}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif
      @if(!empty($error))
        <div class="alert alert-danger alert-dismissible"><pre class="mb-0">{{ $error }}</pre><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif

      <!-- Durum Kartları -->
      <div class="row mb-4">
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="subheader">Node.js</div>
              <div class="h2 mb-0">{{ $nodeInfo['node_version'] ?? 'Kurulu Değil' }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="subheader">npm</div>
              <div class="h2 mb-0">{{ $nodeInfo['npm_version'] ?? 'Yok' }}</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="subheader">nvm</div>
              <div class="h2 mb-0">
                @if($nodeInfo['nvm_installed'])
                  <span class="text-success">Kurulu</span>
                @else
                  <span class="text-muted">Yok</span>
                @endif
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="subheader">pm2</div>
              <div class="h2 mb-0">
                @if($nodeInfo['pm2_installed'])
                  <span class="text-success">Kurulu</span>
                @else
                  <span class="text-muted">Yok</span>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">

        <!-- Sol -->
        <div class="col-lg-6">

          <!-- nvm kurulum -->
          @if(!$nodeInfo['nvm_installed'])
            <div class="card mb-3">
              <div class="card-header"><h3 class="card-title">nvm Kur</h3></div>
              <div class="card-body">
                <p class="text-muted">nvm, birden fazla Node.js sürümünü kolayca yönetmenizi sağlar.</p>
                <form method="POST" action="{{ URL::base('nodemanager/installNvm') }}">
                  {[ echo $csrfField ?? ""; ]}
                  <button type="submit" class="btn btn-primary"
                          onclick="return confirm('nvm kurulacak. İnternet bağlantısı gereklidir.')">
                    <i class="ti ti-download me-1"></i>nvm Kur
                  </button>
                </form>
              </div>
            </div>
          @endif

          <!-- Node.js Sürüm Kur -->
          <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">Node.js Sürümü Kur</h3></div>
            <div class="card-body">
              <form method="POST" action="{{ URL::base('nodemanager/installVersion') }}">
                {[ echo $csrfField ?? ""; ]}
                <div class="input-group">
                  <select name="node_version" class="form-select">
                    <option value="lts">LTS (Önerilen)</option>
                    <option value="latest">Latest</option>
                    <option value="22">Node.js 22</option>
                    <option value="20">Node.js 20</option>
                    <option value="18">Node.js 18</option>
                    <option value="16">Node.js 16</option>
                  </select>
                  <button type="submit" class="btn btn-success"
                          onclick="return confirm('Node.js kurulumu başlayacak. İnternet bağlantısı gerekli.')">
                    <i class="ti ti-download me-1"></i>Kur
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- pm2 -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">pm2 Süreç Yöneticisi</h3>
              @if($nodeInfo['pm2_installed'])
                <span class="badge bg-success ms-2">Kurulu</span>
              @endif
            </div>
            <div class="card-body">
              <p class="text-muted">pm2, Node.js uygulamalarını arka planda ve sistem yeniden başlatıldığında otomatik olarak çalıştırır.</p>
              <div class="d-flex gap-2">
                @if(!$nodeInfo['pm2_installed'])
                  <form method="POST" action="{{ URL::base('nodemanager/pm2') }}">
                    {[ echo $csrfField ?? ""; ]}
                    <input type="hidden" name="action" value="install">
                    <button type="submit" class="btn btn-primary">
                      <i class="ti ti-download me-1"></i>pm2 Kur
                    </button>
                  </form>
                @else
                  <form method="POST" action="{{ URL::base('nodemanager/pm2') }}">
                    {[ echo $csrfField ?? ""; ]}
                    <input type="hidden" name="action" value="list">
                    <button type="submit" class="btn btn-secondary">
                      <i class="ti ti-list me-1"></i>Süreçleri Listele
                    </button>
                  </form>
                @endif
              </div>
            </div>
          </div>

        </div>

        <!-- Sağ: nvm sürüm listesi -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Kurulu Sürümler (nvm)</h3>
            </div>
            @if(!$nodeInfo['nvm_installed'])
              <div class="card-body text-muted">nvm kurulu değil.</div>
            @elseif(empty($nodeInfo['nvm_versions']))
              <div class="card-body text-muted">Henüz sürüm kurulmamış.</div>
            @else
              <div class="table-responsive">
                <table class="table table-vcenter mb-0">
                  <thead>
                    <tr>
                      <th>Sürüm</th>
                      <th>Durum</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($nodeInfo['nvm_versions'] as $v)
                      <tr>
                        <td><strong>{{ $v['version'] }}</strong></td>
                        <td>
                          @if($v['current'])
                            <span class="badge bg-blue">Aktif</span>
                          @endif
                          @if($v['default'])
                            <span class="badge bg-green">Varsayılan</span>
                          @endif
                        </td>
                        <td>
                          @if(!$v['default'])
                            <form method="POST" action="{{ URL::base('nodemanager/setDefault') }}" class="d-inline">
                              {[ echo $csrfField ?? ""; ]}
                              <input type="hidden" name="node_version" value="{{ $v['version'] }}">
                              <button type="submit" class="btn btn-sm btn-secondary">Varsayılan Yap</button>
                            </form>
                          @endif
                          @if(!$v['current'])
                            {[ $nodeVer = $v['version']; ]}
                            <form method="POST" action="{{ URL::base('nodemanager/remove') }}" class="d-inline">
                              {[ echo $csrfField ?? ""; ]}
                              <input type="hidden" name="node_version" value="{{ $nodeVer }}">
                              <button type="submit" class="btn btn-sm btn-danger"
                                      onclick="return confirm('{{ $nodeVer }} kaldırılsın mı?')">
                                <i class="ti ti-trash"></i>
                              </button>
                            </form>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
