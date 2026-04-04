<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">IP Adresleri</h2>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= URL::base('ipaddresses/create') ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                IP Ekle
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
                    <th>IP Adresi</th>
                    <th>Ağ Maskesi</th>
                    <th>Ağ Geçidi</th>
                    <th>Tür</th>
                    <th>Müşteri</th>
                    <th>Durum</th>
                    <th>Notlar</th>
                    <th class="w-1">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($ips) && $ips->result()): ?>
                <?php foreach ($ips->result() as $ip): ?>
                <tr>
                    <td><?= $ip->id ?></td>
                    <td><strong><?= htmlspecialchars($ip->ip) ?></strong></td>
                    <td><?= htmlspecialchars($ip->netmask ?? '-') ?></td>
                    <td><?= htmlspecialchars($ip->gateway ?? '-') ?></td>
                    <td>
                        <?php
                        $typeLabel = ['server' => 'Sunucu', 'shared' => 'Paylaşımlı', 'dedicated' => 'Özel'];
                        $typeColor = ['server' => 'purple', 'shared' => 'blue', 'dedicated' => 'orange'];
                        $tc = $typeColor[$ip->type] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $tc ?>-lt text-<?= $tc ?>"><?= $typeLabel[$ip->type] ?? $ip->type ?></span>
                    </td>
                    <td>
                        <?php if ($ip->first_name): ?>
                            <?= htmlspecialchars($ip->first_name . ' ' . $ip->last_name) ?>
                        <?php else: ?>
                            <span class="text-secondary">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php $badge = ($ip->status === 'active') ? 'success' : 'secondary'; ?>
                        <span class="badge bg-<?= $badge ?>-lt text-<?= $badge ?>"><?= ($ip->status === 'active') ? 'Aktif' : 'Pasif' ?></span>
                    </td>
                    <td><?= htmlspecialchars($ip->notes ?? '-') ?></td>
                    <td>
                        <a href="<?= URL::base('ipaddresses/delete/' . $ip->id) ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Bu IP adresini silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center text-secondary py-5">
                        <p>Henüz IP adresi eklenmemiş.</p>
                        <a href="<?= URL::base('ipaddresses/create') ?>" class="btn btn-primary btn-sm">IP adresi ekle</a>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
