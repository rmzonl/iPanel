<?php namespace Project\Controllers;

class Email extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\EmailModel();
    }

    public function main()
    {
        $this->pageTitle = 'E-posta Hesapları';
        $this->emails    = $this->model->getAll();
        $this->success   = Session::select('success');
        $this->error     = Session::select('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        $this->pageTitle = 'Yeni E-posta Hesabı';
        $siteModel       = new \Project\Models\SiteModel();
        $this->sites     = $siteModel->getAll();
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::to('email/main');
        }

        $siteId   = Post::get('site_id');
        $username = Post::get('username');
        $domain   = Post::get('domain');
        $password = Post::get('password');

        if (empty($siteId) || empty($username) || empty($password)) {
            Session::insert('error', 'Site, kullanıcı adı ve şifre zorunludur.');
            Redirect::to('email/create');
            return;
        }

        $email = $username . '@' . $domain;

        $data = [
            'site_id'  => $siteId,
            'username' => $username,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'quota'    => Post::get('quota') ?: 1024,
            'status'   => Post::get('status') ?: 'active',
        ];

        $this->model->create($data);
        Session::insert('success', 'E-posta hesabı başarıyla oluşturuldu.');
        Redirect::to('email/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'E-posta hesabı başarıyla silindi.');
        Redirect::to('email/main');
    }
}
