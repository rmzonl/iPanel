<?php namespace Project\Models;

class ClientModel extends Model
{
    public function getAll()
    {
        return DB::table('clients')->orderBy('id', 'desc')->get();
    }

    public function getById($id)
    {
        return DB::table('clients')->where('id', $id)->get()->row();
    }

    public function count()
    {
        return DB::table('clients')->count();
    }

    public function getRecent($limit = 5)
    {
        return DB::table('clients')->orderBy('id', 'desc')->limit($limit)->get();
    }

    public function create($data)
    {
        return DB::table('clients')->insert($data);
    }

    public function update($id, $data)
    {
        return DB::table('clients')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return DB::table('clients')->where('id', $id)->delete();
    }
}
