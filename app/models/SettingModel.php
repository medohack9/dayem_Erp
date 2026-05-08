<?php

namespace App\Models;

use App\Core\Model;

class SettingModel extends Model
{
    public function getAll(): array
    {
        return $this->findAll('system_settings');
    }

    public function getByKey(string $key): ?array
    {
        return $this->findOne('system_settings', 'setting_key = :key', ['key' => $key]);
    }

    public function getByGroup(string $group): array
    {
        return $this->findAll('system_settings', 'setting_group = :group', ['group' => $group]);
    }

    public function updateSetting(string $key, string $value): void
    {
        $this->update('system_settings', ['setting_value' => $value], 'setting_key = :key', ['key' => $key]);
    }

    public function createSetting(string $key, string $value, string $group = 'general'): string
    {
        return $this->insert('system_settings', [
            'setting_key' => $key,
            'setting_value' => $value,
            'setting_group' => $group,
        ]);
    }
}