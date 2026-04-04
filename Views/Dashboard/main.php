<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Dashboard</h2>
            <div class="text-secondary mt-1">Sunucu yönetim panelinize hoş geldiniz.</div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row row-deck row-cards mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Toplam Müşteri</div>
                </div>
                <div class="h1 mb-3"><?= (int)($totalClients ?? 0) ?></div>
                <div class="d-flex mb-2">
                    <div>Aktif müşteri sayısı</div>
                </div>
                <a href="<?= URL::base('clients/main') ?>" class="btn btn-primary btn-sm mt-2">Müşterileri Gör</a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Toplam Site</div>
                </div>
                <div class="h1 mb-3"><?= (int)($totalSites ?? 0) ?></div>
                <div class="d-flex mb-2">
                    <div>Barındırılan web sitesi</div>
                </div>
                <a href="<?= URL::base('sites/main') ?>" class="btn btn-success btn-sm mt-2">Siteleri Gör</a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Toplam Domain</div>
                </div>
                <div class="h1 mb-3"><?= (int)($totalDomains ?? 0) ?></div>
                <div class="d-flex mb-2">
                    <div>Kayıtlı domain adı</div>
                </div>
                <a href="<?= URL::base('domains/main') ?>" class="btn btn-info btn-sm mt-2">Domainleri Gör</a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Aktif SSL</div>
                </div>
                <div class="h1 mb-3"><?= (int)($activeSSL ?? 0) ?></div>
                <div class="d-flex mb-2">
                    <div>Aktif SSL sertifikası</div>
                </div>
                <a href="<?= URL::base('ssl/main') ?>" class="btn btn-warning btn-sm mt-2">SSL Yönetimi</a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Hızlı İşlemler</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <a href="<?= URL::base('clients/create') ?>" class="btn btn-outline-primary w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M15 8h6m-3 -3v6"/></svg>
                            Yeni Müşteri
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= URL::base('sites/create') ?>" class="btn btn-outline-success w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9"/><path d="M12 3v18m-4.5 -15.5h9m-10 5h11m-10 5h9"/></svg>
                            Yeni Site
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= URL::base('ipaddresses/create') ?>" class="btn btn-outline-info w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 12h6m-3 -3v6"/><circle cx="12" cy="12" r="9"/></svg>
                            IP Ekle
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= URL::base('backups/main') ?>" class="btn btn-outline-warning w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z"/><path d="M3 10h18v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z"/></svg>
                            Yedekle
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Clients -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Son Eklenen Müşteriler</h3>
                <div class="card-options">
                    <a href="<?= URL::base('clients/main') ?>" class="btn btn-sm btn-primary">Tümünü Gör</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>Şehir</th>
                            <th>Durum</th>
                            <th>Kayıt Tarihi</th>
                            <th class="w-1">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($recentClients) && $recentClients->result()): ?>
                        <?php foreach ($recentClients->result() as $client): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm me-2 bg-blue-lt text-blue fw-bold">
                                        <?= strtoupper(substr($client->first_name, 0, 1)) ?>
                                    </span>
                                    <div>
                                        <div><?= htmlspecialchars($client->first_name . ' ' . $client->last_name) ?></div>
                                        <?php if ($client->company_name): ?>
                                            <div class="text-secondary small"><?= htmlspecialchars($client->company_name) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-secondary"><?= htmlspecialchars($client->email) ?></td>
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
                                <a href="<?= URL::base('clients/edit/' . $client->id) ?>" class="btn btn-sm btn-outline-primary">Düzenle</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                Henüz müşteri eklenmemiş.
                                <a href="<?= URL::base('clients/create') ?>">İlk müşteriyi ekle</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
