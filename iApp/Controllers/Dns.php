<?php namespace Project\Controllers;
use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Request\Get;
use ZN\Inclusion\Project\Masterpage;
use ZN\Inclusion\Project\View;
use DB;
use Session;
use Redirect;
use URL;


class Dns extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\DnsModel();
    }

    public function main()
    {
        View::pageTitle('DNS Yönetimi');
        View::zones($this->model->getAllZones());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function records($zoneId)
    {
        $zone = $this->model->getZoneById($zoneId);
        if (!$zone) {
            Redirect::action('dns/main');
        }
        View::pageTitle('DNS Kayıtları: ' . $zone->domain_name);
        View::zone($zone);
        View::records($this->model->getRecordsByZoneId($zoneId));
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function createRecord($zoneId)
    {
        $zone = $this->model->getZoneById($zoneId);
        if (!$zone) {
            Redirect::action('dns/main');
        }
        View::pageTitle('DNS Kaydı Ekle');
        View::zone($zone);
        View::zoneId($zoneId);
    }

    public function storeRecord()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::action('dns/main');
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
            Session::insert('error', 'Tür, isim ve değer zorunludur.');
            Redirect::action('dns/createRecord/' . $zoneId);
            return;
        }

        $this->model->createRecord($data);
        Session::insert('success', 'DNS kaydı başarıyla eklendi.');
        Redirect::action('dns/records/' . $zoneId);
    }

    public function deleteZone($id)
    {
        $this->model->deleteZone($id);
        Session::insert('success', 'DNS zonu başarıyla silindi.');
        Redirect::action('dns/main');
    }

    public function deleteRecord($id)
    {
        $record = $this->model->getRecordById($id);
        $zoneId = $record ? $record->zone_id : null;
        $this->model->deleteRecord($id);
        Session::insert('success', 'DNS kaydı başarıyla silindi.');
        if ($zoneId) {
            Redirect::action('dns/records/' . $zoneId);
        } else {
            Redirect::action('dns/main');
        }
    }
}
