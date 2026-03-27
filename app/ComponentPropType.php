<?php

namespace App;

enum ComponentPropType: string
{
    case Text = 'text';
    case Boolean = 'boolean';
    case Select = 'select';
    case File = 'file';
    case Repeater = 'repeater';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Text->value => 'Text',
            self::Boolean->value => 'Boolean',
            self::Select->value => 'Select',
            self::File->value => 'File Upload',
            self::Repeater->value => 'Repeater',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function nestedOptions(): array
    {
        return [
            self::Text->value => 'Text',
            self::Boolean->value => 'Boolean',
            self::Select->value => 'Select',
        ];
    }
}
