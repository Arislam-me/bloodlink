<?php

namespace App\Filament\Widgets;

use App\Models\EmergencyRequest;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RegistrationsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Registrations & requests (last 6 months)';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));

        $count = fn (string $model, Carbon $m) => $model::whereBetween('created_at', [$m, $m->copy()->endOfMonth()])->count();

        return [
            'datasets' => [
                ['label' => 'New users', 'data' => $months->map(fn ($m) => $count(User::class, $m))->all(), 'borderColor' => '#a51531', 'backgroundColor' => 'rgba(165,21,49,.15)', 'fill' => true],
                ['label' => 'Emergency requests', 'data' => $months->map(fn ($m) => $count(EmergencyRequest::class, $m))->all(), 'borderColor' => '#0f172a', 'backgroundColor' => 'rgba(15,23,42,.08)', 'fill' => true],
            ],
            'labels' => $months->map(fn ($m) => $m->format('M Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
