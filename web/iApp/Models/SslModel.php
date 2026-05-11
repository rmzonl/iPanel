<?php namespace Project\Models;
use ZN\Model;
use DB;


class SslModel extends Model
{
    public function getAll(): array
    {
        return DB::table('ssl_certificates sc')
            ->select('sc.*, d.name as domain_name')
            ->join('domains d', 'sc.domain_id = d.id')
            ->orderBy('sc.id', 'desc')
            ->get()->result() ?: [];
    }

    public function getById($id)
    {
        return DB::table('ssl_certificates')->where('id', $id)->get()->row();
    }

    public function countActive(): int
    {
        return DB::table('ssl_certificates')->where('status', 'active')->get()->totalRows();
    }

    public function getByReseller(int $resellerId): array
    {
        return DB::table('ssl_certificates sc')
            ->select('sc.*, d.name as domain_name')
            ->join('domains d',  'sc.domain_id = d.id')
            ->join('clients c',  'd.client_id  = c.id')
            ->where('c.reseller_id', $resellerId)
            ->orderBy('sc.id', 'desc')
            ->get()->result() ?: [];
    }

    public function create($data): int
    {
        DB::table('ssl_certificates')->insert($data);
        return (int) DB::pdo()->lastInsertId();
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
