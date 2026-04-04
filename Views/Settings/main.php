<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Sistem Ayarları</h2>
        </div>
    </div>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible" role="alert">
    <?= htmlspecialchars($success) ?>
    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible" role="alert">
    <?= htmlspecialchars($error) ?>
    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
</div>
<?php endif; ?>

<?php
// Build settings key => value map
$settingsMap = [];
if (!empty($serverSettings) && $serverSettings->result()) {
    foreach ($serverSettings->result() as $s) {
        $settingsMap[$s->setting_key] = $s->setting_value;
    }
}
function sv($map, $key, $default = '') {
    return htmlspecialchars($map[$key] ?? $default);
}
?>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
            <li class="nav-item">
                <a href="#tab-server" class="nav-link active" data-bs-toggle="tab">Sunucu Ayarları</a>
            </li>
            <li class="nav-item">
                <a href="#tab-php" class="nav-link" data-bs-toggle="tab">PHP / Hosting</a>
            </li>
            <li class="nav-item">
                <a href="#tab-backup" class="nav-link" data-bs-toggle="tab">Yedekleme & SSL</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">

            <!-- Server Settings Tab -->
            <div class="tab-pane active show" id="tab-server">
                <form method="POST" action="<?= URL::base('settings/save') ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Panel Adı</label>
                            <input type="text" name="panel_name" class="form-control" value="<?= sv($settingsMap, 'panel_name', 'iPanel') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sunucu IP Adresi</label>
                            <input type="text" name="server_ip" class="form-control" value="<?= sv($settingsMap, 'server_ip', '127.0.0.1') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Web Root Dizini</label>
                            <input type="text" name="webroot_base" class="form-control" value="<?= sv($settingsMap, 'webroot_base', '/var/www') ?>">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>

            <!-- PHP/Hosting Tab -->
            <div class="tab-pane" id="tab-php">
                <form method="POST" action="<?= URL::base('settings/save') ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mevcut PHP Versiyonları</label>
                            <input type="text" name="php_versions" class="form-control" value="<?= sv($settingsMap, 'php_versions', '7.4,8.0,8.1,8.2,8.3') ?>">
                            <small class="text-secondary">Virgülle ayırın (örn: 7.4,8.1,8.2)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Varsayılan PHP Versiyonu</label>
                            <input type="text" name="default_php_version" class="form-control" value="<?= sv($settingsMap, 'default_php_version', '8.2') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Müşteri Başına Max Site</label>
                            <input type="number" name="max_sites_per_client" class="form-control" value="<?= sv($settingsMap, 'max_sites_per_client', '0') ?>" min="0">
                            <small class="text-secondary">0 = Sınırsız</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Müşteri Başına Max Disk (MB)</label>
                            <input type="number" name="max_disk_per_client" class="form-control" value="<?= sv($settingsMap, 'max_disk_per_client', '0') ?>" min="0">
                            <small class="text-secondary">0 = Sınırsız</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Müşteri Başına Max Bant (MB)</label>
                            <input type="number" name="max_bandwidth_per_client" class="form-control" value="<?= sv($settingsMap, 'max_bandwidth_per_client', '0') ?>" min="0">
                            <small class="text-secondary">0 = Sınırsız</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>

            <!-- Backup & SSL Tab -->
            <div class="tab-pane" id="tab-backup">
                <form method="POST" action="<?= URL::base('settings/save') ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SSL E-posta Adresi</label>
                            <input type="email" name="ssl_email" class="form-control" value="<?= sv($settingsMap, 'ssl_email', 'admin@ipanel.local') ?>">
                            <small class="text-secondary">Let's Encrypt için gerekli</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Yedekleme Dizini</label>
                            <input type="text" name="backup_path" class="form-control" value="<?= sv($settingsMap, 'backup_path', '/var/backups/ipanel') ?>">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
