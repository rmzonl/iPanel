<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Siteler</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <a href="<?= URL::base('sites/create') ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                Yeni Site
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
                    <th>#</th>
                    <th>Domain</th>
                    <th>Müşteri</th>
                    <th>IP</th>
                    <th>PHP</th>
                    <th>Durum</th>
                    <th>Disk Kotası</th>
                    <th>Oluşturma</th>
                    <th class="w-1">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($sites) && $sites->result()): ?>
                <?php foreach ($sites->result() as $site): ?>
                <tr>
                    <td><?= $site->id ?></td>
                    <td><strong><?= htmlspecialchars($site->domain) ?></strong></td>
                    <td><?= htmlspecialchars($site->first_name . ' ' . $site->last_name) ?></td>
                    <td><?= htmlspecialchars($site->ip ?? '-') ?></td>
                    <td><span class="badge bg-blue-lt">PHP <?= htmlspecialchars($site->php_version) ?></span></td>
                    <td>
                        <?php
                        $statusMap = ['active' => 'success', 'suspended' => 'warning', 'deleted' => 'danger'];
                        $statusLabel = ['active' => 'Aktif', 'suspended' => 'Askıda', 'deleted' => 'Silindi'];
                        $badge = $statusMap[$site->status] ?? 'secondary';
                        $label = $statusLabel[$site->status] ?? $site->status;
                        ?>
                        <span class="badge bg-<?= $badge ?>-lt text-<?= $badge ?>"><?= $label ?></span>
                    </td>
                    <td><?= $site->disk_quota > 0 ? $site->disk_quota . ' MB' : 'Sınırsız' ?></td>
                    <td><?= date('d.m.Y', strtotime($site->created_at)) ?></td>
                    <td>
                        <div class="btn-group">
                            <a href="<?= URL::base('sites/edit/' . $site->id) ?>" class="btn btn-sm btn-outline-primary">Düzenle</a>
                            <a href="<?= URL::base('sites/delete/' . $site->id) ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Bu siteyi silmek istediğinize emin misiniz?')">Sil</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center text-secondary py-5">
                        <p>Henüz site eklenmemiş.</p>
                        <a href="<?= URL::base('sites/create') ?>" class="btn btn-primary btn-sm">İlk siteyi ekle</a>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
