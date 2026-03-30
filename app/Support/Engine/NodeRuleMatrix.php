<?php

declare(strict_types=1);

namespace App\Support\Engine;

class NodeRuleMatrix
{
    public const FAMILY_LAYOUT = 'layout';

    public const FAMILY_FORM = 'form';

    public const FAMILY_REPEATER = 'repeater';

    public const FAMILY_TABLE_COLUMN = 'table-column';

    public const FAMILY_TABLE_WIDGET = 'table-widget';

    public const FAMILY_WIDGET = 'widget';

    public const FAMILY_ACTION = 'action';

    public const FAMILY_GENERIC = 'generic';

    /**
     * @return array<int, string>
     */
    public function rootFamilies(): array
    {
        return [
            self::FAMILY_LAYOUT,
            self::FAMILY_FORM,
            self::FAMILY_REPEATER,
            self::FAMILY_WIDGET,
            self::FAMILY_TABLE_WIDGET,
        ];
    }

    public function canonicalType(string $type): string
    {
        return str($type)
            ->after('component-')
            ->value();
    }

    public function familyForType(string $type): string
    {
        $type = $this->canonicalType($type);

        return match (true) {
            $type === 'filament.form.repeater' => self::FAMILY_REPEATER,
            $type === 'filament.widget.table_widget' => self::FAMILY_TABLE_WIDGET,
            str_starts_with($type, 'filament.action.') => self::FAMILY_ACTION,
            str_starts_with($type, 'filament.layout.') => self::FAMILY_LAYOUT,
            str_starts_with($type, 'filament.form.') => self::FAMILY_FORM,
            str_starts_with($type, 'filament.table.') => self::FAMILY_TABLE_COLUMN,
            str_starts_with($type, 'filament.widget.') => self::FAMILY_WIDGET,
            default => self::FAMILY_GENERIC,
        };
    }

    public function acceptsChildren(string $type): bool
    {
        return $this->allowedChildFamilies($type) !== [];
    }

    /**
     * @return array<int, string>
     */
    public function allowedChildFamilies(string $type): array
    {
        return match ($this->familyForType($type)) {
            self::FAMILY_LAYOUT => [
                self::FAMILY_LAYOUT,
                self::FAMILY_FORM,
                self::FAMILY_REPEATER,
                self::FAMILY_WIDGET,
                self::FAMILY_TABLE_WIDGET,
                self::FAMILY_ACTION,
            ],
            self::FAMILY_REPEATER => [
                self::FAMILY_LAYOUT,
                self::FAMILY_FORM,
                self::FAMILY_REPEATER,
                self::FAMILY_ACTION,
            ],
            self::FAMILY_TABLE_WIDGET => [
                self::FAMILY_TABLE_COLUMN,
                self::FAMILY_ACTION,
            ],
            self::FAMILY_ACTION => [
                self::FAMILY_LAYOUT,
                self::FAMILY_FORM,
                self::FAMILY_REPEATER,
            ],
            default => [],
        };
    }

    public function supportsRootType(string $type): bool
    {
        return in_array($this->familyForType($type), $this->rootFamilies(), true);
    }

    public function supportsChildType(?string $parentType, string $childType): bool
    {
        if ($parentType === null) {
            return $this->supportsRootType($childType);
        }

        return in_array(
            $this->familyForType($childType),
            $this->allowedChildFamilies($parentType),
            true,
        );
    }
}
