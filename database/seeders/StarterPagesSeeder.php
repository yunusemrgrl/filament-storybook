<?php

namespace Database\Seeders;

use App\ComponentSurface;
use App\Models\ComponentDefinition;
use App\Models\Page;
use App\PageStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class StarterPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $heroBanner = ComponentDefinition::query()
            ->forSurface(ComponentSurface::Page)
            ->where('handle', 'hero_banner')
            ->firstOrFail();

        $faq = ComponentDefinition::query()
            ->forSurface(ComponentSurface::Page)
            ->where('handle', 'faq')
            ->firstOrFail();

        $heroImagePath = $this->seedHeroImage();

        Page::query()->updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home',
                'status' => PageStatus::Published,
                'blocks' => [
                    $heroBanner->toDatabaseBlock()->makeBlockPayload([
                        'headline' => 'Build storefront pages from reusable definitions',
                        'subheadline' => 'Starter workspace ships with a live editor shell, schema-driven blocks, and direct Blade runtime rendering.',
                        'cta_text' => 'Open the editor',
                        'cta_url' => '/admin/pages/create',
                        'text_align' => 'left',
                        'image' => $heroImagePath,
                        'image_alt' => 'Starter home hero image',
                    ], 'default'),
                    $faq->toDatabaseBlock()->makeBlockPayload([
                        'section_title' => 'Starter workspace FAQ',
                        'intro' => 'Use these seeded blocks to verify the full editor to runtime pipeline.',
                        'items' => [
                            [
                                'question' => 'Where should I start?',
                                'answer' => 'Open Component Definitions to inspect the starter schemas, then create a page from the custom builder shell.',
                            ],
                            [
                                'question' => 'What is already wired?',
                                'answer' => 'Palette, canvas, inspector, normalized payload persistence, preview sync, and public Blade rendering are already connected.',
                            ],
                        ],
                    ], 'default'),
                ],
            ],
        );

        Page::query()->updateOrCreate(
            ['slug' => 'spring-launch'],
            [
                'title' => 'Spring Launch',
                'status' => PageStatus::Draft,
                'blocks' => [
                    $heroBanner->toDatabaseBlock()->makeBlockPayload([
                        'headline' => 'Spring launch draft',
                        'subheadline' => 'This draft page is seeded to let you test edit, publish, and preview flows immediately.',
                        'cta_text' => 'Preview draft',
                        'cta_url' => '/admin/pages',
                        'text_align' => 'center',
                        'image' => $heroImagePath,
                        'image_alt' => 'Spring campaign draft hero',
                    ], 'default'),
                ],
            ],
        );
    }

    private function seedHeroImage(): string
    {
        $targetPath = 'page-builder/hero-banners/starter-home-hero.png';
        $sourcePath = base_path('vendor/livewire/livewire/src/Features/SupportFileUploads/browser_test_image.png');
        $sourceContents = file_get_contents($sourcePath);

        if ($sourceContents === false) {
            throw new \RuntimeException("Unable to read starter hero image from [{$sourcePath}].");
        }

        Storage::disk('public')->put($targetPath, $sourceContents);

        return $targetPath;
    }
}
