<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Inclusion\Project\View;
use Session;
use Redirect;
use Project\Libraries\CsrfGuard;
use Project\Libraries\AuditLogger;
use Project\Libraries\AgentClient;

/**
 * Node.js sürüm yönetimi — sadece admin.
 * Tüm ayrıcalıklı işlemler agent (root) üzerinden yapılır.
 */
class NodeManager extends Controller
{
    public function main()
    {
        View::pageTitle('Node.js Yönetimi');

        try {
            $agent = new AgentClient();
            View::nodeInfo($agent->call('node.status', []));
        } catch (\Throwable $e) {
            View::nodeInfo([
                'node_version'  => null,
                'npm_version'   => null,
                'pm2_installed' => false,
                'nvm_installed' => false,
                'nvm_versions'  => [],
            ]);
            View::error('Agent bağlantı hatası: ' . htmlspecialchars($e->getMessage()));
        }

        View::success(Session::select('success'));
        View::error(View::error() ?: Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    /** nvm kur */
    public function installNvm()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('nodemanager/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('nodemanager/main'); return; }

        try {
            $agent = new AgentClient();
            $agent->call('node.installNvm', []);
            AuditLogger::log('node.nvm_install', 'system', null, 'nvm kuruldu');
            Session::insert('success', 'nvm başarıyla kuruldu.');
        } catch (\Throwable $e) {
            Session::insert('error', 'nvm kurulum hatası: ' . htmlspecialchars($e->getMessage()));
        }

        Redirect::action('nodemanager/main');
    }

    /** Belirli bir Node.js sürümünü kur */
    public function installVersion()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('nodemanager/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('nodemanager/main'); return; }

        $version = trim((string) Post::node_version());
        if (!preg_match('/^(lts|latest|v?\d+(\.\d+){0,2})$/', $version)) {
            Session::insert('error', 'Geçersiz Node.js sürümü.');
            Redirect::action('nodemanager/main');
            return;
        }

        try {
            $agent = new AgentClient();
            $agent->call('node.installVersion', ['version' => $version]);
            AuditLogger::log('node.install', 'system', null, "Node.js $version kuruldu");
            Session::insert('success', "Node.js $version başarıyla kuruldu.");
        } catch (\Throwable $e) {
            Session::insert('error', 'Kurulum hatası: ' . htmlspecialchars($e->getMessage()));
        }

        Redirect::action('nodemanager/main');
    }

    /** Varsayılan Node.js sürümünü değiştir */
    public function setDefault()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('nodemanager/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('nodemanager/main'); return; }

        $version = trim((string) Post::node_version());
        if (!preg_match('/^v?\d+(\.\d+){0,2}$/', $version)) {
            Session::insert('error', 'Geçersiz sürüm formatı.');
            Redirect::action('nodemanager/main');
            return;
        }

        try {
            $agent = new AgentClient();
            $agent->call('node.setDefault', ['version' => $version]);
            AuditLogger::log('node.set_default', 'system', null, "Node.js varsayılan: $version");
            Session::insert('success', "Node.js $version varsayılan olarak ayarlandı.");
        } catch (\Throwable $e) {
            Session::insert('error', 'Sürüm değiştirme hatası: ' . htmlspecialchars($e->getMessage()));
        }

        Redirect::action('nodemanager/main');
    }

    /** Node.js kaldır */
    public function remove()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('nodemanager/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('nodemanager/main'); return; }

        $version = trim((string) Post::node_version());
        if (!preg_match('/^v?\d+(\.\d+){0,2}$/', $version)) {
            Session::insert('error', 'Geçersiz sürüm formatı.');
            Redirect::action('nodemanager/main');
            return;
        }

        try {
            $agent = new AgentClient();
            $agent->call('node.removeVersion', ['version' => $version]);
            AuditLogger::log('node.remove', 'system', null, "Node.js $version kaldırıldı");
            Session::insert('success', "Node.js $version kaldırıldı.");
        } catch (\Throwable $e) {
            Session::insert('error', 'Kaldırma hatası: ' . htmlspecialchars($e->getMessage()));
        }

        Redirect::action('nodemanager/main');
    }

    /** pm2'yi global olarak kur */
    public function pm2()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('nodemanager/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('nodemanager/main'); return; }

        $action = Post::action();
        if ($action !== 'install') {
            Session::insert('error', 'Geçersiz işlem.');
            Redirect::action('nodemanager/main');
            return;
        }

        try {
            $agent = new AgentClient();
            $agent->call('node.installPm2', []);
            AuditLogger::log('node.pm2_install', 'system', null, 'pm2 kuruldu');
            Session::insert('success', 'pm2 başarıyla kuruldu.');
        } catch (\Throwable $e) {
            Session::insert('error', 'pm2 kurulum hatası: ' . htmlspecialchars($e->getMessage()));
        }

        Redirect::action('nodemanager/main');
    }
}
