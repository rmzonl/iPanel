<?php namespace Project\Models;

class FtpModel extends Model
{
    public function getAll()
    {
        return DB::table('ftp_accounts fa')
            ->select('fa.*, s.domain as site_domain')
            ->join('sites s', 'fa.site_id = s.id')
            ->orderBy('fa.id', 'desc')
            ->get();
    }

    public function getById($id)
    {
        return DB::table('ftp_accounts')->where('id', $id)->get()->row();
    }

    public function getBySiteId($siteId)
    {
        return DB::table('ftp_accounts')->where('site_id', $siteId)->get();
    }

    public function create($data)
    {
        return DB::table('ftp_accounts')->insert($data);
    }

    public function update($id, $data)
    {
        return DB::table('ftp_accounts')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return DB::table('ftp_accounts')->where('id', $id)->delete();
    }
}
