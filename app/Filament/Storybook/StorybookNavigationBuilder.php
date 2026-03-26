<?php

namespace App\Filament\Storybook;

use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

/**
 * StorybookNavigationBuilder
 *
 * StoryRegistry'den gelen gruplandırılmış story listesini alır,
 * Filament'ın NavigationGroup ve NavigationItem objelerine dönüştürür.
 *
 * NEDEN AYRI SINIF?
 * Navigation oluşturma mantığını PanelProvider'dan ayırmak için.
 * PanelProvider büyümesin; navigation detayları burada yaşasın.
 *
 * ÇALIŞMA MANTIĞI:
 * StoryRegistry::grouped() çağrılır:
 * [
 *   'Forms'   => [TextInputStory, SelectStory],
 *   'Widgets' => [StatsWidgetStory],
 * ]
 *
 * Bu array NavigationGroup dizisine dönüşür:
 * Forms (heroicon-o-pencil-square)
 *   ├── TextInput   → /storybook/story/forms-text-input
 *   └── Select      → /storybook/story/forms-select
 * Widgets (heroicon-o-squares-2x2)
 *   └── Stats Overview → /storybook/story/widgets-stats-overview
 */
class StorybookNavigationBuilder
{
    /**
     * Her navigation grubunun ikonu.
     * Group adı bu array'de yoksa varsayılan ikon kullanılır.
     */
    private static array $groupIcons = [
        'Forms' => 'heroicon-o-pencil-square',
        'Tables' => 'heroicon-o-table-cells',
        'Infolists' => 'heroicon-o-information-circle',
        'Widgets' => 'heroicon-o-squares-2x2',
        'Layout' => 'heroicon-o-rectangle-stack',
    ];

    private static string $defaultGroupIcon = 'heroicon-o-folder';

    /**
     * PanelProvider'ın navigation() callback'inden çağrılır.
     *
     * @param  NavigationBuilder  $builder  Filament'ın builder instance'ı
     * @return NavigationBuilder Gruplarla doldurulmuş builder
     */
    public static function build(NavigationBuilder $builder): NavigationBuilder
    {
        $groups = StoryRegistry::grouped();

        if (empty($groups)) {
            // Henüz hiç story yok - boş panel açılır
            // Aşama 2'de ilk story yazılınca sidebar dolar
            return $builder->groups([]);
        }

        $navigationGroups = [];

        foreach ($groups as $groupName => $stories) {
            $items = [];

            foreach ($stories as $story) {
                $items[] = NavigationItem::make($story->title)
                    // Sidebar'da görünecek ikon
                    ->icon($story->icon)
                    // Tıklanınca gidilecek URL
                    // StoryPage bu URL'i dinler ve slug'a göre story'yi yükler
                    ->url(
                        route('filament.storybook.pages.story-page', [
                            'slug' => $story->getSlug(),
                        ])
                    )
                    // Aktif sayfa sidebar'da highlight edilir
                    ->isActiveWhen(
                        fn (): bool => request()->query('slug') === $story->getSlug()
                    );
            }

            $navigationGroups[] = NavigationGroup::make($groupName)
                ->icon(
                    static::$groupIcons[$groupName] ?? static::$defaultGroupIcon
                )
                ->items($items);
        }

        return $builder->groups($navigationGroups);
    }
}
