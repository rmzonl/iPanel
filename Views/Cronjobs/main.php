<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Cron İşleri</h2>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= URL::base('cronjobs/create') ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                Yeni Cron İşi
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
                    <th>Başlık</th>
                    <th>Komut</th>
                    <th>Zamanlama</th>
                    <th>Site</th>
                    <th>Son Çalışma</th>
                    <th>Durum</th>
                    <th class="w-1">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($cronjobs) && $cronjobs->result()): ?>
                <?php foreach ($cronjobs->result() as $cron): ?>
                <tr>
                    <td><?= $cron->id ?></td>
                    <td><strong><?= htmlspecialchars($cron->title) ?></strong></td>
                    <td><code class="text-truncate d-block" style="max-width:200px;"><?= htmlspecialchars($cron->command) ?></code></td>
                    <td><code><?= htmlspecialchars($cron->schedule) ?></code></td>
                    <td><?= htmlspecialchars($cron->site_domain) ?></td>
                    <td><?= $cron->last_run ? date('d.m.Y H:i', strtotime($cron->last_run)) : 'Henüz çalışmadı' ?></td>
                    <td>
                        <?php $badge = ($cron->status === 'active') ? 'success' : 'secondary'; ?>
                        <span class="badge bg-<?= $badge ?>-lt text-<?= $badge ?>"><?= ($cron->status === 'active') ? 'Aktif' : 'Pasif' ?></span>
                    </td>
                    <td>
                        <a href="<?= URL::base('cronjobs/delete/' . $cron->id) ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Bu cron işini silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-secondary py-5">
                        <p>Henüz cron işi eklenmemiş.</p>
                        <a href="<?= URL::base('cronjobs/create') ?>" class="btn btn-primary btn-sm">Cron işi oluştur</a>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
