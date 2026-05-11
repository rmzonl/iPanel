<?php namespace Project\Models;
use ZN\Model;
use DB;


class SiteModel extends Model
{
    public function getAll(): array
    {
        return DB::table('sites s')
            ->select("s.*, CONCAT(c.first_name, ' ', c.last_name) AS client_name, c.company_name, i.ip")
            ->join('clients c', 's.client_id = c.id')
            ->join('ip_addresses i', 's.ip_id = i.id', 'LEFT')
            ->orderBy('s.id', 'desc')
            ->get()->result() ?: [];
    }

    public function getById($id)
    {
        return DB::table('sites s')
            ->select("s.*, CONCAT(c.first_name, ' ', c.last_name) AS client_name, c.company_name, i.ip")
            ->join('clients c', 's.client_id = c.id')
            ->join('ip_addresses i', 's.ip_id = i.id', 'LEFT')
            ->where('s.id', $id)
            ->get()->row();
    }

    public function getByClientId($clientId): array
    {
        return DB::table('sites')
            ->where('client_id', $clientId)
            ->orderBy('id', 'desc')
            ->get()->result() ?: [];
    }

    public function count(): int
    {
        return DB::table('sites')->get()->totalRows();
    }

    public function getByReseller(int $resellerId): array
    {
        return DB::table('sites s')
            ->select("s.*, CONCAT(c.first_name, ' ', c.last_name) AS client_name, c.company_name, i.ip")
            ->join('clients c',      's.client_id = c.id')
            ->join('ip_addresses i', 's.ip_id = i.id', 'LEFT')
            ->where('c.reseller_id', $resellerId)
            ->orderBy('s.id', 'desc')
            ->get()->result() ?: [];
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
