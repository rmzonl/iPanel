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


class Cronjobs extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\CronjobModel();
    }

    public function main()
    {
        View::pageTitle('Cron İşleri');
        View::cronjobs($this->model->getAll());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        View::pageTitle('Yeni Cron İşi');
        $siteModel       = new \Project\Models\SiteModel();
        View::sites($siteModel->getAll());
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::action('cronjobs/main');
        }

        $data = [
            'site_id'  => Post::get('site_id'),
            'title'    => Post::get('title'),
            'command'  => Post::get('command'),
            'schedule' => Post::get('schedule'),
            'status'   => Post::get('status') ?: 'active',
        ];

        if (empty($data['site_id']) || empty($data['title']) || empty($data['command']) || empty($data['schedule'])) {
            Session::insert('error', 'Tüm zorunlu alanları doldurunuz.');
            Redirect::action('cronjobs/create');
            return;
        }

        $this->model->create($data);
        Session::insert('success', 'Cron işi başarıyla oluşturuldu.');
        Redirect::action('cronjobs/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'Cron işi başarıyla silindi.');
        Redirect::action('cronjobs/main');
    }
}
