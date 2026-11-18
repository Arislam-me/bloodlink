<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\District;
use App\Models\Division;
use App\Models\Upazila;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds Bangladesh's 8 divisions, 64 districts and 495 upazilas from the
 * open "bangladesh-geocode" dataset (database/data/*.json), plus the city
 * thanas of Dhaka and Chattogram metropolitan areas which that dataset omits.
 * Idempotent: safe to re-run.
 */
class LocationSeeder extends Seeder
{
    /** Metropolitan thanas (treated as upazila-level units for search). */
    private const CITY_THANAS = [
        'Dhaka' => [
            'Adabor', 'Badda', 'Banani', 'Bangshal', 'Bhashantek', 'Biman Bandar', 'Cantonment', 'Chawkbazar',
            'Dakshinkhan', 'Darus Salam', 'Demra', 'Dhanmondi', 'Gendaria', 'Gulshan', 'Hatirjheel', 'Hazaribagh',
            'Jatrabari', 'Kadamtali', 'Kafrul', 'Kalabagan', 'Kamrangirchar', 'Khilgaon', 'Khilkhet', 'Kotwali',
            'Lalbagh', 'Mirpur Model', 'Mohammadpur', 'Motijheel', 'Mugda', 'New Market', 'Pallabi', 'Paltan',
            'Ramna', 'Rampura', 'Rupnagar', 'Sabujbagh', 'Shah Ali', 'Shahbagh', 'Shahjahanpur', 'Sher-e-Bangla Nagar',
            'Shyampur', 'Sutrapur', 'Tejgaon', 'Tejgaon Industrial', 'Turag', 'Uttara East', 'Uttara West',
            'Uttarkhan', 'Vatara', 'Wari',
        ],
        'Chattogram' => [
            'Akbar Shah', 'Bakalia', 'Bandar', 'Bayazid Bostami', 'Chandgaon', 'Chawkbazar (Ctg)', 'Double Mooring',
            'EPZ', 'Halishahar', 'Khulshi', 'Kotwali (Ctg)', 'Pahartali', 'Panchlaish', 'Patenga', 'Sadarghat',
            'Karnaphuli (City)',
        ],
    ];

    /** A few well-known neighborhoods to make the Area picker useful out of the box. */
    private const SAMPLE_AREAS = [
        'Dhanmondi' => ['Dhanmondi 27', 'Jigatola', 'Kalabagan Road', 'Shankar'],
        'Mirpur Model' => ['Mirpur 1', 'Mirpur 2', 'Mirpur 10', 'Shewrapara'],
        'Mohammadpur' => ['Asad Avenue', 'Shyamoli', 'Tajmahal Road', 'Mohammadi Housing'],
        'Uttara West' => ['Sector 3', 'Sector 7', 'Sector 10', 'Sector 13'],
        'Panchlaish' => ['GEC Circle', 'Nasirabad', 'Muradpur'],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $divisions = $this->load('divisions');
            $districts = $this->load('districts');
            $upazilas = $this->load('upazilas');

            $divisionMap = [];
            foreach ($divisions as $d) {
                $divisionMap[$d['id']] = Division::updateOrCreate(
                    ['name' => $d['name']],
                    ['bn_name' => $d['bn_name'] ?? null, 'latitude' => $d['lat'] ?? null, 'longitude' => $d['lon'] ?? null],
                )->id;
            }

            $districtMap = [];
            foreach ($districts as $d) {
                $districtMap[$d['id']] = District::updateOrCreate(
                    ['division_id' => $divisionMap[$d['division_id']], 'name' => $d['name']],
                    ['bn_name' => $d['bn_name'] ?? null, 'latitude' => $d['lat'] ?? null, 'longitude' => $d['lon'] ?? null],
                )->id;
            }

            foreach ($upazilas as $u) {
                Upazila::updateOrCreate(
                    ['district_id' => $districtMap[$u['district_id']], 'name' => $u['name']],
                    ['bn_name' => $u['bn_name'] ?? null, 'latitude' => $u['lat'] ?? null, 'longitude' => $u['lon'] ?? null],
                );
            }

            foreach (self::CITY_THANAS as $districtName => $thanas) {
                $district = District::where('name', $districtName)->firstOrFail();
                foreach ($thanas as $thana) {
                    Upazila::updateOrCreate(
                        ['district_id' => $district->id, 'name' => $thana],
                        ['is_city_thana' => true],
                    );
                }
            }

            foreach (self::SAMPLE_AREAS as $upazilaName => $areas) {
                $upazila = Upazila::where('name', $upazilaName)->where('is_city_thana', true)->first();
                foreach ($areas as $area) {
                    $upazila && Area::firstOrCreate(['upazila_id' => $upazila->id, 'name' => $area], ['is_approved' => true]);
                }
            }
        });

        cache()->forget('divisions.options');
    }

    /** @return array<int, array<string, string>> */
    private function load(string $name): array
    {
        $json = json_decode(file_get_contents(database_path("data/{$name}.json")), true, flags: JSON_THROW_ON_ERROR);

        // phpMyAdmin export format: [{type: header}, {type: database}, {type: table, data: [...]}]
        foreach ($json as $block) {
            if (($block['type'] ?? null) === 'table') {
                return $block['data'];
            }
        }

        return $json;
    }
}
