<?php namespace Project\Controllers;

use ZN\Controller;
use Redirect;

class Home extends Controller
{
    public function main()
    {
        Redirect::action('dashboard/main');
    }
}
