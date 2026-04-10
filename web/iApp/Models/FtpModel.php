<?php namespace Project\Models;
use ZN\Model;
use DB;


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

    public function getByReseller(int $resellerId)
    {
        return DB::table('ftp_accounts fa')
            ->select('fa.*, s.domain as site_domain')
            ->join('sites s',   'fa.site_id = s.id')
            ->join('clients c', 's.client_id = c.id')
            ->where('c.reseller_id', $resellerId)
            ->orderBy('fa.id', 'desc')
            ->get();
    }

    public function create($data): int
    {
        DB::insert('ftp_accounts', $data);
        return (int) DB::pdo()->lastInsertId();
    }

    public function update($id, $data)
    {
        return DB::where('id', $id)->update('ftp_accounts', $data);
    }

    public function delete($id)
    {
        return DB::table('ftp_accounts')->where('id', $id)->delete();
    }
}
