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


class Settings extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\SettingsModel();
    }

    public function main()
    {
        View::pageTitle('Ayarlar');
        View::serverSettings($this->model->getServerSettings());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function save()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::action('settings/main');
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
        Redirect::action('settings/main');
    }
}
