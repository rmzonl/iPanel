<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">SSL Sertifikaları</h2>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= URL::base('ssl/create') ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                SSL Ekle
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
                    <th>Tür</th>
                    <th>Otomatik Yenileme</th>
                    <th>Veriliş Tarihi</th>
                    <th>Son Kullanma</th>
                    <th>Durum</th>
                    <th class="w-1">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($certs) && $certs->result()): ?>
                <?php foreach ($certs->result() as $cert): ?>
                <tr>
                    <td><?= $cert->id ?></td>
                    <td><strong><?= htmlspecialchars($cert->domain_name) ?></strong></td>
                    <td>
                        <?php
                        $typeLabel = ['letsencrypt' => "Let's Encrypt", 'paid' => 'Ücretli', 'self_signed' => 'Kendinden İmzalı'];
                        ?>
                        <?= $typeLabel[$cert->type] ?? $cert->type ?>
                    </td>
                    <td>
                        <?php if ($cert->auto_renew): ?>
                            <span class="badge bg-green-lt text-green">Evet</span>
                        <?php else: ?>
                            <span class="badge bg-secondary-lt text-secondary">Hayır</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $cert->issued_at ? date('d.m.Y', strtotime($cert->issued_at)) : '-' ?></td>
                    <td>
                        <?php if ($cert->expires_at): ?>
                            <?php
                            $expiry = strtotime($cert->expires_at);
                            $daysLeft = ceil(($expiry - time()) / 86400);
                            $color = $daysLeft > 30 ? 'success' : ($daysLeft > 7 ? 'warning' : 'danger');
                            ?>
                            <span class="text-<?= $color ?>"><?= date('d.m.Y', $expiry) ?></span>
                            <small class="text-secondary">(<?= $daysLeft ?> gün)</small>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $statusMap = ['active' => 'success', 'expired' => 'danger', 'pending' => 'warning', 'failed' => 'danger'];
                        $statusLabel = ['active' => 'Aktif', 'expired' => 'Süresi Doldu', 'pending' => 'Bekliyor', 'failed' => 'Başarısız'];
                        $badge = $statusMap[$cert->status] ?? 'secondary';
                        $label = $statusLabel[$cert->status] ?? $cert->status;
                        ?>
                        <span class="badge bg-<?= $badge ?>-lt text-<?= $badge ?>"><?= $label ?></span>
                    </td>
                    <td>
                        <a href="<?= URL::base('ssl/delete/' . $cert->id) ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Bu SSL sertifikasını silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-secondary py-5">
                        <p>Henüz SSL sertifikası eklenmemiş.</p>
                        <a href="<?= URL::base('ssl/create') ?>" class="btn btn-primary btn-sm">SSL ekle</a>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
