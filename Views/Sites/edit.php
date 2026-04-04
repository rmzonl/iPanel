<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('sites/main') ?>">Siteler</a></li>
                    <li class="breadcrumb-item active">Düzenle</li>
                </ol>
            </nav>
            <h2 class="page-title">Site Düzenle: <?= htmlspecialchars($site->domain) ?></h2>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= URL::base('sites/update/' . $site->id) ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Müşteri</label>
                    <select name="client_id" class="form-select" required>
                        <?php if (!empty($clients) && $clients->result()): ?>
                            <?php foreach ($clients->result() as $client): ?>
                            <option value="<?= $client->id ?>" <?= ($site->client_id == $client->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client->first_name . ' ' . $client->last_name) ?>
                            </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Domain</label>
                    <input type="text" name="domain" class="form-control" required value="<?= htmlspecialchars($site->domain) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">IP Adresi</label>
                    <select name="ip_id" class="form-select">
                        <option value="">IP seçin</option>
                        <?php if (!empty($ips) && $ips->result()): ?>
                            <?php foreach ($ips->result() as $ip): ?>
                            <option value="<?= $ip->id ?>" <?= ($site->ip_id == $ip->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ip->ip) ?>
                            </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">PHP Versiyonu</label>
                    <select name="php_version" class="form-select">
                        <?php foreach (['7.4', '8.0', '8.1', '8.2', '8.3'] as $ver): ?>
                        <option value="<?= $ver ?>" <?= ($site->php_version === $ver) ? 'selected' : '' ?>>PHP <?= $ver ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Document Root</label>
                    <input type="text" name="document_root" class="form-control" value="<?= htmlspecialchars($site->document_root ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Disk Kotası (MB)</label>
                    <input type="number" name="disk_quota" class="form-control" value="<?= $site->disk_quota ?>" min="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Bant Genişliği (MB)</label>
                    <input type="number" name="bandwidth_quota" class="form-control" value="<?= $site->bandwidth_quota ?>" min="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= ($site->status === 'active') ? 'selected' : '' ?>>Aktif</option>
                        <option value="suspended" <?= ($site->status === 'suspended') ? 'selected' : '' ?>>Askıya Alındı</option>
                        <option value="deleted" <?= ($site->status === 'deleted') ? 'selected' : '' ?>>Silindi</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= URL::base('sites/main') ?>" class="btn btn-outline-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
