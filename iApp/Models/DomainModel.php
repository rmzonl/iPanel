<?php namespace Project\Models;

class DomainModel extends Model
{
    public function getAll()
    {
        return DB::table('domains d')
            ->select('d.*, s.domain as site_domain, c.first_name, c.last_name')
            ->join('sites s', 'd.site_id = s.id')
            ->join('clients c', 'd.client_id = c.id')
            ->orderBy('d.id', 'desc')
            ->get();
    }

    public function getById($id)
    {
        return DB::table('domains')->where('id', $id)->get()->row();
    }

    public function getBySiteId($siteId)
    {
        return DB::table('domains')->where('site_id', $siteId)->get();
    }

    public function count()
    {
        return DB::table('domains')->count();
    }

    public function create($data)
    {
        return DB::table('domains')->insert($data);
    }

    public function delete($id)
    {
        return DB::table('domains')->where('id', $id)->delete();
    }
}
