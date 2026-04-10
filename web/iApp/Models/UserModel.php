<?php namespace Project\Models;
use ZN\Model;
use DB;


class UserModel extends Model
{
    public function getAll()
    {
        return DB::table('users')->orderBy('id', 'desc')->get();
    }

    public function getById($id)
    {
        return DB::table('users')->where('id', $id)->get()->row();
    }

    public function getByUsername($username)
    {
        return DB::table('users')
            ->where('username', $username)
            ->whereOr('email', $username)
            ->get()->row();
    }

    public function create($data)
    {
        return DB::insert('users', $data);
    }

    public function update($id, $data)
    {
        return DB::where('id', $id)->update('users', $data);
    }

    public function delete($id)
    {
        return DB::table('users')->where('id', $id)->delete();
    }
}
