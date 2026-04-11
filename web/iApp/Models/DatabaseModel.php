<?php namespace Project\Models;
use ZN\Model;
use DB;


class DatabaseModel extends Model
{
    public function getAll()
    {
        return DB::table('site_databases sd')
            ->select('sd.*, s.domain as site_domain')
            ->join('sites s', 'sd.site_id = s.id')
            ->orderBy('sd.id', 'desc')
            ->get();
    }

    public function getById($id)
    {
        return DB::table('site_databases')->where('id', $id)->get()->row();
    }

    public function getBySiteId($siteId)
    {
        return DB::table('site_databases')->where('site_id', $siteId)->get();
    }

    public function getByReseller(int $resellerId)
    {
        return DB::table('site_databases sd')
            ->select('sd.*, s.domain as site_domain')
            ->join('sites s',   'sd.site_id = s.id')
            ->join('clients c', 's.client_id = c.id')
            ->where('c.reseller_id', $resellerId)
            ->orderBy('sd.id', 'desc')
            ->get();
    }

    public function create($data): int
    {
        DB::table('site_databases')->insert($data);
        return (int) DB::pdo()->lastInsertId();
    }

    public function update($id, $data)
    {
        return DB::table('site_databases')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return DB::table('site_databases')->where('id', $id)->delete();
    }
}
