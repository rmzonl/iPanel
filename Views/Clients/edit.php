<?php include(VIEW_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('clients/main') ?>">Müşteriler</a></li>
                    <li class="breadcrumb-item active">Düzenle</li>
                </ol>
            </nav>
            <h2 class="page-title">Müşteri Düzenle: <?= htmlspecialchars($client->first_name . ' ' . $client->last_name) ?></h2>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Müşteri Bilgileri</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= URL::base('clients/update/' . $client->id) ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Ad</label>
                    <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($client->first_name) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Soyad</label>
                    <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($client->last_name) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Firma Adı</label>
                    <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($client->company_name ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required">E-posta</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($client->email) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Telefon</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($client->phone ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Şehir</label>
                    <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($client->city ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ülke</label>
                    <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($client->country ?? 'TR') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= ($client->status === 'active') ? 'selected' : '' ?>>Aktif</option>
                        <option value="suspended" <?= ($client->status === 'suspended') ? 'selected' : '' ?>>Askıya Alındı</option>
                        <option value="terminated" <?= ($client->status === 'terminated') ? 'selected' : '' ?>>Sonlandırıldı</option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Adres</label>
                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($client->address ?? '') ?></textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Notlar</label>
                    <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($client->notes ?? '') ?></textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= URL::base('clients/main') ?>" class="btn btn-outline-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php include(VIEW_DIR . 'layouts/footer.php'); ?>
