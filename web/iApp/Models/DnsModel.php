<?php namespace Project\Models;
use ZN\Model;
use DB;


class DnsModel extends Model
{
    public function getAllZones()
    {
        return DB::table('dns_zones dz')
            ->select('dz.*, d.name as domain_name')
            ->join('domains d', 'dz.domain_id = d.id')
            ->orderBy('dz.id', 'desc')
            ->get();
    }

    public function getZoneById($id)
    {
        return DB::table('dns_zones dz')
            ->select('dz.*, d.name as domain_name')
            ->join('domains d', 'dz.domain_id = d.id')
            ->where('dz.id', $id)
            ->get()->row();
    }

    public function getZoneByDomainId($domainId)
    {
        return DB::table('dns_zones')->where('domain_id', $domainId)->get()->row();
    }

    public function getZonesByReseller(int $resellerId)
    {
        return DB::table('dns_zones dz')
            ->select('dz.*, d.name as domain_name')
            ->join('domains d',  'dz.domain_id = d.id')
            ->join('clients c',  'd.client_id  = c.id')
            ->where('c.reseller_id', $resellerId)
            ->orderBy('dz.id', 'desc')
            ->get();
    }

    public function createZone($data): int
    {
        DB::table('dns_zones')->insert($data);
        return (int) DB::pdo()->lastInsertId();
    }

    public function deleteZone($id)
    {
        return DB::table('dns_zones')->where('id', $id)->delete();
    }

    public function getRecordsByZoneId($zoneId)
    {
        return DB::table('dns_records')->where('zone_id', $zoneId)->orderBy('type')->get();
    }

    public function getRecordById($id)
    {
        return DB::table('dns_records')->where('id', $id)->get()->row();
    }

    public function createRecord($data)
    {
        return DB::table('dns_records')->insert($data);
    }

    public function updateRecord($id, $data)
    {
        return DB::table('dns_records')->where('id', $id)->update($data);
    }

    public function deleteRecord($id)
    {
        return DB::table('dns_records')->where('id', $id)->delete();
    }
}
