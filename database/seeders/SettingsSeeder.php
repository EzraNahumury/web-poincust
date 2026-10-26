<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => config('points.keys.rupiah_per_point'), 'value' => (string) config('points.rupiah_per_point'), 'type' => 'integer'],
            ['key' => config('points.keys.redeem_value'), 'value' => (string) config('points.redeem_value'), 'type' => 'integer'],
        ];

        foreach ($defaults as $row) {
            Setting::firstOrCreate(['key' => $row['key']], $row);
        }

        Setting::flushCache();
    }
}
