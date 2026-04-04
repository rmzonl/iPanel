<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">E-posta Hesapları</h2>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= URL::base('email/create') ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                Yeni E-posta Hesabı
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
                    <th>E-posta</th>
                    <th>Site</th>
                    <th>Kota (MB)</th>
                    <th>Kullanılan (MB)</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th class="w-1">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($emails) && $emails->result()): ?>
                <?php foreach ($emails->result() as $email): ?>
                <tr>
                    <td><?= $email->id ?></td>
                    <td><strong><?= htmlspecialchars($email->email) ?></strong></td>
                    <td><?= htmlspecialchars($email->site_domain) ?></td>
                    <td><?= number_format($email->quota) ?></td>
                    <td>
                        <?php $percent = $email->quota > 0 ? round(($email->used / $email->quota) * 100) : 0; ?>
                        <div class="d-flex align-items-center">
                            <div class="me-2"><?= number_format($email->used) ?></div>
                            <div class="progress flex-grow-1" style="height:4px;">
                                <div class="progress-bar <?= $percent > 80 ? 'bg-danger' : 'bg-blue' ?>" style="width:<?= min($percent, 100) ?>%"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php $badge = ($email->status === 'active') ? 'success' : 'warning'; ?>
                        <span class="badge bg-<?= $badge ?>-lt text-<?= $badge ?>"><?= ($email->status === 'active') ? 'Aktif' : 'Askıda' ?></span>
                    </td>
                    <td><?= date('d.m.Y', strtotime($email->created_at)) ?></td>
                    <td>
                        <a href="<?= URL::base('email/delete/' . $email->id) ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Bu e-posta hesabını silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-secondary py-5">
                        <p>Henüz e-posta hesabı eklenmemiş.</p>
                        <a href="<?= URL::base('email/create') ?>" class="btn btn-primary btn-sm">E-posta hesabı oluştur</a>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
