<?php

namespace App\Filament\Widgets;

use App\Models\BloodGroup;
use Filament\Widgets\ChartWidget;

class DonorsByBloodGroupChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Listed donors by blood group';

    protected function getData(): array
    {
        $groups = BloodGroup::orderBy('sort_order')
            ->withCount(['donorProfiles' => fn ($q) => $q->where('is_listed', true)])->get();

        return [
            'datasets' => [[
                'label' => 'Donors',
                'data' => $groups->pluck('donor_profiles_count')->all(),
                'backgroundColor' => ['#a51531', '#e23c55', '#1e3a8a', '#3b82f6', '#6d28d9', '#a78bfa', '#065f46', '#34d399'],
            ]],
            'labels' => $groups->pluck('code')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
