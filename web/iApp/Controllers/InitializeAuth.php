<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Inclusion\Project\Masterpage;

/**
 * Sadece Auth controller'ı için çalışan başlangıç kontrolcüsü.
 * Auth sayfalarına auth-body masterpage'i yükler.
 */
class InitializeAuth extends Controller
{
    const include = ['Auth'];

    public function main()
    {
        header_remove('X-Powered-By');
        Masterpage::attributes(['html' => ['data-bs-theme' => 'dark']]);
        Masterpage::bodyPage('layouts/auth-body');
    }
}
