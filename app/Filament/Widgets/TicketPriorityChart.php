<?php

namespace App\Filament\Widgets;

use App\Models\Priority;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TicketPriorityChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'ticketPriorityChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Ticket Priority';

    /**
     * Defer loading of the chart
     */
    protected static bool $deferLoading = true;

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $ticketPriorities = Priority::select('id', 'name')->withCount('tickets')->get();
        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Ticket Priority',
                    'data' => $ticketPriorities->pluck('tickets_count')->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $ticketPriorities->pluck('name')->toArray(),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#f59e0b'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => false,
                ],
            ],
        ];
    }
}
