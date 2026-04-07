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

    public function create($data)
    {
        return DB::table('email_accounts')->insert($data);
    }

    public function update($id, $data)
    {
        return DB::table('email_accounts')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return DB::table('email_accounts')->where('id', $id)->delete();
    }
}
