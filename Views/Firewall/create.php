<?php include(VIEWS_DIR . 'layouts/header.php'); ?>

<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= URL::base('firewall/main') ?>">Güvenlik Duvarı</a></li>
                    <li class="breadcrumb-item active">Yeni Kural</li>
                </ol>
            </nav>
            <h2 class="page-title">Güvenlik Duvarı Kuralı Ekle</h2>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= URL::base('firewall/store') ?>">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label required">Kural Adı</label>
                            <input type="text" name="name" class="form-control" placeholder="SSH Erişimine İzin Ver" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Öncelik</label>
                            <input type="number" name="priority" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Eylem</label>
                            <select name="action" class="form-select">
                                <option value="allow">İzin Ver</option>
                                <option value="deny">Engelle</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Protokol</label>
                            <select name="protocol" class="form-select">
                                <option value="tcp">TCP</option>
                                <option value="udp">UDP</option>
                                <option value="icmp">ICMP</option>
                                <option value="all">Tümü</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Yön</label>
                            <select name="direction" class="form-select">
                                <option value="in">Gelen</option>
                                <option value="out">Giden</option>
                                <option value="both">Her İki Yön</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kaynak IP</label>
                            <input type="text" name="source_ip" class="form-control" placeholder="Boş = Tümü (örn: 192.168.1.0/24)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hedef Port</label>
                            <input type="text" name="dest_port" class="form-control" placeholder="Boş = Tümü (örn: 22 veya 80,443)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durum</label>
                            <select name="status" class="form-select">
                                <option value="active">Aktif</option>
                                <option value="inactive">Pasif</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= URL::base('firewall/main') ?>" class="btn btn-outline-secondary">İptal</a>
                        <button type="submit" class="btn btn-primary">Kural Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Yaygın Kurallar</h3>
            </div>
            <div class="list-group list-group-flush">
                <div class="list-group-item">
                    <div class="fw-bold">SSH (Port 22)</div>
                    <div class="text-secondary small">TCP, Gelen, Port 22</div>
                </div>
                <div class="list-group-item">
                    <div class="fw-bold">HTTP (Port 80)</div>
                    <div class="text-secondary small">TCP, Gelen, Port 80</div>
                </div>
                <div class="list-group-item">
                    <div class="fw-bold">HTTPS (Port 443)</div>
                    <div class="text-secondary small">TCP, Gelen, Port 443</div>
                </div>
                <div class="list-group-item">
                    <div class="fw-bold">MySQL (Port 3306)</div>
                    <div class="text-secondary small">TCP, Gelen, Port 3306</div>
                </div>
                <div class="list-group-item">
                    <div class="fw-bold">FTP (Port 21)</div>
                    <div class="text-secondary small">TCP, Gelen, Port 21</div>
                </div>
                <div class="list-group-item">
                    <div class="fw-bold">SMTP (Port 25)</div>
                    <div class="text-secondary small">TCP, Her İki Yön, Port 25</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include(VIEWS_DIR . 'layouts/footer.php'); ?>
