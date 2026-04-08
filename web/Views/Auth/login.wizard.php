<div class="container container-tight py-4">
  <div class="text-center mb-4">
    <a href="#" class="navbar-brand navbar-brand-autodark justify-content-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-server-bolt me-2" width="40" height="40" viewBox="0 0 24 24" stroke-width="1.5" stroke="var(--tblr-primary)" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z"/>
        <path d="M3 14m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z"/>
        <path d="M7 8l0 .01"/><path d="M7 18l0 .01"/><path d="M13 7l-2 3h4l-2 3"/>
      </svg>
      <span class="fs-2 fw-bold">iPanel</span>
    </a>
  </div>

  <div class="card card-md">
    <div class="card-body">
      <h2 class="h2 text-center mb-4">Yönetici Girişi</h2>

      @if(!empty($error))
        <div class="alert alert-danger mb-3">
          <i class="ti ti-alert-circle me-2"></i>{{ $error }}
        </div>
      @endif

      <form method="POST" action="{{ URL::base('auth/login') }}" autocomplete="off" novalidate>
        {[ echo $csrfField ?? ''; ]}
        <div class="mb-3">
          <label class="form-label">Kullanıcı Adı veya E-posta</label>
          <input type="text" name="username" class="form-control" placeholder="admin" autocomplete="username" required/>
        </div>
        <div class="mb-2">
          <label class="form-label">
            Şifre
          </label>
          <div class="input-group input-group-flat">
            <input type="password" name="password" class="form-control" placeholder="••••••••" autocomplete="current-password" required id="passwordInput"/>
            <span class="input-group-text" style="cursor:pointer" onclick="var p=document.getElementById('passwordInput');p.type=p.type==='password'?'text':'password'">
              <i class="ti ti-eye"></i>
            </span>
          </div>
        </div>
        <div class="form-footer">
          <button type="submit" class="btn btn-primary w-100">
            <i class="ti ti-login me-2"></i>Giriş Yap
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="text-center text-muted mt-3">
    iPanel &mdash; Sunucu Yönetim Sistemi
  </div>
</div>
