<?php namespace Project\Controllers;
use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Request\Get;
use ZN\Inclusion\Project\Masterpage;
use DB;
use Session;
use Redirect;
use URL;


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
