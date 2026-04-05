<?php namespace Project\Models;
use ZN\Model;
use DB;


class SettingsModel extends Model
{
    public function getByScope($scope, $scopeId = null)
    {
        $query = DB::table('settings')->where('scope', $scope);
        if ($scopeId === null) {
            $query = $query->whereNull('scope_id');
        } else {
            $query = $query->where('scope_id', $scopeId);
        }
        return $query->get();
    }

    public function getServerSettings()
    {
        return $this->getByScope('server');
    }

    public function getSettingValue($scope, $key, $scopeId = null)
    {
        $query = DB::table('settings')
            ->where('scope', $scope)
            ->where('setting_key', $key);
        if ($scopeId === null) {
            $query = $query->whereNull('scope_id');
        } else {
            $query = $query->where('scope_id', $scopeId);
        }
        $row = $query->get()->row();
        return $row ? $row->setting_value : null;
    }

    public function set($scope, $key, $value, $scopeId = null)
    {
        $existing = $this->getSettingValue($scope, $key, $scopeId);
        $data = [
            'scope'         => $scope,
            'scope_id'      => $scopeId,
            'setting_key'   => $key,
            'setting_value' => $value,
        ];
        if ($existing !== null) {
            $query = DB::table('settings')
                ->where('scope', $scope)
                ->where('setting_key', $key);
            if ($scopeId === null) {
                $query = $query->whereNull('scope_id');
            } else {
                $query = $query->where('scope_id', $scopeId);
            }
            return $query->update(['setting_value' => $value]);
        } else {
            return DB::table('settings')->insert($data);
        }
    }

    public function getAll()
    {
        return DB::table('settings')->orderBy('scope')->orderBy('setting_key')->get();
    }
}
