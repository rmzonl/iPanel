<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">FTP Hesapları</h2>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= URL::base('ftp/create') ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                Yeni FTP Hesabı
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
                    <th>Kullanıcı Adı</th>
                    <th>Site</th>
                    <th>Ana Dizin</th>
                    <th>Kota (MB)</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th class="w-1">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($ftpAccounts) && $ftpAccounts->result()): ?>
                <?php foreach ($ftpAccounts->result() as $ftp): ?>
                <tr>
                    <td><?= $ftp->id ?></td>
                    <td><strong><?= htmlspecialchars($ftp->username) ?></strong></td>
                    <td><?= htmlspecialchars($ftp->site_domain) ?></td>
                    <td><code><?= htmlspecialchars($ftp->home_dir ?? '-') ?></code></td>
                    <td><?= $ftp->quota > 0 ? number_format($ftp->quota) : 'Sınırsız' ?></td>
                    <td>
                        <?php $badge = ($ftp->status === 'active') ? 'success' : 'warning'; ?>
                        <span class="badge bg-<?= $badge ?>-lt text-<?= $badge ?>"><?= ($ftp->status === 'active') ? 'Aktif' : 'Askıda' ?></span>
                    </td>
                    <td><?= date('d.m.Y', strtotime($ftp->created_at)) ?></td>
                    <td>
                        <a href="<?= URL::base('ftp/delete/' . $ftp->id) ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Bu FTP hesabını silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-secondary py-5">
                        <p>Henüz FTP hesabı eklenmemiş.</p>
                        <a href="<?= URL::base('ftp/create') ?>" class="btn btn-primary btn-sm">FTP hesabı oluştur</a>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
