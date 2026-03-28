<?php

namespace App\Http\Controllers\Admin;

use App\ComponentSurface;
use App\Filament\Storybook\Blocks\BlockRegistry;
use App\Filament\Storybook\KnobDefinition;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadPageBuilderAssetRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PageBuilderUploadController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function __invoke(UploadPageBuilderAssetRequest $request): JsonResponse
    {
        $block = BlockRegistry::findByTypeForSurface(
            ComponentSurface::Page,
            $request->string('blockType')->value(),
        );

        $field = $this->findFileField(
            $block?->knobs() ?? [],
            $request->string('fieldName')->value(),
        );

        if (! $field) {
            throw ValidationException::withMessages([
                'fieldName' => 'The selected field does not support uploads.',
            ]);
        }

        $uploadedFile = $request->file('file');

        if ($field->isImageFile()) {
            Validator::validate(['file' => $uploadedFile], [
                'file' => ['image'],
            ]);
        }

        $disk = $field->getFileDisk() ?? 'public';
        $directory = $field->getFileDirectory() ?? 'page-builder/editor-temp';
        $path = $uploadedFile->store($directory, $disk);

        return response()->json([
            'path' => $path,
            'url' => Storage::disk($disk)->url($path),
            'disk' => $disk,
            'meta' => [
                'name' => $uploadedFile->getClientOriginalName(),
                'size' => $uploadedFile->getSize(),
                'mimeType' => $uploadedFile->getMimeType(),
                'image' => $field->isImageFile(),
            ],
        ]);
    }

    /**
     * @param  array<int, KnobDefinition>  $definitions
     */
    private function findFileField(array $definitions, string $name): ?KnobDefinition
    {
        foreach ($definitions as $definition) {
            if ($definition->getName() === $name && $definition->getType() === KnobDefinition::TYPE_FILE) {
                return $definition;
            }

            if ($definition->getType() === KnobDefinition::TYPE_REPEATER) {
                $field = $this->findFileField($definition->getRepeaterSchema(), $name);

                if ($field) {
                    return $field;
                }
            }
        }

        return null;
    }
}
