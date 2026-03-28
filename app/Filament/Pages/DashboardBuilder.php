<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class DashboardBuilder extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Builder';

    protected static ?string $navigationLabel = 'Dashboard Builder';

    protected string $view = 'filament.pages.dashboard-builder';

    public string $paletteSearch = '';

    /**
     * @var array<int, array{key: string, title: string, group: string, description: string, metric: string, trend: string, icon: string}>
     */
    public array $dashboardWidgets = [];

    public ?string $selectedWidgetKey = null;

    public function mount(): void
    {
        $this->dashboardWidgets = [
            $this->findWidget('revenue-overview'),
            $this->findWidget('conversion-funnel'),
        ];

        $this->selectedWidgetKey = $this->dashboardWidgets[0]['key'] ?? null;
    }

    /**
     * @return array<int, array{group: string, items: array<int, array<string, string>>}>
     */
    public function getPaletteGroups(): array
    {
        $search = str($this->paletteSearch)->lower()->trim()->value();

        return collect($this->availableWidgets())
            ->filter(function (array $widget) use ($search): bool {
                if ($search === '') {
                    return true;
                }

                return str_contains(strtolower($widget['title']), $search)
                    || str_contains(strtolower($widget['description']), $search)
                    || str_contains(strtolower($widget['group']), $search);
            })
            ->groupBy('group')
            ->map(fn ($items, string $group): array => [
                'group' => $group,
                'items' => array_values($items->all()),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{key: string, title: string, group: string, description: string, metric: string, trend: string, icon: string}>
     */
    public function getCanvasWidgets(): array
    {
        return $this->dashboardWidgets;
    }

    /**
     * @return array{key: string, title: string, group: string, description: string, metric: string, trend: string, icon: string}|null
     */
    public function getSelectedWidget(): ?array
    {
        foreach ($this->dashboardWidgets as $widget) {
            if ($widget['key'] === $this->selectedWidgetKey) {
                return $widget;
            }
        }

        return null;
    }

    public function addWidget(string $key): void
    {
        $widget = $this->findWidget($key);

        if ($widget === null) {
            return;
        }

        $this->dashboardWidgets[] = $widget;
        $this->selectedWidgetKey = $widget['key'];
    }

    public function selectWidget(string $key): void
    {
        if (! collect($this->dashboardWidgets)->contains(fn (array $widget): bool => $widget['key'] === $key)) {
            return;
        }

        $this->selectedWidgetKey = $key;
    }

    public function removeSelectedWidget(): void
    {
        if ($this->selectedWidgetKey === null) {
            return;
        }

        $this->dashboardWidgets = array_values(array_filter(
            $this->dashboardWidgets,
            fn (array $widget): bool => $widget['key'] !== $this->selectedWidgetKey,
        ));

        $this->selectedWidgetKey = $this->dashboardWidgets[0]['key'] ?? null;
    }

    /**
     * @return array<int, array{key: string, title: string, group: string, description: string, metric: string, trend: string, icon: string}>
     */
    private function availableWidgets(): array
    {
        return [
            [
                'key' => 'revenue-overview',
                'title' => 'Revenue overview',
                'group' => 'Commerce',
                'description' => 'Gross revenue, average order value, and campaign delta.',
                'metric' => '$248k',
                'trend' => '+18.2%',
                'icon' => Heroicon::OutlinedBanknotes,
            ],
            [
                'key' => 'conversion-funnel',
                'title' => 'Conversion funnel',
                'group' => 'Commerce',
                'description' => 'Sessions, add-to-cart, checkout, and completed orders.',
                'metric' => '4.9%',
                'trend' => '+0.6%',
                'icon' => Heroicon::OutlinedChartPie,
            ],
            [
                'key' => 'fulfillment-health',
                'title' => 'Fulfillment health',
                'group' => 'Operations',
                'description' => 'Late shipments, cancellation rate, and warehouse backlog.',
                'metric' => '92%',
                'trend' => '-1.1%',
                'icon' => Heroicon::OutlinedTruck,
            ],
            [
                'key' => 'campaign-attribution',
                'title' => 'Campaign attribution',
                'group' => 'Marketing',
                'description' => 'Top channels, attributed revenue, and blended ROAS.',
                'metric' => '7.2x',
                'trend' => '+0.8x',
                'icon' => Heroicon::OutlinedMegaphone,
            ],
        ];
    }

    /**
     * @return array{key: string, title: string, group: string, description: string, metric: string, trend: string, icon: string}|null
     */
    private function findWidget(string $key): ?array
    {
        foreach ($this->availableWidgets() as $widget) {
            if ($widget['key'] === $key) {
                return $widget;
            }
        }

        return null;
    }
}
