<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('ftp/main') ?>">FTP Hesapları</a></li>
                    <li class="breadcrumb-item active">Yeni Hesap</li>
                </ol>
            </nav>
            <h2 class="page-title">Yeni FTP Hesabı</h2>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= URL::base('ftp/store') ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Site</label>
                    <select name="site_id" class="form-select" required>
                        <option value="">Site seçin...</option>
                        <?php if (!empty($sites) && $sites->result()): ?>
                            <?php foreach ($sites->result() as $site): ?>
                            <option value="<?= $site->id ?>"><?= htmlspecialchars($site->domain) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Kullanıcı Adı</label>
                    <input type="text" name="username" class="form-control" placeholder="ftpuser" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Şifre</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ana Dizin</label>
                    <input type="text" name="home_dir" class="form-control" placeholder="/var/www/ornek.com">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kota (MB)</label>
                    <input type="number" name="quota" class="form-control" value="0" min="0">
                    <small class="text-secondary">0 = Sınırsız</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="active">Aktif</option>
                        <option value="suspended">Askıya Alındı</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= URL::base('ftp/main') ?>" class="btn btn-outline-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">FTP Hesabı Oluştur</button>
            </div>
        </form>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
