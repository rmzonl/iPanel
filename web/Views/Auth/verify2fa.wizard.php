<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>2FA Doğrulama — iPanel</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
</head>
<body class="d-flex flex-column">
<div class="page page-center">
  <div class="container-tight py-4">
    <div class="text-center mb-4">
      <h1 class="navbar-brand navbar-brand-autodark d-inline-block">
        <i class="ti ti-shield-check text-primary" style="font-size:2.5rem;"></i>
      </h1>
      <h2 class="h3">İki Faktörlü Doğrulama</h2>
      <p class="text-muted">Merhaba <strong>{{ $username }}</strong>, Google Authenticator uygulamanızdaki 6 haneli kodu girin.</p>
    </div>

    @if(!empty($error))
      <div class="alert alert-danger">{{ $error }}</div>
    @endif

    <div class="card card-md">
      <div class="card-body">
        <form method="POST" action="{{ URL::base('auth/verify2fa') }}" autocomplete="off">
          {[ echo $csrfField ?? ""; ]}

          <div class="mb-3">
            <label class="form-label">Doğrulama Kodu</label>
            <input type="text" name="code" class="form-control form-control-lg text-center"
                   placeholder="000000" maxlength="9" autofocus
                   inputmode="numeric" pattern="[0-9 -]*"
                   style="letter-spacing:.4em; font-size:1.8rem;">
            <div class="form-text">Yedek kod da kullanabilirsiniz (XXXX-XXXX formatında).</div>
          </div>

          <div class="form-footer">
            <button type="submit" class="btn btn-primary w-100">
              <i class="ti ti-check me-2"></i>Doğrula
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="text-center mt-3">
      <a href="{{ URL::base('auth/login') }}" class="text-muted">
        <i class="ti ti-arrow-left me-1"></i>Giriş sayfasına dön
      </a>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
</body>
</html>
