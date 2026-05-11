<?php namespace Project\Models;
use ZN\Model;
use DB;


class DomainModel extends Model
{
    public function getAll(): array
    {
        return DB::table('domains d')
            ->select('d.*, s.domain as site_domain, c.first_name, c.last_name')
            ->join('sites s', 'd.site_id = s.id')
            ->join('clients c', 'd.client_id = c.id')
            ->orderBy('d.id', 'desc')
            ->get()->result() ?: [];
    }

    public function getById($id)
    {
        return DB::table('domains')->where('id', $id)->get()->row();
    }

    public function getBySiteId($siteId): array
    {
        return DB::table('domains')->where('site_id', $siteId)->get()->result() ?: [];
    }

    public function count(): int
    {
        return DB::table('domains')->get()->totalRows();
    }

    public function getByReseller(int $resellerId): array
    {
        return DB::table('domains d')
            ->select('d.*, s.domain as site_domain, c.first_name, c.last_name')
            ->join('sites s',   'd.site_id = s.id')
            ->join('clients c', 'd.client_id = c.id')
            ->where('c.reseller_id', $resellerId)
            ->orderBy('d.id', 'desc')
            ->get()->result() ?: [];
    }

    public function create($data): int
    {
        DB::insert('domains', $data);
        return (int) DB::pdo()->lastInsertId();
    }

    public function delete($id)
    {
        return DB::table('domains')->where('id', $id)->delete();
    }
}
