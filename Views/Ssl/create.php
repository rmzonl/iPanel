<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('ssl/main') ?>">SSL Sertifikaları</a></li>
                    <li class="breadcrumb-item active">Yeni Sertifika</li>
                </ol>
            </nav>
            <h2 class="page-title">SSL Sertifikası Ekle</h2>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= URL::base('ssl/store') ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Domain</label>
                    <select name="domain_id" class="form-select" required>
                        <option value="">Domain seçin...</option>
                        <?php if (!empty($domains) && $domains->result()): ?>
                            <?php foreach ($domains->result() as $domain): ?>
                            <option value="<?= $domain->id ?>"><?= htmlspecialchars($domain->name) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sertifika Türü</label>
                    <select name="type" class="form-select">
                        <option value="letsencrypt">Let's Encrypt (Ücretsiz)</option>
                        <option value="paid">Ücretli SSL</option>
                        <option value="self_signed">Kendinden İmzalı</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Veriliş Tarihi</label>
                    <input type="date" name="issued_at" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Son Kullanma Tarihi</label>
                    <input type="date" name="expires_at" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="pending">Bekliyor</option>
                        <option value="active">Aktif</option>
                        <option value="expired">Süresi Dolmuş</option>
                        <option value="failed">Başarısız</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3 d-flex align-items-end">
                    <label class="form-check">
                        <input type="checkbox" name="auto_renew" class="form-check-input" value="1" checked>
                        <span class="form-check-label">Otomatik Yenile</span>
                    </label>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Sertifika Dosyası (cert_file)</label>
                    <textarea name="cert_file" class="form-control" rows="4" placeholder="-----BEGIN CERTIFICATE-----"></textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Anahtar Dosyası (key_file)</label>
                    <textarea name="key_file" class="form-control" rows="4" placeholder="-----BEGIN PRIVATE KEY-----"></textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Zincir Dosyası (chain_file)</label>
                    <textarea name="chain_file" class="form-control" rows="3" placeholder="-----BEGIN CERTIFICATE-----"></textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= URL::base('ssl/main') ?>" class="btn btn-outline-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">Sertifika Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
