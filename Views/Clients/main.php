<?php include(VIEW_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Müşteriler</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <a href="<?= URL::base('clients/create') ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                Yeni Müşteri
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
    <div class="card-header">
        <h3 class="card-title">Tüm Müşteriler</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ad Soyad / Firma</th>
                    <th>E-posta</th>
                    <th>Telefon</th>
                    <th>Şehir</th>
                    <th>Durum</th>
                    <th>Kayıt Tarihi</th>
                    <th class="w-1">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($clients) && $clients->result()): ?>
                <?php foreach ($clients->result() as $client): ?>
                <tr>
                    <td><?= $client->id ?></td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($client->first_name . ' ' . $client->last_name) ?></div>
                        <?php if ($client->company_name): ?>
                            <div class="text-secondary small"><?= htmlspecialchars($client->company_name) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($client->email) ?></td>
                    <td><?= htmlspecialchars($client->phone ?? '-') ?></td>
                    <td><?= htmlspecialchars($client->city ?? '-') ?></td>
                    <td>
                        <?php
                        $statusMap = ['active' => 'success', 'suspended' => 'warning', 'terminated' => 'danger'];
                        $statusLabel = ['active' => 'Aktif', 'suspended' => 'Askıya Alındı', 'terminated' => 'Sonlandırıldı'];
                        $badge = $statusMap[$client->status] ?? 'secondary';
                        $label = $statusLabel[$client->status] ?? $client->status;
                        ?>
                        <span class="badge bg-<?= $badge ?>-lt text-<?= $badge ?>"><?= $label ?></span>
                    </td>
                    <td><?= date('d.m.Y', strtotime($client->created_at)) ?></td>
                    <td>
                        <div class="btn-group">
                            <a href="<?= URL::base('clients/edit/' . $client->id) ?>" class="btn btn-sm btn-outline-primary">Düzenle</a>
                            <a href="<?= URL::base('clients/delete/' . $client->id) ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Bu müşteriyi silmek istediğinize emin misiniz?')">Sil</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-secondary py-5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg mb-2 text-secondary" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
                        <p class="mt-2">Henüz müşteri eklenmemiş.</p>
                        <a href="<?= URL::base('clients/create') ?>" class="btn btn-primary btn-sm">İlk müşteriyi ekle</a>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include(VIEW_DIR . 'layouts/footer.php'); ?>
