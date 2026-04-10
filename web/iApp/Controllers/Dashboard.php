<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Inclusion\Project\View;
use Session;
use Project\Libraries\Acl;

class Dashboard extends Controller
{
    public function main()
    {
        $user        = Acl::user();
        $clientModel = new \Project\Models\ClientModel();
        $siteModel   = new \Project\Models\SiteModel();
        $domainModel = new \Project\Models\DomainModel();
        $sslModel    = new \Project\Models\SslModel();

        if ($user['role'] === 'admin') {
            $totalClients  = $clientModel->count();
            $totalSites    = $siteModel->count();
            $totalDomains  = $domainModel->count();
            $activeSSL     = $sslModel->countActive();
            $recentClients = $clientModel->getRecent(5);
            $recentSites   = $siteModel->getRecent(5);
        } else {
            // Reseller: yalnızca kendi müşterileri ve siteleri
            $myClients    = $clientModel->getByReseller((int) $user['id']);
            $mySites      = $siteModel->getByReseller((int) $user['id']);
            $clientRows   = $myClients ? $myClients->result() : [];
            $siteRows     = $mySites   ? $mySites->result()   : [];
            $totalClients = count($clientRows);
            $totalSites   = count($siteRows);
            $totalDomains = 0;
            $activeSSL    = 0;
            $recentClients = $myClients;
            $recentSites   = $mySites;
        }

        View::pageTitle('Dashboard');
        View::stats([
            'clients' => $totalClients,
            'sites'   => $totalSites,
            'domains' => $totalDomains,
            'ssl'     => $activeSSL,
        ]);
        View::recentClients($recentClients);
        View::recentSites($recentSites);
    }
}
