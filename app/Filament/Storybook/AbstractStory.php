<?php

namespace App\Filament\Storybook;

/**
 * AbstractStory
 *
 * Her story class'ının miras alması gereken temel sınıf.
 * Storybook'taki bir ".stories.ts" dosyasının PHP karşılığı.
 *
 * KULLANIM:
 * class TextInputStory extends AbstractStory
 * {
 *     public string $title = 'TextInput';
 *     public string $group = 'Forms';
 *
 *     public function variants(): array { ... }
 * }
 *
 * NEDEN ABSTRACT?
 * variants() metodu abstract tanımlandı. Bu sayede alt sınıf
 * bu metodu yazmak zorunda - yazmayan class PHP hata verir.
 * Bu bir "sözleşme": her story en az bir variant içermeli.
 */
abstract class AbstractStory
{
    /**
     * Sidebar'da ve sayfa başlığında görünecek isim.
     * Örn: 'TextInput', 'Select', 'Stats Overview'
     */
    public string $title = '';

    /**
     * Hangi navigation grubuna ait olduğu.
     * Sidebar'da bu grubun altında görünür.
     * Örn: 'Forms', 'Tables', 'Infolists', 'Widgets', 'Layout'
     */
    public string $group = '';

    /**
     * Sidebar'daki ikon (Heroicon adı).
     * Varsayılan olarak genel bir ikon kullan,
     * alt sınıf override edebilir.
     */
    public string $icon = 'heroicon-o-square-2-stack';

    /**
     * Bu component ne işe yarar, ne zaman kullanılır?
     * StoryPage'de başlığın altında gösterilir.
     */
    public string $description = '';

    /**
     * URL'de kullanılacak benzersiz slug.
     * Otomatik üretilir: "Forms / TextInput" → "forms-text-input"
     * Alt sınıf override edebilir.
     */
    public ?string $slug = null;

    /**
     * Variant'ları döner.
     *
     * Variant = aynı component'in farklı bir hali.
     * Array key = variant adı (tab label olarak kullanılır)
     * Array value = Filament component instance'ı veya config array'i
     *
     * ÖRNEK (Aşama 2'de dolacak):
     * return [
     *     'default'     => TextInput::make('name'),
     *     'disabled'    => TextInput::make('name')->disabled(),
     *     'with_prefix' => TextInput::make('url')->prefix('https://'),
     * ];
     *
     * Şimdilik boş array döndürüyoruz - Aşama 2'de render motoru yazılınca
     * alt sınıflar bu metodu dolduracak.
     */
    abstract public function variants(): array;

    /**
     * Variant anahtarlarını normalize eder.
     *
     * Düz liste dönen story'lerde:
     * ['default', 'disabled']
     *
     * Key/value dönen story'lerde:
     * ['default' => TextInput::make('name')]
     *
     * Her iki durumda da view katmanı için okunacak variant key listesini üretir.
     *
     * @return array<int, string>
     */
    public function getVariantKeys(): array
    {
        $variants = $this->variants();

        if (array_is_list($variants)) {
            return array_values($variants);
        }

        return array_keys($variants);
    }

    public function hasVariant(string $variant): bool
    {
        return in_array($variant, $this->getVariantKeys(), true);
    }

    public function getDefaultVariantKey(): ?string
    {
        return $this->getVariantKeys()[0] ?? null;
    }

    /**
     * Story'nin benzersiz URL slug'ını döner.
     * "Forms / TextInput" → "forms-text-input"
     *
     * Otomatik üretim: group + title'dan türetilir.
     * Alt sınıf $slug property'sini set ederse onu kullanır.
     */
    public function getSlug(): string
    {
        if ($this->slug !== null) {
            return $this->slug;
        }

        $group = strtolower(str_replace(' ', '-', $this->group));
        $title = strtolower(str_replace(' ', '-', $this->title));

        return "{$group}-{$title}";
    }

    /**
     * Variant isimlerini okunabilir label'a çevirir.
     * "with_prefix" → "With Prefix"
     * "disabled"    → "Disabled"
     */
    public function getVariantLabel(string $key): string
    {
        return ucwords(str_replace('_', ' ', $key));
    }

    /**
     * Bu story hangi render tipini kullanır?
     * Alt sınıflar override eder: 'form', 'table', 'infolist', 'widget'
     * Render motoru (Aşama 2) bu değere göre doğru stratejiyi seçer.
     */
    public function getRenderType(): string
    {
        return 'generic';
    }

    public function getUsageSnippet(): ?string
    {
        return null;
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public function anatomy(): array
    {
        return [];
    }

    /**
     * @return array<int, array{
     *     title: string,
     *     description: string,
     *     code?: string|null,
     *     points?: array<int, string>
     * }>
     */
    public function documentationSections(): array
    {
        return [];
    }

    public function getExternalDocsUrl(): ?string
    {
        return null;
    }
}
