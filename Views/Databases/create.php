<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('databases/main') ?>">Veritabanları</a></li>
                    <li class="breadcrumb-item active">Yeni Veritabanı</li>
                </ol>
            </nav>
            <h2 class="page-title">Yeni Veritabanı Oluştur</h2>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= URL::base('databases/store') ?>">
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
                    <label class="form-label required">Veritabanı Adı</label>
                    <input type="text" name="db_name" class="form-control" placeholder="mydb_production" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Veritabanı Kullanıcısı</label>
                    <input type="text" name="db_user" class="form-control" placeholder="mydb_user" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Şifre</label>
                    <input type="password" name="db_password" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Karakter Seti</label>
                    <select name="charset" class="form-select">
                        <option value="utf8mb4">utf8mb4 (Önerilen)</option>
                        <option value="utf8">utf8</option>
                        <option value="latin1">latin1</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="active">Aktif</option>
                        <option value="suspended">Askıya Alındı</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= URL::base('databases/main') ?>" class="btn btn-outline-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">Veritabanı Oluştur</button>
            </div>
        </form>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
