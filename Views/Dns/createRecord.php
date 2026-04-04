<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('dns/main') ?>">DNS Yönetimi</a></li>
                    <li class="breadcrumb-item"><a href="<?= URL::base('dns/records/' . $zone->id) ?>">Kayıtlar</a></li>
                    <li class="breadcrumb-item active">Kayıt Ekle</li>
                </ol>
            </nav>
            <h2 class="page-title">DNS Kaydı Ekle: <?= htmlspecialchars($zone->domain_name ?? '') ?></h2>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= URL::base('dns/storeRecord') ?>">
            <input type="hidden" name="zone_id" value="<?= $zoneId ?>">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label required">Kayıt Türü</label>
                    <select name="type" class="form-select" required>
                        <option value="A">A</option>
                        <option value="AAAA">AAAA</option>
                        <option value="CNAME">CNAME</option>
                        <option value="MX">MX</option>
                        <option value="TXT">TXT</option>
                        <option value="NS">NS</option>
                        <option value="SRV">SRV</option>
                        <option value="CAA">CAA</option>
                        <option value="PTR">PTR</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label required">İsim</label>
                    <input type="text" name="name" class="form-control" placeholder="@ veya subdomain" required>
                    <small class="text-secondary">@ = kök domain</small>
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label required">Değer</label>
                    <input type="text" name="value" class="form-control" placeholder="IP adresi veya hedef" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Öncelik</label>
                    <input type="number" name="priority" class="form-control" placeholder="0" value="0" min="0">
                    <small class="text-secondary">MX ve SRV için gerekli</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">TTL (saniye)</label>
                    <input type="number" name="ttl" class="form-control" value="3600" min="60">
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= URL::base('dns/records/' . $zoneId) ?>" class="btn btn-outline-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">Kaydı Ekle</button>
            </div>
        </form>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
