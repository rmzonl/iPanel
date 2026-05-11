<?php namespace Project\Models;
use ZN\Model;
use DB;


class IpAddressModel extends Model
{
    public function getAll(): array
    {
        return DB::table('ip_addresses ip')
            ->select("ip.*, COALESCE(c.company_name, CONCAT(c.first_name, ' ', c.last_name)) AS client_name")
            ->join('clients c', 'ip.client_id = c.id', 'LEFT')
            ->orderBy('ip.id', 'desc')
            ->get()->result() ?: [];
    }

    public function getById($id)
    {
        return DB::table('ip_addresses')->where('id', $id)->get()->row();
    }

    public function getAvailable(): array
    {
        return DB::table('ip_addresses')
            ->where('status', 'active')
            ->where('client_id', NULL)
            ->get()->result() ?: [];
    }

    public function getAllActive(): array
    {
        return DB::table('ip_addresses')->where('status', 'active')->get()->result() ?: [];
    }

    public function create($data)
    {
        return DB::table('ip_addresses')->insert($data);
    }

    public function update($id, $data)
    {
        return DB::table('ip_addresses')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return DB::table('ip_addresses')->where('id', $id)->delete();
    }
}
