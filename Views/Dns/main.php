<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">DNS Yönetimi</h2>
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

<div class="card">
    <div class="card-header">
        <h3 class="card-title">DNS Zonları</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Domain</th>
                    <th>SOA E-posta</th>
                    <th>TTL</th>
                    <th>Durum</th>
                    <th>Oluşturma</th>
                    <th class="w-1">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($zones) && $zones->result()): ?>
                <?php foreach ($zones->result() as $zone): ?>
                <tr>
                    <td><?= $zone->id ?></td>
                    <td><strong><?= htmlspecialchars($zone->domain_name) ?></strong></td>
                    <td><?= htmlspecialchars($zone->soa_email ?? '-') ?></td>
                    <td><?= number_format($zone->ttl) ?></td>
                    <td>
                        <?php $badge = ($zone->status === 'active') ? 'success' : 'secondary'; ?>
                        <span class="badge bg-<?= $badge ?>-lt text-<?= $badge ?>"><?= ($zone->status === 'active') ? 'Aktif' : 'Pasif' ?></span>
                    </td>
                    <td><?= date('d.m.Y', strtotime($zone->created_at)) ?></td>
                    <td>
                        <div class="btn-group">
                            <a href="<?= URL::base('dns/records/' . $zone->id) ?>" class="btn btn-sm btn-outline-primary">Kayıtlar</a>
                            <a href="<?= URL::base('dns/deleteZone/' . $zone->id) ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Bu DNS zonunu silmek istediğinize emin misiniz?')">Sil</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-secondary py-5">
                        <p>Henüz DNS zonu eklenmemiş.</p>
                        <p class="text-secondary small">DNS zonları, domain eklendiğinde otomatik oluşturulur veya buradan yönetilir.</p>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
