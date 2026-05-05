<?php namespace Project\Models;
use ZN\Model;
use DB;


class SiteModel extends Model
{
    public function getAll()
    {
        return DB::table('sites s')
            ->select('s.*, c.first_name, c.last_name, c.company_name, i.ip')
            ->join('clients c', 's.client_id = c.id')
            ->join('ip_addresses i', 's.ip_id = i.id', 'LEFT')
            ->orderBy('s.id', 'desc')
            ->get();
    }

    public function getById($id)
    {
        return DB::table('sites s')
            ->select('s.*, c.first_name, c.last_name, c.company_name, i.ip')
            ->join('clients c', 's.client_id = c.id')
            ->join('ip_addresses i', 's.ip_id = i.id', 'LEFT')
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
        return DB::table('sites')->get()->totalRows();
    }

    public function getByReseller(int $resellerId)
    {
        return DB::table('sites s')
            ->select('s.*, c.first_name, c.last_name, c.company_name, i.ip')
            ->join('clients c',      's.client_id = c.id')
            ->join('ip_addresses i', 's.ip_id = i.id', 'LEFT')
            ->where('c.reseller_id', $resellerId)
            ->orderBy('s.id', 'desc')
            ->get();
    }

    public function create($data): int
    {
        DB::insert('sites', $data);
        return (int) DB::pdo()->lastInsertId();
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
