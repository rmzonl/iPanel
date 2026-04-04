<?php namespace Project\Controllers;

class Initialize extends Controller
{
    protected $publicRoutes = ['auth/login', 'auth/logout', 'auth/dologin', 'errors/notfound'];

    public function main()
    {
        $controller = strtolower(CURRENT_CFUNCTION ?? '');
        $method     = strtolower(CURRENT_CMETHOD ?? '');
        $currentUrl = $controller . '/' . $method;

        $isPublic = false;
        foreach ($this->publicRoutes as $route) {
            if (strpos($currentUrl, str_replace('/', '', str_replace('-', '', $route))) !== false ||
                $currentUrl === $route) {
                $isPublic = true;
                break;
            }
        }

        // Also allow auth controller completely
        if ($controller === 'auth') {
            $isPublic = true;
        }

        if (!$isPublic && !Session::get('admin_user')) {
            Redirect::to('auth/login');
        }
    }
}
