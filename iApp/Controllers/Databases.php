<?php namespace Project\Controllers;

class Databases extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\DatabaseModel();
    }

    public function main()
    {
        $this->pageTitle  = 'Veritabanları';
        $this->databases  = $this->model->getAll();
        $this->success    = Session::get('success');
        $this->error      = Session::get('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        $this->pageTitle = 'Yeni Veritabanı';
        $siteModel       = new \Project\Models\SiteModel();
        $this->sites     = $siteModel->getAll();
    }

    public function store()
    {
        if (!Http::isPost()) {
            Redirect::to('databases/main');
        }

        $data = [
            'site_id'     => Post::get('site_id'),
            'db_name'     => Post::get('db_name'),
            'db_user'     => Post::get('db_user'),
            'db_password' => password_hash(Post::get('db_password'), PASSWORD_BCRYPT),
            'charset'     => Post::get('charset') ?: 'utf8mb4',
            'status'      => Post::get('status') ?: 'active',
        ];

        if (empty($data['site_id']) || empty($data['db_name']) || empty($data['db_user'])) {
            Session::set('error', 'Site, veritabanı adı ve kullanıcı zorunludur.');
            Redirect::to('databases/create');
            return;
        }

        $this->model->create($data);
        Session::set('success', 'Veritabanı başarıyla oluşturuldu.');
        Redirect::to('databases/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::set('success', 'Veritabanı başarıyla silindi.');
        Redirect::to('databases/main');
    }
}
