<?php namespace Project\Models;

class FirewallModel extends Model
{
    public function getAll()
    {
        return DB::table('firewall_rules')->orderBy('priority', 'asc')->get();
    }

    public function getById($id)
    {
        return DB::table('firewall_rules')->where('id', $id)->get()->row();
    }

    public function create($data)
    {
        return DB::table('firewall_rules')->insert($data);
    }

    public function update($id, $data)
    {
        return DB::table('firewall_rules')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return DB::table('firewall_rules')->where('id', $id)->delete();
    }
}
