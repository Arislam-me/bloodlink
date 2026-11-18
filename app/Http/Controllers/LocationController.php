<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\District;
use App\Models\Upazila;
use Illuminate\Http\JsonResponse;

/**
 * Read-only JSON endpoints powering the dependent location dropdowns.
 * Public reference data only; throttled and cached.
 */
class LocationController extends Controller
{
    public function districts(int $division): JsonResponse
    {
        $data = cache()->remember("loc.districts.$division", 3600, fn () => District::where('division_id', $division)
            ->orderBy('name')->get(['id', 'name', 'latitude', 'longitude'])->toArray());

        return response()->json($data)->setPublic()->setMaxAge(3600);
    }

    public function upazilas(int $district): JsonResponse
    {
        $data = cache()->remember("loc.upazilas.$district", 3600, fn () => Upazila::where('district_id', $district)
            ->orderBy('is_city_thana')->orderBy('name')->get(['id', 'name', 'is_city_thana', 'latitude', 'longitude'])->toArray());

        return response()->json($data)->setPublic()->setMaxAge(3600);
    }

    public function areas(int $upazila): JsonResponse
    {
        $data = Area::where('upazila_id', $upazila)->where('is_approved', true)->orderBy('name')->get(['id', 'name']);

        return response()->json($data)->setPublic()->setMaxAge(600);
    }
}
