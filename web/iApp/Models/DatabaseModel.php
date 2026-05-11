<?php namespace Project\Models;
use ZN\Model;
use DB;


class DatabaseModel extends Model
{
    public function getAll(): array
    {
        return DB::table('site_databases sd')
            ->select('sd.*, s.domain as site_domain')
            ->join('sites s', 'sd.site_id = s.id', 'LEFT')
            ->orderBy('sd.id', 'desc')
            ->get()->result() ?: [];
    }

    public function getById($id)
    {
        return DB::table('site_databases')->where('id', $id)->get()->row();
    }

    public function getBySiteId($siteId): array
    {
        return DB::table('site_databases')->where('site_id', $siteId)->get()->result() ?: [];
    }

    public function getByReseller(int $resellerId): array
    {
        return DB::table('site_databases sd')
            ->select('sd.*, s.domain as site_domain')
            ->join('sites s',   'sd.site_id = s.id', 'LEFT')
            ->join('clients c', 's.client_id = c.id')
            ->where('c.reseller_id', $resellerId)
            ->orderBy('sd.id', 'desc')
            ->get()->result() ?: [];
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
