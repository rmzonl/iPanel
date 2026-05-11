<?php namespace Project\Models;
use ZN\Model;
use DB;


class CronjobModel extends Model
{
    public function getAll(): array
    {
        return DB::table('cron_jobs cj')
            ->select('cj.*, s.domain as site_domain')
            ->join('sites s', 'cj.site_id = s.id', 'LEFT')
            ->orderBy('cj.id', 'desc')
            ->get()->result() ?: [];
    }

    public function getById($id)
    {
        return DB::table('cron_jobs')->where('id', $id)->get()->row();
    }

    public function getBySiteId($siteId): array
    {
        return DB::table('cron_jobs')->where('site_id', $siteId)->get()->result() ?: [];
    }

    public function getByReseller(int $resellerId): array
    {
        return DB::table('cron_jobs cj')
            ->select('cj.*, s.domain as site_domain')
            ->join('sites s',   'cj.site_id = s.id', 'LEFT')
            ->join('clients c', 's.client_id = c.id')
            ->where('c.reseller_id', $resellerId)
            ->orderBy('cj.id', 'desc')
            ->get()->result() ?: [];
    }

    public function create($data): int
    {
        DB::table('cron_jobs')->insert($data);
        return (int) DB::pdo()->lastInsertId();
    }

    public function update($id, $data)
    {
        return DB::table('cron_jobs')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return DB::table('cron_jobs')->where('id', $id)->delete();
    }
}
