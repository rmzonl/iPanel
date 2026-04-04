<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Veritabanları</h2>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= URL::base('databases/create') ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                Yeni Veritabanı
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
                    <th>Veritabanı Adı</th>
                    <th>Kullanıcı</th>
                    <th>Site</th>
                    <th>Karakter Seti</th>
                    <th>Boyut (MB)</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th class="w-1">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($databases) && $databases->result()): ?>
                <?php foreach ($databases->result() as $db): ?>
                <tr>
                    <td><?= $db->id ?></td>
                    <td><strong><code><?= htmlspecialchars($db->db_name) ?></code></strong></td>
                    <td><code><?= htmlspecialchars($db->db_user) ?></code></td>
                    <td><?= htmlspecialchars($db->site_domain) ?></td>
                    <td><?= htmlspecialchars($db->charset) ?></td>
                    <td><?= number_format($db->size_mb) ?></td>
                    <td>
                        <?php $badge = ($db->status === 'active') ? 'success' : 'warning'; ?>
                        <span class="badge bg-<?= $badge ?>-lt text-<?= $badge ?>"><?= ($db->status === 'active') ? 'Aktif' : 'Askıda' ?></span>
                    </td>
                    <td><?= date('d.m.Y', strtotime($db->created_at)) ?></td>
                    <td>
                        <a href="<?= URL::base('databases/delete/' . $db->id) ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Bu veritabanını silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center text-secondary py-5">
                        <p>Henüz veritabanı eklenmemiş.</p>
                        <a href="<?= URL::base('databases/create') ?>" class="btn btn-primary btn-sm">Veritabanı oluştur</a>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
