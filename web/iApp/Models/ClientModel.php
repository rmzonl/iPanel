<?php namespace Project\Models;
use ZN\Model;
use DB;


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
        return DB::table('clients')->get()->totalRows();
    }

    public function getRecent($limit = 5)
    {
        return DB::table('clients')->orderBy('id', 'desc')->limit(0, $limit)->get();
    }

    public function getByReseller(int $resellerId)
    {
        return DB::table('clients')
            ->where('reseller_id', $resellerId)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function create($data): int
    {
        DB::insert('clients', $data);
        return (int) DB::pdo()->lastInsertId();
    }

    public function update($id, $data)
    {
        return DB::where('id', $id)->update('clients', $data);
    }

    public function delete($id)
    {
        return DB::table('clients')->where('id', $id)->delete();
    }
}
