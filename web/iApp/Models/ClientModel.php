<?php namespace Project\Models;
use ZN\Model;
use DB;


class ClientModel extends Model
{
    public function getAll(): array
    {
        return DB::table('clients')->orderBy('id', 'desc')->get()->result() ?: [];
    }

    public function getById($id)
    {
        return DB::table('clients')->where('id', $id)->get()->row();
    }

    public function count(): int
    {
        return DB::table('clients')->get()->totalRows();
    }

    public function getRecent($limit = 5): array
    {
        return DB::table('clients')->orderBy('id', 'desc')->limit(0, $limit)->get()->result() ?: [];
    }

    public function getByReseller(int $resellerId): array
    {
        return DB::table('clients')
            ->where('reseller_id', $resellerId)
            ->orderBy('id', 'desc')
            ->get()->result() ?: [];
    }

    public function create($data): int
    {
        DB::table('clients')->insert($data);
        return (int) DB::pdo()->lastInsertId();
    }

    public function update($id, $data)
    {
        return DB::table('clients')->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return DB::table('clients')->where('id', $id)->delete();
    }
}
