<?php namespace Project\Models;
use ZN\Model;
use DB;


class CronjobModel extends Model
{
    public function getAll()
    {
        return DB::table('cron_jobs cj')
            ->select('cj.*, s.domain as site_domain')
            ->join('sites s', 'cj.site_id = s.id')
            ->orderBy('cj.id', 'desc')
            ->get();
    }

    public function getById($id)
    {
        return DB::table('cron_jobs')->where('id', $id)->get()->row();
    }

    public function getBySiteId($siteId)
    {
        return DB::table('cron_jobs')->where('site_id', $siteId)->get();
    }

    public function getByReseller(int $resellerId)
    {
        return DB::table('cron_jobs cj')
            ->select('cj.*, s.domain as site_domain')
            ->join('sites s',   'cj.site_id = s.id')
            ->join('clients c', 's.client_id = c.id')
            ->where('c.reseller_id', $resellerId)
            ->orderBy('cj.id', 'desc')
            ->get();
    }

    public function create($data): int
    {
        DB::insert('cron_jobs', $data);
        return (int) DB::pdo()->lastInsertId();
    }

    public function update($id, $data)
    {
        return DB::where('id', $id)->update('cron_jobs', $data);
    }

    public function delete($id)
    {
        return DB::table('cron_jobs')->where('id', $id)->delete();
    }
}
