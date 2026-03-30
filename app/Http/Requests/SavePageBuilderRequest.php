<?php

namespace App\Http\Requests;

use App\ComponentSurface;
use App\PageStatus;
use App\Support\PageBuilder\EditorPayloadValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SavePageBuilderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->ignore($this->route('page')),
            ],
            'status' => ['required', Rule::enum(PageStatus::class)],
            'nodes' => ['array'],
            'nodes.*.id' => ['required', 'string'],
            'nodes.*.type' => ['required', 'string'],
            'nodes.*.source' => ['nullable', Rule::in(['definition', 'system'])],
            'nodes.*.variant' => ['nullable', 'string'],
            'nodes.*.props' => ['required', 'array'],
            'nodes.*.children' => ['array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('slug'))) {
            $this->merge([
                'slug' => Str::slug($this->string('slug')->value()),
            ]);
        }
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $payloadValidator = app(EditorPayloadValidator::class);

                foreach ($payloadValidator->errorsFor(ComponentSurface::Page, $this->input('nodes', [])) as $field => $messages) {
                    foreach ($messages as $message) {
                        $validator->errors()->add($field, $message);
                    }
                }
            },
        ];
    }
}
