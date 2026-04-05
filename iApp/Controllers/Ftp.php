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


class Ftp extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\FtpModel();
    }

    public function main()
    {
        $this->pageTitle = 'FTP Hesapları';
        $this->ftpAccounts = $this->model->getAll();
        $this->success   = Session::select('success');
        $this->error     = Session::select('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        $this->pageTitle = 'Yeni FTP Hesabı';
        $siteModel       = new \Project\Models\SiteModel();
        $this->sites     = $siteModel->getAll();
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::to('ftp/main');
        }

        $data = [
            'site_id'  => Post::get('site_id'),
            'username' => Post::get('username'),
            'password' => password_hash(Post::get('password'), PASSWORD_BCRYPT),
            'home_dir' => Post::get('home_dir'),
            'quota'    => Post::get('quota') ?: 0,
            'status'   => Post::get('status') ?: 'active',
        ];

        if (empty($data['site_id']) || empty($data['username'])) {
            Session::insert('error', 'Site ve kullanıcı adı zorunludur.');
            Redirect::to('ftp/create');
            return;
        }

        $this->model->create($data);
        Session::insert('success', 'FTP hesabı başarıyla oluşturuldu.');
        Redirect::to('ftp/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'FTP hesabı başarıyla silindi.');
        Redirect::to('ftp/main');
    }
}
