<?php

namespace Database\Seeders;

use App\Models\BloodGroup;
use Illuminate\Database\Seeder;

class BloodGroupSeeder extends Seeder
{
    public function run(): void
    {
        foreach (BloodGroup::CODES as $i => $code) {
            BloodGroup::updateOrCreate(['code' => $code], ['sort_order' => $i + 1]);
        }
        cache()->forget('blood_groups.options');
    }
}
