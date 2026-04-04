<?php namespace Project\Models;

class SslModel extends Model
{
    public function getAll()
    {
        return DB::table('ssl_certificates sc')
            ->select('sc.*, d.name as domain_name')
            ->join('domains d', 'sc.domain_id = d.id')
            ->orderBy('sc.id', 'desc')
            ->get();
    }

    public function getById($id)
    {
        return DB::table('ssl_certificates')->where('id', $id)->get()->row();
    }

    public function countActive()
    {
        return DB::table('ssl_certificates')->where('status', 'active')->get()->totalRows();
    }

    public function create($data)
    {
        return DB::table('ssl_certificates')->insert($data);
    }

    public function update($id, $data)
    {
        return DB::table('ssl_certificates')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return DB::table('ssl_certificates')->where('id', $id)->delete();
    }
}
