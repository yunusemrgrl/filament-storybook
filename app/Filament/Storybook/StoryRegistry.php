<?php

namespace App\Filament\Storybook;

use App\Filament\Storybook\Blocks\BlockRegistry;
use Illuminate\Support\Facades\File;
use ReflectionClass;

/**
 * StoryRegistry
 *
 * app/Filament/Storybook/Stories/ klasörünü tarar,
 * AbstractStory'yi extend eden tüm class'ları bulur,
 * bunları gruplandırılmış şekilde döner.
 *
 * NEDEN REGISTRY?
 * Yeni bir story class'ı oluşturduğunda başka hiçbir yere
 * kaydetmene gerek kalmaz. Registry dosyayı bulur, yükler,
 * listeye ekler. Storybook'taki auto-discovery mantığının aynısı.
 *
 * ÇALIŞMA MANTIĞI:
 * 1. Stories/ klasöründeki tüm .php dosyalarını bul
 * 2. Her birini require et (class'ı PHP'ye tanıt)
 * 3. AbstractStory'yi extend edip etmediğini kontrol et
 * 4. Ediyorsa instance oluştur ve registry'e ekle
 * 5. group'a göre gruplandır
 */
class StoryRegistry
{
    /**
     * Discover edilmiş story instance'larının cache'i.
     * İlk çağrıda dolar, sonraki çağrılarda cache'den döner.
     *
     * @var AbstractStory[]|null
     */
    private static ?array $stories = null;

    /**
     * Stories/ klasörünün path'i.
     * Değiştirmek istersen buradan güncelle.
     */
    private static string $storiesPath = '';

    /**
     * Tüm story'leri döner (flat liste).
     *
     * @return AbstractStory[]
     */
    public static function all(): array
    {
        if (static::$stories === null) {
            static::discover();
        }

        return static::$stories;
    }

    /**
     * Story'leri group'a göre gruplandırılmış döner.
     * Navigation builder bu metodu kullanır.
     *
     * Dönen format:
     * [
     *   'Forms'   => [TextInputStory, SelectStory, ...],
     *   'Tables'  => [TextColumnStory, ...],
     *   'Widgets' => [StatsWidgetStory, ...],
     * ]
     *
     * @return array<string, AbstractStory[]>
     */
    public static function grouped(): array
    {
        $groups = [];

        foreach (static::all() as $story) {
            $group = $story->group ?: 'Other';

            if (! isset($groups[$group])) {
                $groups[$group] = [];
            }

            $groups[$group][] = $story;
        }

        // Grup adına göre alfabetik sırala
        ksort($groups);

        return $groups;
    }

    /**
     * Slug'a göre tek bir story döner.
     * StoryPage bu metodu kullanarak hangi story'yi
     * render edeceğini bulur.
     *
     * @param  string  $slug  Örn: "forms-text-input"
     */
    public static function findBySlug(string $slug): ?AbstractStory
    {
        foreach (static::all() as $story) {
            if ($story->getSlug() === $slug) {
                return $story;
            }
        }

        return null;
    }

    /**
     * Stories/ klasörünü tarayıp tüm story class'larını yükler.
     * Sonucu static::$stories cache'ine yazar.
     */
    private static function discover(): void
    {
        static::$stories = [];

        $path = static::getStoriesPath();

        if (! File::exists($path)) {
            // Klasör henüz yok - boş döner, hata vermez
            // Aşama 2'de ilk story yazılınca klasör oluşacak
            return;
        }

        // Klasördeki tüm .php dosyalarını recursive tara
        // (Stories/Forms/TextInputStory.php gibi alt klasörler de taranır)
        $files = File::allFiles($path);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            // Dosyayı PHP'ye tanıt
            // require_once: aynı dosyayı iki kez yüklememek için
            require_once $file->getPathname();
        }

        // Şu an yüklü olan tüm class'lar arasından story olanları bul
        foreach (get_declared_classes() as $class) {
            if (! static::isStoryClass($class)) {
                continue;
            }

            $story = new $class;

            // title veya group boşsa atla - eksik tanımlı story
            if (empty($story->title) || empty($story->group)) {
                continue;
            }

            static::$stories[] = $story;
        }

        // Aynı grup içinde title'a göre alfabetik sırala
        usort(static::$stories, function (AbstractStory $a, AbstractStory $b) {
            // Önce gruba göre sırala, sonra title'a göre
            $groupCompare = strcmp($a->group, $b->group);
            if ($groupCompare !== 0) {
                return $groupCompare;
            }

            return strcmp($a->title, $b->title);
        });
    }

    /**
     * Verilen class'ın bir story class'ı olup olmadığını kontrol eder.
     *
     * Kontroller:
     * - AbstractStory'yi extend etmeli
     * - Abstract olmamalı (AbstractStory kendisi de AbstractStory'yi extend eder)
     * - App namespace'inde olmalı (vendor class'larını dışarıda tut)
     */
    private static function isStoryClass(string $class): bool
    {
        // Kendi namespace'imizde değilse atla
        if (! str_starts_with($class, 'App\\')) {
            return false;
        }

        try {
            $reflection = new ReflectionClass($class);
        } catch (\Throwable) {
            return false;
        }

        // Abstract class'ları atla (AbstractStory'nin kendisi dahil)
        if ($reflection->isAbstract()) {
            return false;
        }

        // AbstractStory'yi extend etmeli
        return $reflection->isSubclassOf(AbstractStory::class);
    }

    /**
     * Stories/ klasörünün tam path'ini döner.
     */
    private static function getStoriesPath(): string
    {
        if (static::$storiesPath) {
            return static::$storiesPath;
        }

        return app_path('Filament/Storybook/Stories');
    }

    /**
     * Cache'i temizler. Test yazarken veya hot-reload senaryolarında kullanılır.
     */
    public static function flush(): void
    {
        static::$stories = null;
        BlockRegistry::flush();
    }
}
