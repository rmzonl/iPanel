<?php namespace Project\Controllers;

class Cronjobs extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\CronjobModel();
    }

    public function main()
    {
        $this->pageTitle  = 'Cron İşleri';
        $this->cronjobs   = $this->model->getAll();
        $this->success    = Session::get('success');
        $this->error      = Session::get('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        $this->pageTitle = 'Yeni Cron İşi';
        $siteModel       = new \Project\Models\SiteModel();
        $this->sites     = $siteModel->getAll();
    }

    public function store()
    {
        if (!Http::isPost()) {
            Redirect::to('cronjobs/main');
        }

        $data = [
            'site_id'  => Post::get('site_id'),
            'title'    => Post::get('title'),
            'command'  => Post::get('command'),
            'schedule' => Post::get('schedule'),
            'status'   => Post::get('status') ?: 'active',
        ];

        if (empty($data['site_id']) || empty($data['title']) || empty($data['command']) || empty($data['schedule'])) {
            Session::set('error', 'Tüm zorunlu alanları doldurunuz.');
            Redirect::to('cronjobs/create');
            return;
        }

        $this->model->create($data);
        Session::set('success', 'Cron işi başarıyla oluşturuldu.');
        Redirect::to('cronjobs/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::set('success', 'Cron işi başarıyla silindi.');
        Redirect::to('cronjobs/main');
    }
}
