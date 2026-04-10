<?php namespace Project\Models;
use ZN\Model;
use DB;


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
        return DB::insert('firewall_rules', $data);
    }

    public function update($id, $data)
    {
        return DB::where('id', $id)->update('firewall_rules', $data);
    }

    public function delete($id)
    {
        return DB::table('firewall_rules')->where('id', $id)->delete();
    }
}
