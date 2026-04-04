<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('ipaddresses/main') ?>">IP Adresleri</a></li>
                    <li class="breadcrumb-item active">Yeni IP</li>
                </ol>
            </nav>
            <h2 class="page-title">Yeni IP Adresi Ekle</h2>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= URL::base('ipaddresses/store') ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">IP Adresi</label>
                    <input type="text" name="ip" class="form-control" placeholder="192.168.1.1" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ağ Maskesi</label>
                    <input type="text" name="netmask" class="form-control" placeholder="255.255.255.0">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ağ Geçidi</label>
                    <input type="text" name="gateway" class="form-control" placeholder="192.168.1.1">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tür</label>
                    <select name="type" class="form-select">
                        <option value="shared">Paylaşımlı</option>
                        <option value="dedicated">Özel</option>
                        <option value="server">Sunucu</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Müşteri (opsiyonel)</label>
                    <select name="client_id" class="form-select">
                        <option value="">Atanmamış</option>
                        <?php if (!empty($clients) && $clients->result()): ?>
                            <?php foreach ($clients->result() as $client): ?>
                            <option value="<?= $client->id ?>"><?= htmlspecialchars($client->first_name . ' ' . $client->last_name) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="active">Aktif</option>
                        <option value="inactive">Pasif</option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Notlar</label>
                    <input type="text" name="notes" class="form-control" placeholder="Opsiyonel not">
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= URL::base('ipaddresses/main') ?>" class="btn btn-outline-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">IP Adresi Ekle</button>
            </div>
        </form>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
