<?php namespace Project\Controllers;
use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Request\Get;
use ZN\Inclusion\Project\Masterpage;
use ZN\Inclusion\Project\View;
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

        View::pageTitle('Dashboard');
        View::totalClients($clientModel->count());
        View::totalSites($siteModel->count());
        View::totalDomains($domainModel->count());
        View::activeSSL($sslModel->countActive());
        View::recentClients($clientModel->getRecent(5));
    }
}
