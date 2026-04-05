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


class Auth extends Controller
{
    public function login()
    {
        if (Session::select('admin_user')) {
            Redirect::action('dashboard/main');
        }

        if (Http::isRequestMethod('post')) {
            $username = Post::get('username');
            $password = Post::get('password');

            if (empty($username) || empty($password)) {
                View::error('Kullanıcı adı ve şifre gereklidir.');
                return;
            }

            $user = DB::table('users')
                ->where('username', $username)
                ->whereOr('email', $username)
                ->get()
                ->row();

            if ($user && password_verify($password, $user->password)) {
                if ($user->status == 0) {
                    View::error('Hesabınız devre dışı bırakılmıştır.');
                    return;
                }

                Session::insert('admin_user', [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'role'     => $user->role,
                ]);

                DB::table('users')->where('id', $user->id)->update([
                    'last_login' => date('Y-m-d H:i:s')
                ]);

                Redirect::action('dashboard/main');
            } else {
                View::error('Geçersiz kullanıcı adı veya şifre.');
            }
        }
    }

    public function logout()
    {
        Session::delete('admin_user');
        Redirect::action('auth/login');
    }
}
