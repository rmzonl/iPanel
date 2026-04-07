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


class Ftp extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\FtpModel();
    }

    public function main()
    {
        View::pageTitle('FTP Hesapları');
        View::ftpAccounts($this->model->getAll());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        View::pageTitle('Yeni FTP Hesabı');
        $siteModel       = new \Project\Models\SiteModel();
        View::sites($siteModel->getAll());
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::action('ftp/main');
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
            Redirect::action('ftp/create');
            return;
        }

        $this->model->create($data);
        Session::insert('success', 'FTP hesabı başarıyla oluşturuldu.');
        Redirect::action('ftp/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'FTP hesabı başarıyla silindi.');
        Redirect::action('ftp/main');
    }
}
