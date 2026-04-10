<?php namespace Project\Models;
use ZN\Model;
use DB;


class EmailModel extends Model
{
    public function getAll()
    {
        return DB::table('email_accounts ea')
            ->select('ea.*, s.domain as site_domain')
            ->join('sites s', 'ea.site_id = s.id')
            ->orderBy('ea.id', 'desc')
            ->get();
    }

    public function getById($id)
    {
        return DB::table('email_accounts')->where('id', $id)->get()->row();
    }

    public function getBySiteId($siteId)
    {
        return DB::table('email_accounts')->where('site_id', $siteId)->get();
    }

    public function getByReseller(int $resellerId)
    {
        return DB::table('email_accounts ea')
            ->select('ea.*, s.domain as site_domain')
            ->join('sites s',   'ea.site_id = s.id')
            ->join('clients c', 's.client_id = c.id')
            ->where('c.reseller_id', $resellerId)
            ->orderBy('ea.id', 'desc')
            ->get();
    }

    public function create($data): int
    {
        DB::insert('email_accounts', $data);
        return (int) DB::pdo()->lastInsertId();
    }

    public function update($id, $data)
    {
        return DB::where('id', $id)->update('email_accounts', $data);
    }

    public function delete($id)
    {
        return DB::table('email_accounts')->where('id', $id)->delete();
    }
}
