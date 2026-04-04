<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Yedeklemeler</h2>
        </div>
        <div class="col-auto ms-auto">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackupModal">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                Yedek Al
            </button>
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
                    <th>Dosya Adı</th>
                    <th>Site / Müşteri</th>
                    <th>Tür</th>
                    <th>Boyut (MB)</th>
                    <th>Durum</th>
                    <th>Başlangıç</th>
                    <th>Bitiş</th>
                    <th class="w-1">İşlemler</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($backups) && $backups->result()): ?>
                <?php foreach ($backups->result() as $backup): ?>
                <tr>
                    <td><?= $backup->id ?></td>
                    <td><code><?= htmlspecialchars($backup->filename ?? '-') ?></code></td>
                    <td>
                        <?php if ($backup->site_domain): ?>
                            <span class="badge bg-blue-lt text-blue"><?= htmlspecialchars($backup->site_domain) ?></span>
                        <?php elseif ($backup->first_name): ?>
                            <?= htmlspecialchars($backup->first_name . ' ' . $backup->last_name) ?>
                        <?php else: ?>
                            <span class="text-secondary">Tümü</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $typeLabel = ['full' => 'Tam', 'database' => 'Veritabanı', 'files' => 'Dosyalar', 'email' => 'E-posta'];
                        ?>
                        <?= $typeLabel[$backup->type] ?? $backup->type ?>
                    </td>
                    <td><?= number_format($backup->size_mb) ?></td>
                    <td>
                        <?php
                        $statusMap = ['pending' => 'warning', 'running' => 'blue', 'completed' => 'success', 'failed' => 'danger'];
                        $statusLabel = ['pending' => 'Bekliyor', 'running' => 'Çalışıyor', 'completed' => 'Tamamlandı', 'failed' => 'Başarısız'];
                        $badge = $statusMap[$backup->status] ?? 'secondary';
                        $label = $statusLabel[$backup->status] ?? $backup->status;
                        ?>
                        <span class="badge bg-<?= $badge ?>-lt text-<?= $badge ?>"><?= $label ?></span>
                    </td>
                    <td><?= $backup->started_at ? date('d.m.Y H:i', strtotime($backup->started_at)) : '-' ?></td>
                    <td><?= $backup->completed_at ? date('d.m.Y H:i', strtotime($backup->completed_at)) : '-' ?></td>
                    <td>
                        <a href="<?= URL::base('backups/delete/' . $backup->id) ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Bu yedekleme kaydını silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center text-secondary py-5">
                        <p>Henüz yedekleme oluşturulmamış.</p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createBackupModal">İlk yedeği al</button>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Backup Modal -->
<div class="modal modal-blur fade" id="createBackupModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yedek Oluştur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= URL::base('backups/create') ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Site (opsiyonel)</label>
                        <select name="site_id" class="form-select">
                            <option value="">Tüm siteler</option>
                            <?php if (!empty($sites) && $sites->result()): ?>
                                <?php foreach ($sites->result() as $site): ?>
                                <option value="<?= $site->id ?>"><?= htmlspecialchars($site->domain) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Müşteri (opsiyonel)</label>
                        <select name="client_id" class="form-select">
                            <option value="">Tüm müşteriler</option>
                            <?php if (!empty($clients) && $clients->result()): ?>
                                <?php foreach ($clients->result() as $client): ?>
                                <option value="<?= $client->id ?>"><?= htmlspecialchars($client->first_name . ' ' . $client->last_name) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Yedek Türü</label>
                        <select name="type" class="form-select">
                            <option value="full">Tam Yedek</option>
                            <option value="database">Yalnızca Veritabanı</option>
                            <option value="files">Yalnızca Dosyalar</option>
                            <option value="email">Yalnızca E-postalar</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Yedeklemeyi Başlat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
