<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
@if(!empty($csrfToken))
<meta name="csrf-token" content="{{ $csrfToken }}">
@endif
<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="{{ URL::base('iApp/Themes/Tabler/css/tabler.min.css') }}"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css"/>
<style>
  body { font-family: 'Inter', sans-serif; }
  .navbar-brand-image { height: 32px; }
  .sidebar-active { background: rgba(var(--tblr-primary-rgb), .1); color: var(--tblr-primary) !important; border-radius: 4px; }
  .sidebar-active .nav-link-icon { color: var(--tblr-primary) !important; }
  .alert-flash { position: fixed; top: 1rem; right: 1rem; z-index: 9999; min-width: 300px; }
  /* Stat kartları */
  .stat-chart-wrap { position: relative; height: 60px; }
  .stat-badge-live { display:inline-flex;align-items:center;gap:4px;font-size:.7rem; }
  .stat-badge-live::before { content:''; width:7px;height:7px;border-radius:50%;background:#2fb344;animation:pulse 1.5s infinite; }
  @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }
</style>
