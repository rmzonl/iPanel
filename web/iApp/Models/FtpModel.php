<?php namespace Project\Models;
use ZN\Model;
use DB;


class FtpModel extends Model
{
    public function getAll(): array
    {
        return DB::table('ftp_accounts fa')
            ->select('fa.*, s.domain as site_domain')
            ->join('sites s', 'fa.site_id = s.id', 'LEFT')
            ->orderBy('fa.id', 'desc')
            ->get()->result() ?: [];
    }

    public function getById($id)
    {
        return DB::table('ftp_accounts')->where('id', $id)->get()->row();
    }

    public function getBySiteId($siteId): array
    {
        return DB::table('ftp_accounts')->where('site_id', $siteId)->get()->result() ?: [];
    }

    public function getByReseller(int $resellerId): array
    {
        return DB::table('ftp_accounts fa')
            ->select('fa.*, s.domain as site_domain')
            ->join('sites s',   'fa.site_id = s.id', 'LEFT')
            ->join('clients c', 's.client_id = c.id')
            ->where('c.reseller_id', $resellerId)
            ->orderBy('fa.id', 'desc')
            ->get()->result() ?: [];
    }

    public function create($data): int
    {
        DB::table('ftp_accounts')->insert($data);
        return (int) DB::pdo()->lastInsertId();
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
