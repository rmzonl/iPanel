<?php namespace Project\Models;

class SiteModel extends Model
{
    public function getAll()
    {
        return DB::table('sites s')
            ->select('s.*, c.first_name, c.last_name, c.company_name, i.ip')
            ->join('clients c', 's.client_id = c.id')
            ->leftJoin('ip_addresses i', 's.ip_id = i.id')
            ->orderBy('s.id', 'desc')
            ->get();
    }

    public function getById($id)
    {
        return DB::table('sites s')
            ->select('s.*, c.first_name, c.last_name, c.company_name, i.ip')
            ->join('clients c', 's.client_id = c.id')
            ->leftJoin('ip_addresses i', 's.ip_id = i.id')
            ->where('s.id', $id)
            ->get()->row();
    }

    public function getByClientId($clientId)
    {
        return DB::table('sites')
            ->where('client_id', $clientId)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function count()
    {
        return DB::table('sites')->count();
    }

    public function create($data)
    {
        return DB::table('sites')->insert($data);
    }

    public function update($id, $data)
    {
        return DB::table('sites')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return DB::table('sites')->where('id', $id)->delete();
    }
}
