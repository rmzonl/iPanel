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


class Settings extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\SettingsModel();
    }

    public function main()
    {
        $this->pageTitle      = 'Ayarlar';
        $this->serverSettings = $this->model->getServerSettings();
        $this->success        = Session::select('success');
        $this->error          = Session::select('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function save()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::to('settings/main');
        }

        $keys = [
            'panel_name', 'server_ip', 'php_versions', 'max_sites_per_client',
            'max_disk_per_client', 'max_bandwidth_per_client', 'default_php_version',
            'ssl_email', 'backup_path', 'webroot_base',
        ];

        foreach ($keys as $key) {
            $value = Post::get($key);
            if ($value !== null) {
                $this->model->set('server', $key, $value);
            }
        }

        Session::insert('success', 'Ayarlar başarıyla kaydedildi.');
        Redirect::to('settings/main');
    }
}
