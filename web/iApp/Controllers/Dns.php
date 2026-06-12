<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Inclusion\Project\View;
use DB;
use Session;
use Redirect;
use Project\Libraries\Acl;
use Project\Libraries\CsrfGuard;
use Project\Libraries\AuditLogger;
use Project\Libraries\InputValidator;

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
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function rows()
    {
        $user = Acl::user();
        $data = ($user['role'] === 'admin')
            ? $this->model->getAllZones()
            : $this->model->getZonesByReseller($user['id']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['data' => $data]);
        exit;
    }

    public function records(int $zoneId)
    {
        Acl::requireOwnership(Acl::ownsDnsZone($zoneId));

        $zone = $this->model->getZoneById($zoneId);
        if (!$zone) { Redirect::action('dns/main'); return; }

        View::pageTitle('DNS Kayıtları: ' . ($zone->domain_name ?? ''));
        View::zone($zone);
        View::records($this->model->getRecordsByZoneId($zoneId));
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function createRecord(int $zoneId)
    {
        Acl::requireOwnership(Acl::ownsDnsZone($zoneId));

        $zone = $this->model->getZoneById($zoneId);
        if (!$zone) { Redirect::action('dns/main'); return; }

        View::pageTitle('DNS Kayıdı Ekle');
        View::zone($zone);
        View::zoneId($zoneId);
    }

    public function storeRecord()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('dns/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('dns/main'); return; }

        $zoneId = (int) Post::zone_id();
        Acl::requireOwnership(Acl::ownsDnsZone($zoneId));

        $raw = InputValidator::sanitize([
            'zone_id'  => $zoneId,
            'type'     => Post::type(),
            'name'     => Post::name(),
            'value'    => Post::value(),
            'priority' => (int) (Post::priority() ?: 0),
            'ttl'      => (int) (Post::ttl() ?: 3600),
        ]);

        $v = InputValidator::from($raw)
            ->required('type', 'Tür')
            ->required('name', 'İsim')
            ->required('value', 'Değer')
            ->in('type', ['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'SRV', 'CAA', 'PTR'], 'DNS kayıt türü');

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('dns/createRecord/' . $zoneId);
            return;
        }

        $id = $this->model->createRecord($raw);
        AuditLogger::log('dns.createRecord', 'dns_record', $id, "DNS kayıdı eklendi: {$raw['type']} {$raw['name']}");
        Session::insert('success', 'DNS kayıdı başarıyla eklendi.');
        Redirect::action('dns/records/' . $zoneId);
    }

    public function deleteZone(int $id)
    {
        Acl::requireOwnership(Acl::ownsDnsZone($id));
        $this->model->deleteZone($id);
        AuditLogger::log('dns.deleteZone', 'dns_zone', $id, 'DNS zonu silindi.');
        Session::insert('success', 'DNS zonu başarıyla silindi.');
        Redirect::action('dns/main');
    }

    public function deleteRecord(int $id)
    {
        Acl::requireOwnership(Acl::ownsDnsRecord($id));
        $record = $this->model->getRecordById($id);
        $zoneId = $record ? (int) $record->zone_id : null;
        $this->model->deleteRecord($id);
        AuditLogger::log('dns.deleteRecord', 'dns_record', $id, 'DNS kayıdı silindi.');
        Session::insert('success', 'DNS kayıdı başarıyla silindi.');
        $zoneId ? Redirect::action('dns/records/' . $zoneId) : Redirect::action('dns/main');
    }
}
