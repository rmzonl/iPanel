<?php namespace Project\Models;
use ZN\Model;
use DB;


class BackupModel extends Model
{
    public function getAll()
    {
        return DB::table('backups b')
            ->select('b.*, s.domain as site_domain, c.first_name, c.last_name')
            ->join('sites s', 'b.site_id = s.id', 'LEFT')
            ->join('clients c', 'b.client_id = c.id', 'LEFT')
            ->orderBy('b.id', 'desc')
            ->get();
    }

    public function getById($id)
    {
        return DB::table('backups')->where('id', $id)->get()->row();
    }

    public function getByReseller(int $resellerId)
    {
        return DB::table('backups b')
            ->select('b.*, s.domain as site_domain, c.first_name, c.last_name')
            ->join('sites s',   'b.site_id = s.id', 'LEFT')
            ->join('clients c', 'b.client_id = c.id', 'LEFT')
            ->where('c.reseller_id', $resellerId)
            ->orderBy('b.id', 'desc')
            ->get();
    }

    public function create($data): int
    {
        DB::insert('backups', $data);
        return (int) DB::pdo()->lastInsertId();
    }

    public function update($id, $data)
    {
        return DB::where('id', $id)->update('backups', $data);
    }

    public function delete($id)
    {
        return DB::table('backups')->where('id', $id)->delete();
    }
}
