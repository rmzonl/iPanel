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


class Errors extends Controller
{
    public function notFound()
    {
        $this->pageTitle = '404 - Sayfa Bulunamadı';
        http_response_code(404);
    }
}
