<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Domain Yönetimi</h2>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= URL::base('domains/create') ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                Yeni Domain
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
                    <th>Ana Site</th>
                    <th>Müşteri</th>
                    <th>Tür</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th class="w-1">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($domains) && $domains->result()): ?>
                <?php foreach ($domains->result() as $domain): ?>
                <tr>
                    <td><?= $domain->id ?></td>
                    <td><strong><?= htmlspecialchars($domain->name) ?></strong></td>
                    <td><?= htmlspecialchars($domain->site_domain ?? '-') ?></td>
                    <td><?= htmlspecialchars($domain->first_name . ' ' . $domain->last_name) ?></td>
                    <td>
                        <?php
                        $typeLabel = ['main' => 'Ana Domain', 'addon' => 'Ek Domain', 'subdomain' => 'Alt Domain', 'alias' => 'Alias'];
                        $typeColor = ['main' => 'blue', 'addon' => 'green', 'subdomain' => 'cyan', 'alias' => 'orange'];
                        $tc = $typeColor[$domain->type] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $tc ?>-lt text-<?= $tc ?>"><?= $typeLabel[$domain->type] ?? $domain->type ?></span>
                    </td>
                    <td>
                        <?php $badge = ($domain->status === 'active') ? 'success' : 'secondary'; ?>
                        <span class="badge bg-<?= $badge ?>-lt text-<?= $badge ?>"><?= ($domain->status === 'active') ? 'Aktif' : 'Pasif' ?></span>
                    </td>
                    <td><?= date('d.m.Y', strtotime($domain->created_at)) ?></td>
                    <td>
                        <a href="<?= URL::base('domains/delete/' . $domain->id) ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Bu domaini silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-secondary py-5">
                        <p>Henüz domain eklenmemiş.</p>
                        <a href="<?= URL::base('domains/create') ?>" class="btn btn-primary btn-sm">İlk domaini ekle</a>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
