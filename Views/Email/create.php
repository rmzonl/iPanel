<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('email/main') ?>">E-posta Hesapları</a></li>
                    <li class="breadcrumb-item active">Yeni Hesap</li>
                </ol>
            </nav>
            <h2 class="page-title">Yeni E-posta Hesabı</h2>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= URL::base('email/store') ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Site</label>
                    <select name="site_id" class="form-select" required id="siteSelect" onchange="updateDomain(this)">
                        <option value="">Site seçin...</option>
                        <?php if (!empty($sites) && $sites->result()): ?>
                            <?php foreach ($sites->result() as $site): ?>
                            <option value="<?= $site->id ?>" data-domain="<?= htmlspecialchars($site->domain) ?>">
                                <?= htmlspecialchars($site->domain) ?>
                            </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Kullanıcı Adı</label>
                    <div class="input-group">
                        <input type="text" name="username" class="form-control" placeholder="info" required>
                        <span class="input-group-text">@</span>
                        <input type="text" name="domain" id="domainField" class="form-control" placeholder="domain.com" readonly>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Şifre</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kota (MB)</label>
                    <input type="number" name="quota" class="form-control" value="1024" min="0">
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
                <a href="<?= URL::base('email/main') ?>" class="btn btn-outline-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">E-posta Hesabı Oluştur</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateDomain(select) {
    var option = select.options[select.selectedIndex];
    var domain = option.getAttribute('data-domain') || '';
    document.getElementById('domainField').value = domain;
}
</script>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
