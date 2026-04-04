<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Güvenlik Duvarı Kuralları</h2>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= URL::base('firewall/create') ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                Kural Ekle
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
                    <th>Öncelik</th>
                    <th>Kural Adı</th>
                    <th>Eylem</th>
                    <th>Protokol</th>
                    <th>Yön</th>
                    <th>Kaynak IP</th>
                    <th>Hedef Port</th>
                    <th>Durum</th>
                    <th class="w-1">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($rules) && $rules->result()): ?>
                <?php foreach ($rules->result() as $rule): ?>
                <tr>
                    <td><?= $rule->priority ?></td>
                    <td><strong><?= htmlspecialchars($rule->name) ?></strong></td>
                    <td>
                        <?php $actionBadge = ($rule->action === 'allow') ? 'success' : 'danger'; ?>
                        <span class="badge bg-<?= $actionBadge ?>-lt text-<?= $actionBadge ?> text-uppercase fw-bold">
                            <?= ($rule->action === 'allow') ? 'İzin Ver' : 'Engelle' ?>
                        </span>
                    </td>
                    <td><code><?= strtoupper(htmlspecialchars($rule->protocol)) ?></code></td>
                    <td>
                        <?php
                        $dirLabel = ['in' => 'Gelen', 'out' => 'Giden', 'both' => 'Her İki Yön'];
                        echo $dirLabel[$rule->direction] ?? $rule->direction;
                        ?>
                    </td>
                    <td><?= htmlspecialchars($rule->source_ip ?? 'Tümü') ?></td>
                    <td><?= htmlspecialchars($rule->dest_port ?? 'Tümü') ?></td>
                    <td>
                        <?php $badge = ($rule->status === 'active') ? 'success' : 'secondary'; ?>
                        <span class="badge bg-<?= $badge ?>-lt text-<?= $badge ?>"><?= ($rule->status === 'active') ? 'Aktif' : 'Pasif' ?></span>
                    </td>
                    <td>
                        <a href="<?= URL::base('firewall/delete/' . $rule->id) ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Bu kuralı silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center text-secondary py-5">
                        <p>Henüz güvenlik duvarı kuralı eklenmemiş.</p>
                        <a href="<?= URL::base('firewall/create') ?>" class="btn btn-primary btn-sm">Kural ekle</a>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
