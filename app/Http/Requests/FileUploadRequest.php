<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Пожалуйста, выберите файл для загрузки.',
            'file.file' => 'Загруженный файл должен быть действительным файлом.',
            'file.mimes' => 'Файл должен быть формата Excel (xlsx).',
            'file.max' => 'Размер файла не должен превышать 2 МБ.',
        ];
    }
}
