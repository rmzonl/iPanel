<?php namespace Project\Controllers;

class Dashboard extends Controller
{
    public function main()
    {
        $clientModel = new \Project\Models\ClientModel();
        $siteModel   = new \Project\Models\SiteModel();
        $domainModel = new \Project\Models\DomainModel();
        $sslModel    = new \Project\Models\SslModel();

        $this->pageTitle       = 'Dashboard';
        $this->totalClients    = $clientModel->count();
        $this->totalSites      = $siteModel->count();
        $this->totalDomains    = $domainModel->count();
        $this->activeSSL       = $sslModel->countActive();
        $this->recentClients   = $clientModel->getRecent(5);
    }
}
