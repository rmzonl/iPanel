<?php namespace Project\Controllers;

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
            Redirect::to('auth/login');
            return;
        }

        $this->authUser = $user;
        Masterpage::title(($this->pageTitle ?? 'Panel') . ' — iPanel');
    }
}
