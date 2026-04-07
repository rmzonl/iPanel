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

    public function create($data)
    {
        return DB::table('cron_jobs')->insert($data);
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
