<?php

namespace App\Support\PageBuilder;

class DashboardWidgetRegistry
{
    /**
     * @return array<int, array<string, string>>
     */
    public function all(): array
    {
        return [
            [
                'key' => 'revenue-overview',
                'title' => 'Revenue overview',
                'group' => 'Commerce',
                'description' => 'Gross revenue, average order value, and campaign delta.',
                'metric' => '$248k',
                'trend' => '+18.2%',
            ],
            [
                'key' => 'conversion-funnel',
                'title' => 'Conversion funnel',
                'group' => 'Commerce',
                'description' => 'Sessions, add-to-cart, checkout, and completed orders.',
                'metric' => '4.9%',
                'trend' => '+0.6%',
            ],
            [
                'key' => 'fulfillment-health',
                'title' => 'Fulfillment health',
                'group' => 'Operations',
                'description' => 'Late shipments, cancellation rate, and warehouse backlog.',
                'metric' => '92%',
                'trend' => '-1.1%',
            ],
            [
                'key' => 'campaign-attribution',
                'title' => 'Campaign attribution',
                'group' => 'Marketing',
                'description' => 'Top channels, attributed revenue, and blended ROAS.',
                'metric' => '7.2x',
                'trend' => '+0.8x',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function defaultCanvas(): array
    {
        return array_slice($this->all(), 0, 2);
    }
}
