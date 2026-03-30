<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveNavigationBuilderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'placement' => ['required', 'string', 'max:255'],
            'channel' => ['required', 'string', 'max:255'],
            'nodes' => ['required', 'array'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $nodes = $this->input('nodes', []);

                if (! is_array($nodes)) {
                    return;
                }

                foreach ($nodes as $index => $node) {
                    $this->validateNode($validator, $node, "nodes.{$index}");
                }
            },
        ];
    }

    private function validateNode(Validator $validator, mixed $node, string $path): void
    {
        if (! is_array($node)) {
            $validator->errors()->add($path, 'Each navigation node must be an object payload.');

            return;
        }

        $type = $this->stringValue($node['type'] ?? null);

        if ($this->stringValue($node['id'] ?? null) === null) {
            $validator->errors()->add("{$path}.id", 'Each navigation node must define an id.');
        }

        if ($this->stringValue($node['label'] ?? null) === null) {
            $validator->errors()->add("{$path}.label", 'Each navigation node must define a label.');
        }

        if (! in_array($type, ['link', 'dropdown', 'mega'], true)) {
            $validator->errors()->add("{$path}.type", 'Navigation nodes must be link, dropdown, or mega.');
        }

        if ($this->hasNonStringValue($node, 'href')) {
            $validator->errors()->add("{$path}.href", 'Navigation href values must be strings.');
        }

        if ($this->hasNonStringValue($node, 'icon')) {
            $validator->errors()->add("{$path}.icon", 'Navigation icon values must be strings.');
        }

        if ($this->hasNonStringValue($node, 'group')) {
            $validator->errors()->add("{$path}.group", 'Navigation group values must be strings.');
        }

        if ($this->hasNonStringValue($node, 'description')) {
            $validator->errors()->add("{$path}.description", 'Navigation description values must be strings.');
        }

        if (isset($node['target']) && ! in_array($node['target'], ['same-tab', 'new-tab'], true)) {
            $validator->errors()->add("{$path}.target", 'Navigation target values must be same-tab or new-tab.');
        }

        if (isset($node['visibility']) && ! in_array($node['visibility'], ['always', 'authenticated', 'role'], true)) {
            $validator->errors()->add("{$path}.visibility", 'Navigation visibility values must be always, authenticated, or role.');
        }

        if (isset($node['columns']) && ! is_int($node['columns']) && ! ctype_digit((string) $node['columns'])) {
            $validator->errors()->add("{$path}.columns", 'Mega menu column counts must be integers.');
        }

        $children = $node['children'] ?? [];

        if (! is_array($children)) {
            $validator->errors()->add("{$path}.children", 'Navigation children must be an array.');

            return;
        }

        foreach ($children as $index => $child) {
            $this->validateNode($validator, $child, "{$path}.children.{$index}");
        }
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function hasNonStringValue(array $node, string $key): bool
    {
        if (! array_key_exists($key, $node) || $node[$key] === null) {
            return false;
        }

        return ! is_string($node[$key]);
    }

    private function stringValue(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
