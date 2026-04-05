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


class Initialize extends Controller
{
    public function main()
    {
        $controller = strtolower(CURRENT_CFUNCTION ?? '');

        if ($controller === 'auth') {
            Masterpage::bodyPage('layouts/auth-body');
            return;
        }

        $user = Session::select('admin_user');

        if (empty($user)) {
            Redirect::action('auth/login');
            return;
        }

        View::authUser($user);
    }
}
