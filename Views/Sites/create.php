<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('sites/main') ?>">Siteler</a></li>
                    <li class="breadcrumb-item active">Yeni Site</li>
                </ol>
            </nav>
            <h2 class="page-title">Yeni Site Ekle</h2>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= URL::base('sites/store') ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Müşteri</label>
                    <select name="client_id" class="form-select" required>
                        <option value="">Müşteri seçin...</option>
                        <?php if (!empty($clients) && $clients->result()): ?>
                            <?php foreach ($clients->result() as $client): ?>
                            <option value="<?= $client->id ?>"><?= htmlspecialchars($client->first_name . ' ' . $client->last_name . ($client->company_name ? ' (' . $client->company_name . ')' : '')) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Domain</label>
                    <input type="text" name="domain" class="form-control" placeholder="ornek.com" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">IP Adresi</label>
                    <select name="ip_id" class="form-select">
                        <option value="">IP seçin (opsiyonel)</option>
                        <?php if (!empty($ips) && $ips->result()): ?>
                            <?php foreach ($ips->result() as $ip): ?>
                            <option value="<?= $ip->id ?>"><?= htmlspecialchars($ip->ip) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">PHP Versiyonu</label>
                    <select name="php_version" class="form-select">
                        <option value="7.4">PHP 7.4</option>
                        <option value="8.0">PHP 8.0</option>
                        <option value="8.1">PHP 8.1</option>
                        <option value="8.2" selected>PHP 8.2</option>
                        <option value="8.3">PHP 8.3</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Document Root</label>
                    <input type="text" name="document_root" class="form-control" placeholder="/var/www/ornek.com/public_html">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Disk Kotası (MB)</label>
                    <input type="number" name="disk_quota" class="form-control" placeholder="0 = Sınırsız" value="0" min="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Bant Genişliği Kotası (MB)</label>
                    <input type="number" name="bandwidth_quota" class="form-control" placeholder="0 = Sınırsız" value="0" min="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="active">Aktif</option>
                        <option value="suspended">Askıya Alındı</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= URL::base('sites/main') ?>" class="btn btn-outline-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">Siteyi Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
