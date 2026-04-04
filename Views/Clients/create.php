<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('clients/main') ?>">Müşteriler</a></li>
                    <li class="breadcrumb-item active">Yeni Müşteri</li>
                </ol>
            </nav>
            <h2 class="page-title">Yeni Müşteri Ekle</h2>
        </div>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible" role="alert">
    <?= htmlspecialchars($error) ?>
    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Müşteri Bilgileri</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= URL::base('clients/store') ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Ad</label>
                    <input type="text" name="first_name" class="form-control" placeholder="Ad" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Soyad</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Soyad" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Firma Adı</label>
                    <input type="text" name="company_name" class="form-control" placeholder="Firma adı (opsiyonel)" value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">E-posta</label>
                    <input type="email" name="email" class="form-control" placeholder="ornek@domain.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Telefon</label>
                    <input type="text" name="phone" class="form-control" placeholder="+90 555 000 0000" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Şehir</label>
                    <input type="text" name="city" class="form-control" placeholder="İstanbul" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ülke</label>
                    <input type="text" name="country" class="form-control" placeholder="TR" value="<?= htmlspecialchars($_POST['country'] ?? 'TR') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (($_POST['status'] ?? '') === 'active') ? 'selected' : '' ?>>Aktif</option>
                        <option value="suspended" <?= (($_POST['status'] ?? '') === 'suspended') ? 'selected' : '' ?>>Askıya Alındı</option>
                        <option value="terminated" <?= (($_POST['status'] ?? '') === 'terminated') ? 'selected' : '' ?>>Sonlandırıldı</option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Adres</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Açık adres"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Notlar</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="İç notlar (müşteriye gösterilmez)"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= URL::base('clients/main') ?>" class="btn btn-outline-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">Müşteriyi Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
