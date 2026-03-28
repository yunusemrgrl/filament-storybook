<?php

namespace App;

enum ComponentSurface: string
{
    case Page = 'page';
    case Navigation = 'navigation';
    case Dashboard = 'dashboard';

    public function label(): string
    {
        return match ($this) {
            self::Navigation => 'Navigation',
            self::Dashboard => 'Dashboard',
            default => 'Page',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $surface): array => [
                $surface->value => $surface->label(),
            ])
            ->all();
    }
}
