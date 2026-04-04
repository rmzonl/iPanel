<?php namespace Project\Models;

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
            ->orWhere('email', $username)
            ->get()->row();
    }

    public function create($data)
    {
        return DB::table('users')->insert($data);
    }

    public function update($id, $data)
    {
        return DB::table('users')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return DB::table('users')->where('id', $id)->delete();
    }
}
