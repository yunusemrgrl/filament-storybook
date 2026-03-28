<?php

namespace App\Http\Requests;

use App\PageStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'blocks' => ['array'],
            'blocks.*.id' => ['required', 'string'],
            'blocks.*.type' => ['required', 'string'],
            'blocks.*.source' => ['nullable', 'string'],
            'blocks.*.variant' => ['nullable', 'string'],
            'blocks.*.data' => ['array'],
        ];
    }
}
