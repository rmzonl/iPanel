<?php namespace Project\Controllers;

class Errors extends Controller
{
    public function notFound()
    {
        $this->pageTitle = '404 - Sayfa Bulunamadı';
        http_response_code(404);
    }
}
