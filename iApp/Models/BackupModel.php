<?php namespace Project\Models;

class BackupModel extends Model
{
    public function getAll()
    {
        return DB::table('backups b')
            ->select('b.*, s.domain as site_domain, c.first_name, c.last_name')
            ->join('sites s', 'b.site_id = s.id', 'LEFT')
            ->join('clients c', 'b.client_id = c.id', 'LEFT')
            ->orderBy('b.id', 'desc')
            ->get();
    }

    public function getById($id)
    {
        return DB::table('backups')->where('id', $id)->get()->row();
    }

    public function create($data)
    {
        return DB::table('backups')->insert($data);
    }

    public function update($id, $data)
    {
        return DB::table('backups')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return DB::table('backups')->where('id', $id)->delete();
    }
}
