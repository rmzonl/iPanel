<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Giriş Yap - iPanel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
    <style>
        :root { --tblr-font-sans-serif: 'Inter', sans-serif; }
        .auth-bg { background: linear-gradient(135deg, #1e3a5f 0%, #0f2340 100%); min-height: 100vh; }
    </style>
</head>
<body class="antialiased">
<div class="auth-bg d-flex align-items-center">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold display-6">i<span class="text-azure">Panel</span></h1>
            <p class="text-white-50">Sunucu Yönetim Paneli</p>
        </div>

        <div class="card card-md">
            <div class="card-body">
                <h2 class="h3 text-center mb-4">Hesabınıza giriş yapın</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><point x="12" y="16"/></svg>
                            </div>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= URL::base('auth/login') ?>" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Adı veya E-posta</label>
                        <input type="text"
                               name="username"
                               class="form-control"
                               placeholder="admin"
                               autocomplete="off"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">
                            Şifre
                        </label>
                        <div class="input-group input-group-flat">
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="••••••••"
                                   autocomplete="current-password"
                                   required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="remember"/>
                            <span class="form-check-label">Beni hatırla</span>
                        </label>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/><path d="M20 12h-13l3 -3m0 6l-3 -3"/></svg>
                            Giriş Yap
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center text-white-50 mt-3">
            <small>iPanel &copy; <?= date('Y') ?> - Sunucu Yönetim Sistemi</small>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
</body>
</html>
