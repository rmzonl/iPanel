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
            $stats = [
                'clients' => $clientModel->count(),
                'sites'   => $siteModel->count(),
                'domains' => $domainModel->count(),
                'ssl'     => $sslModel->countActive(),
            ];
            $recentClients = $clientModel->getRecent(5);
            $recentSites   = $siteModel->getRecent(5);
        } else {
            // Reseller: yalnızca kendi müşterileri ve siteleri
            $myClients = $clientModel->getByReseller((int) $user['id']);
            $mySites   = $siteModel->getByReseller((int) $user['id']);
            $myDomains = $domainModel->getByReseller((int) $user['id']);

            $stats = [
                'clients' => count($myClients),
                'sites'   => count($mySites),
                'domains' => count($myDomains),
                'ssl'     => count($sslModel->getByReseller((int) $user['id'])),
            ];
            $recentClients = array_slice($myClients, 0, 5);
            $recentSites   = array_slice($mySites, 0, 5);
        }

        View::pageTitle('Dashboard');
        View::stats($stats);
        View::recentClients($recentClients);
        View::recentSites($recentSites);
    }
}
