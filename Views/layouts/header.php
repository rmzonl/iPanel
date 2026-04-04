<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title><?= htmlspecialchars($pageTitle ?? 'iPanel') ?> - iPanel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
    <style>
        :root {
            --tblr-font-sans-serif: 'Inter', sans-serif;
        }
        .navbar-brand-image { height: 2rem; }
        .nav-link-icon { opacity: .7; }
        .sidebar-nav .active .nav-link-icon { opacity: 1; }
    </style>
</head>
<body class="antialiased">
<div class="wrapper">
    <!-- Sidebar -->
    <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand navbar-brand-autodark">
                <a href="<?= URL::base('dashboard/main') ?>">
                    <span class="text-white fw-bold fs-3">i<span class="text-azure">Panel</span></span>
                </a>
            </h1>
            <div class="collapse navbar-collapse" id="sidebar-menu">
                <ul class="navbar-nav pt-lg-3">

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'dashboard') ? 'active' : '' ?>" href="<?= URL::base('dashboard/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
                            </span>
                            <span class="nav-link-title">Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'clients') ? 'active' : '' ?>" href="<?= URL::base('clients/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/></svg>
                            </span>
                            <span class="nav-link-title">Müşteriler</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'sites') ? 'active' : '' ?>" href="<?= URL::base('sites/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9"/><path d="M3.6 9h16.8"/><path d="M3.6 15h16.8"/><path d="M11.5 3a17 17 0 0 0 0 18"/><path d="M12.5 3a17 17 0 0 1 0 18"/></svg>
                            </span>
                            <span class="nav-link-title">Siteler</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'domains') ? 'active' : '' ?>" href="<?= URL::base('domains/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 14a3.5 3.5 0 0 0 5 0l4 -4a3.5 3.5 0 0 0 -5 -5l-1.5 1.5"/><path d="M14 10a3.5 3.5 0 0 0 -5 0l-4 4a3.5 3.5 0 0 0 5 5l1.5 -1.5"/></svg>
                            </span>
                            <span class="nav-link-title">Domain Yönetimi</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'ssl') ? 'active' : '' ?>" href="<?= URL::base('ssl/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z"/><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0"/><path d="M8 11v-4a4 4 0 1 1 8 0v4"/></svg>
                            </span>
                            <span class="nav-link-title">SSL Sertifikaları</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'email') ? 'active' : '' ?>" href="<?= URL::base('email/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6l9 -6"/></svg>
                            </span>
                            <span class="nav-link-title">E-posta Yönetimi</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'dns') ? 'active' : '' ?>" href="<?= URL::base('dns/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="3" y="4" width="18" height="4" rx="1"/><rect x="3" y="10" width="18" height="4" rx="1"/><rect x="3" y="16" width="18" height="4" rx="1"/></svg>
                            </span>
                            <span class="nav-link-title">DNS Yönetimi</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'ftp') ? 'active' : '' ?>" href="<?= URL::base('ftp/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"/></svg>
                            </span>
                            <span class="nav-link-title">FTP Hesapları</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'databases') ? 'active' : '' ?>" href="<?= URL::base('databases/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6a8 3 0 0 0 16 0v-6"/><path d="M4 12v6a8 3 0 0 0 16 0v-6"/></svg>
                            </span>
                            <span class="nav-link-title">Veritabanları</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'backups') ? 'active' : '' ?>" href="<?= URL::base('backups/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z"/><path d="M3 10h18"/><path d="M3 10v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2 -2v-10"/></svg>
                            </span>
                            <span class="nav-link-title">Yedeklemeler</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'cronjobs') ? 'active' : '' ?>" href="<?= URL::base('cronjobs/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                            </span>
                            <span class="nav-link-title">Cron İşleri</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'ipaddresses') ? 'active' : '' ?>" href="<?= URL::base('ipaddresses/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 12h6"/><path d="M12 9v6"/><circle cx="12" cy="12" r="9"/></svg>
                            </span>
                            <span class="nav-link-title">IP Adresleri</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'firewall') ? 'active' : '' ?>" href="<?= URL::base('firewall/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3"/></svg>
                            </span>
                            <span class="nav-link-title">Güvenlik Duvarı</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= (strtolower(CURRENT_CONTROLLER) === 'settings') ? 'active' : '' ?>" href="<?= URL::base('settings/main') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                            </span>
                            <span class="nav-link-title">Ayarlar</span>
                        </a>
                    </li>

                </ul>
                <div class="mt-auto pb-3">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= URL::base('auth/logout') ?>">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/><path d="M7 12h14l-3 -3m0 6l3 -3"/></svg>
                                </span>
                                <span class="nav-link-title">Çıkış Yap</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </aside>

    <div class="page-wrapper">
        <!-- Top navbar -->
        <div class="navbar-expand-md">
            <div class="collapse navbar-collapse" id="navbar-menu">
                <div class="navbar navbar-light">
                    <div class="container-xl">
                        <div class="navbar-nav flex-row order-md-last">
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                                    <span class="avatar avatar-sm">
                                        <?php
                                        $user = Session::select('admin_user');
                                        $initial = strtoupper(substr($user['username'] ?? 'A', 0, 1));
                                        echo $initial;
                                        ?>
                                    </span>
                                    <div class="d-none d-xl-block ps-2">
                                        <div><?= htmlspecialchars($user['username'] ?? '') ?></div>
                                        <div class="mt-1 small text-secondary"><?= htmlspecialchars($user['role'] ?? '') ?></div>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <a href="<?= URL::base('auth/logout') ?>" class="dropdown-item">Çıkış Yap</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page content -->
        <div class="page-body">
            <div class="container-xl">
