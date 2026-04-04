<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('domains/main') ?>">Domainler</a></li>
                    <li class="breadcrumb-item active">Yeni Domain</li>
                </ol>
            </nav>
            <h2 class="page-title">Yeni Domain Ekle</h2>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= URL::base('domains/store') ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Site</label>
                    <select name="site_id" class="form-select" required id="siteSelect">
                        <option value="">Site seçin...</option>
                        <?php if (!empty($sites) && $sites->result()): ?>
                            <?php foreach ($sites->result() as $site): ?>
                            <option value="<?= $site->id ?>" data-client="<?= $site->client_id ?>">
                                <?= htmlspecialchars($site->domain) ?>
                            </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Müşteri</label>
                    <select name="client_id" class="form-select" required>
                        <option value="">Müşteri seçin...</option>
                        <?php if (!empty($clients) && $clients->result()): ?>
                            <?php foreach ($clients->result() as $client): ?>
                            <option value="<?= $client->id ?>">
                                <?= htmlspecialchars($client->first_name . ' ' . $client->last_name) ?>
                            </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Domain Adı</label>
                    <input type="text" name="name" class="form-control" placeholder="subdomain.ornek.com" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tür</label>
                    <select name="type" class="form-select">
                        <option value="addon">Ek Domain</option>
                        <option value="subdomain">Alt Domain</option>
                        <option value="alias">Alias</option>
                        <option value="main">Ana Domain</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="active">Aktif</option>
                        <option value="inactive">Pasif</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Yönlendirme URL (opsiyonel)</label>
                    <input type="text" name="redirect_to" class="form-control" placeholder="https://hedef.com">
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= URL::base('domains/main') ?>" class="btn btn-outline-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">Domain Ekle</button>
            </div>
        </form>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
