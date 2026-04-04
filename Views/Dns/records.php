<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('dns/main') ?>">DNS Yönetimi</a></li>
                    <li class="breadcrumb-item active">Kayıtlar</li>
                </ol>
            </nav>
            <h2 class="page-title">DNS Kayıtları: <?= htmlspecialchars($zone->domain_name ?? '') ?></h2>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= URL::base('dns/createRecord/' . $zone->id) ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                Kayıt Ekle
            </a>
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
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>Tür</th>
                    <th>İsim</th>
                    <th>Değer</th>
                    <th>Öncelik</th>
                    <th>TTL</th>
                    <th class="w-1">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($records) && $records->result()): ?>
                <?php foreach ($records->result() as $record): ?>
                <tr>
                    <td>
                        <span class="badge bg-blue-lt text-blue fw-bold"><?= htmlspecialchars($record->type) ?></span>
                    </td>
                    <td><?= htmlspecialchars($record->name) ?></td>
                    <td class="text-truncate" style="max-width:300px;"><?= htmlspecialchars($record->value) ?></td>
                    <td><?= $record->priority ?: '-' ?></td>
                    <td><?= number_format($record->ttl) ?></td>
                    <td>
                        <a href="<?= URL::base('dns/deleteRecord/' . $record->id) ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Bu DNS kaydını silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-secondary py-5">
                        <p>Bu zon için henüz DNS kaydı eklenmemiş.</p>
                        <a href="<?= URL::base('dns/createRecord/' . $zone->id) ?>" class="btn btn-primary btn-sm">Kayıt ekle</a>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
