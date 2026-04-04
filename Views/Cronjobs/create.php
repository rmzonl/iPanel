<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('cronjobs/main') ?>">Cron İşleri</a></li>
                    <li class="breadcrumb-item active">Yeni Cron İşi</li>
                </ol>
            </nav>
            <h2 class="page-title">Yeni Cron İşi Oluştur</h2>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= URL::base('cronjobs/store') ?>">
                    <div class="mb-3">
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
                    <div class="mb-3">
                        <label class="form-label required">Başlık</label>
                        <input type="text" name="title" class="form-control" placeholder="Cron işi açıklaması" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Komut</label>
                        <input type="text" name="command" class="form-control" placeholder="/usr/bin/php /var/www/site/artisan schedule:run" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Zamanlama (Cron ifadesi)</label>
                        <input type="text" name="schedule" class="form-control" placeholder="* * * * *" required>
                        <small class="text-secondary">Dakika Saat GünAy Ay HaftaGünü</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="active">Aktif</option>
                            <option value="inactive">Pasif</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= URL::base('cronjobs/main') ?>" class="btn btn-outline-secondary">İptal</a>
                        <button type="submit" class="btn btn-primary">Cron İşi Oluştur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Yaygın Zamanlama Örnekleri</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tbody>
                        <tr><td><code>* * * * *</code></td><td>Her dakika</td></tr>
                        <tr><td><code>0 * * * *</code></td><td>Her saat</td></tr>
                        <tr><td><code>0 0 * * *</code></td><td>Her gece yarısı</td></tr>
                        <tr><td><code>0 0 * * 0</code></td><td>Her Pazar</td></tr>
                        <tr><td><code>0 0 1 * *</code></td><td>Her ayın 1'i</td></tr>
                        <tr><td><code>*/5 * * * *</code></td><td>Her 5 dakika</td></tr>
                        <tr><td><code>0 9,18 * * *</code></td><td>09:00 ve 18:00</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
