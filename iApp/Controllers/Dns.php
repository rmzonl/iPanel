<?php namespace Project\Controllers;

class Dns extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\DnsModel();
    }

    public function main()
    {
        $this->pageTitle = 'DNS Yönetimi';
        $this->zones     = $this->model->getAllZones();
        $this->success   = Session::get('success');
        $this->error     = Session::get('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function records($zoneId)
    {
        $zone = $this->model->getZoneById($zoneId);
        if (!$zone) {
            Redirect::to('dns/main');
        }
        $this->pageTitle = 'DNS Kayıtları: ' . $zone->domain_name;
        $this->zone      = $zone;
        $this->records   = $this->model->getRecordsByZoneId($zoneId);
        $this->success   = Session::get('success');
        $this->error     = Session::get('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function createRecord($zoneId)
    {
        $zone = $this->model->getZoneById($zoneId);
        if (!$zone) {
            Redirect::to('dns/main');
        }
        $this->pageTitle = 'DNS Kaydı Ekle';
        $this->zone      = $zone;
        $this->zoneId    = $zoneId;
    }

    public function storeRecord()
    {
        if (!Http::isPost()) {
            Redirect::to('dns/main');
        }

        $zoneId = Post::get('zone_id');
        $data = [
            'zone_id'  => $zoneId,
            'type'     => Post::get('type'),
            'name'     => Post::get('name'),
            'value'    => Post::get('value'),
            'priority' => Post::get('priority') ?: 0,
            'ttl'      => Post::get('ttl') ?: 3600,
        ];

        if (empty($data['type']) || empty($data['name']) || empty($data['value'])) {
            Session::set('error', 'Tür, isim ve değer zorunludur.');
            Redirect::to('dns/createRecord/' . $zoneId);
            return;
        }

        $this->model->createRecord($data);
        Session::set('success', 'DNS kaydı başarıyla eklendi.');
        Redirect::to('dns/records/' . $zoneId);
    }

    public function deleteZone($id)
    {
        $this->model->deleteZone($id);
        Session::set('success', 'DNS zonu başarıyla silindi.');
        Redirect::to('dns/main');
    }

    public function deleteRecord($id)
    {
        $record = $this->model->getRecordById($id);
        $zoneId = $record ? $record->zone_id : null;
        $this->model->deleteRecord($id);
        Session::set('success', 'DNS kaydı başarıyla silindi.');
        if ($zoneId) {
            Redirect::to('dns/records/' . $zoneId);
        } else {
            Redirect::to('dns/main');
        }
    }
}
