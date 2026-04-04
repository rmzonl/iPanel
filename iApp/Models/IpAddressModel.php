<?php namespace Project\Models;

class IpAddressModel extends Model
{
    public function getAll()
    {
        return DB::table('ip_addresses ip')
            ->select('ip.*, c.first_name, c.last_name, c.company_name')
            ->leftJoin('clients c', 'ip.client_id = c.id')
            ->orderBy('ip.id', 'desc')
            ->get();
    }

    public function getById($id)
    {
        return DB::table('ip_addresses')->where('id', $id)->get()->row();
    }

    public function getAvailable()
    {
        return DB::table('ip_addresses')
            ->where('status', 'active')
            ->where('client_id', NULL)
            ->get();
    }

    public function getAllActive()
    {
        return DB::table('ip_addresses')->where('status', 'active')->get();
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
