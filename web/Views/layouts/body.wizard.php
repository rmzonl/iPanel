{[
    $currentController = strtolower(CURRENT_CFUNCTION ?? '');
    $currentMethod     = strtolower(CURRENT_CMETHOD ?? '');
    $currentRoute      = $currentController . '/' . $currentMethod;
    $navUser           = Session::select('admin_user') ?? [];
    $navUsername       = $navUser['username'] ?? 'Admin';
    $navRole           = $navUser['role'] ?? 'admin';
]}

<div class="wrapper">

  <!-- Sidebar -->
  <aside class="navbar navbar-vertical navbar-expand-lg navbar-dark">
    <div class="container-fluid">

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <h1 class="navbar-brand navbar-brand-autodark">
        <a href="{{ URL::base('dashboard/main') }}" class="text-white text-decoration-none d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-server-bolt" width="32" height="32" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z"/>
            <path d="M3 14m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z"/>
            <path d="M7 8l0 .01"/><path d="M7 18l0 .01"/><path d="M13 7l-2 3h4l-2 3"/>
          </svg>
          <span class="fs-4 fw-bold">iPanel</span>
        </a>
      </h1>

      <div class="collapse navbar-collapse" id="navbar-menu">
        <ul class="navbar-nav pt-lg-3">

          {[
            // Tüm roller için gösterilen menü öğeleri
            $navItems = [
              ['url'=>'dashboard/main', 'icon'=>'ti-dashboard', 'label'=>'Dashboard'],
              ['url'=>'clients/main',   'icon'=>'ti-users',     'label'=>'Müşteriler'],
              ['url'=>'sites/main',     'icon'=>'ti-world',     'label'=>'Siteler'],
              ['url'=>'domains/main',   'icon'=>'ti-link',      'label'=>'Domainler'],
              ['url'=>'ssl/main',       'icon'=>'ti-lock',      'label'=>'SSL Sertifikaları'],
              ['url'=>'email/main',     'icon'=>'ti-mail',      'label'=>'E-posta'],
              ['url'=>'dns/main',       'icon'=>'ti-server',    'label'=>'DNS Yönetimi'],
              ['url'=>'ftp/main',       'icon'=>'ti-folder',    'label'=>'FTP Hesapları'],
              ['url'=>'databases/main', 'icon'=>'ti-database',  'label'=>'Veritabanları'],
              ['url'=>'backups/main',   'icon'=>'ti-archive',   'label'=>'Yedeklemeler'],
              ['url'=>'cronjobs/main',  'icon'=>'ti-clock',     'label'=>'Cron İşleri'],
            ];

            // Admin-only menü öğeleri
            $adminItems = [
              ['url'=>'filemanager/main', 'icon'=>'ti-files',          'label'=>'Dosya Yöneticisi'],
              ['url'=>'ipaddresses/main', 'icon'=>'ti-network',        'label'=>'IP Adresleri'],
              ['url'=>'firewall/main',    'icon'=>'ti-shield',         'label'=>'Güvenlik Duvarı'],
              ['url'=>'phpmyadmin/main',  'icon'=>'ti-database-import','label'=>'PHPMyAdmin'],
              ['url'=>'phpmanager/main',  'icon'=>'ti-brand-php',      'label'=>'PHP Yönetimi'],
              ['url'=>'nodemanager/main', 'icon'=>'ti-brand-nodejs',   'label'=>'Node.js'],
              ['url'=>'jobs/main',        'icon'=>'ti-list-check',     'label'=>'İş Kuyruğu'],
              ['url'=>'settings/main',    'icon'=>'ti-settings',       'label'=>'Ayarlar'],
            ];

            // Tüm kullanıcılar için ek öğeler
            $commonExtraItems = [
              ['url'=>'apitokens/main',      'icon'=>'ti-api',         'label'=>'API Token\'lar'],
              ['url'=>'settings/twoFactor',  'icon'=>'ti-shield-lock', 'label'=>'2FA Güvenlik'],
            ];

            $isAdmin = ($navRole === 'admin');
            if ($isAdmin) {
                $navItems = array_merge($navItems, $adminItems);
            }
            $navItems = array_merge($navItems, $commonExtraItems);
          ]}

          @foreach($navItems as $item)
            {[ $isActive = (strpos($currentRoute, explode('/', $item['url'])[0]) === 0) ? 'sidebar-active' : ''; ]}
            <li class="nav-item">
              <a class="nav-link {{ $isActive }}" href="{{ URL::base($item['url']) }}">
                <span class="nav-link-icon d-md-none d-lg-block">
                  <i class="ti {{ $item['icon'] }}"></i>
                </span>
                <span class="nav-link-title">{{ $item['label'] }}</span>
              </a>
            </li>
          @endforeach

        </ul>

        <div class="mt-auto pb-3">
          <ul class="navbar-nav">
            <li class="nav-item dropdown">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <span class="nav-link-icon d-md-none d-lg-block">
                  <i class="ti ti-user-circle"></i>
                </span>
                <span class="nav-link-title">{{ $navUsername }}</span>
              </a>
              <div class="dropdown-menu dropdown-menu-end">
                <span class="dropdown-item-text text-muted small">{{ $navRole }}</span>
                <div class="dropdown-divider"></div>
                <a href="{{ URL::base('settings/main') }}" class="dropdown-item">
                  <i class="ti ti-settings me-2"></i> Ayarlar
                </a>
                <a href="{{ URL::base('auth/logout') }}" class="dropdown-item text-danger">
                  <i class="ti ti-logout me-2"></i> Çıkış Yap
                </a>
              </div>
            </li>
          </ul>
        </div>

      </div>
    </div>
  </aside>

  <!-- Page Content -->
  <div class="page-wrapper">

    <!-- Flash Messages -->
    {[ $flashSuccess = Session::select('flash_success'); $flashError = Session::select('flash_error'); ]}
    @if(!empty($flashSuccess))
      {[ Session::delete('flash_success'); ]}
      <div class="alert alert-success alert-flash alert-dismissible fade show" role="alert">
        <i class="ti ti-circle-check me-2"></i>{{ $flashSuccess }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    @if(!empty($flashError))
      {[ Session::delete('flash_error'); ]}
      <div class="alert alert-danger alert-flash alert-dismissible fade show" role="alert">
        <i class="ti ti-alert-circle me-2"></i>{{ $flashError }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    {{ $view }}

    <footer class="footer footer-transparent d-print-none">
      <div class="container-xl">
        <div class="row text-center align-items-center flex-row-reverse">
          <div class="col-lg-auto ms-lg-auto">
            <ul class="list-inline list-inline-dots mb-0">
              <li class="list-inline-item">
                <a href="#" class="link-secondary">Belgeler</a>
              </li>
              <li class="list-inline-item">
                <a href="#" class="link-secondary">Destek</a>
              </li>
            </ul>
          </div>
          <div class="col-12 col-lg-auto mt-3 mt-lg-0">
            <ul class="list-inline list-inline-dots mb-0">
              <li class="list-inline-item">
                &copy; {[ echo date('Y'); ]} <a href="#" class="link-secondary">iPanel</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </footer>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
<script src="{{ URL::base('iApp/Themes/Tabler/js/ipanel.js') }}"></script>
