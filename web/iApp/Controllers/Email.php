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


class Email extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\EmailModel();
    }

    public function main()
    {
        View::pageTitle('E-posta Hesapları');
        View::emails($this->model->getAll());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        View::pageTitle('Yeni E-posta Hesabı');
        $siteModel       = new \Project\Models\SiteModel();
        View::sites($siteModel->getAll());
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::action('email/main');
        }

        $siteId   = Post::get('site_id');
        $username = Post::get('username');
        $domain   = Post::get('domain');
        $password = Post::get('password');

        if (empty($siteId) || empty($username) || empty($password)) {
            Session::insert('error', 'Site, kullanıcı adı ve şifre zorunludur.');
            Redirect::action('email/create');
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
        Redirect::action('email/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'E-posta hesabı başarıyla silindi.');
        Redirect::action('email/main');
    }
}
