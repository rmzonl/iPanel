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


class Errors extends Controller
{
    public function notFound()
    {
        View::pageTitle('404 - Sayfa Bulunamadı');
        http_response_code(404);
    }
}
