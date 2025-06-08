<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitResumeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Авторизация проверяется в middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'candidate_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{L}\s\-\'\.]+$/u' // Только буквы, пробелы, дефисы, апострофы, точки
            ],
            'candidate_phone' => [
                'required',
                'string',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/', // Телефон с возможными символами
                'min:10',
                'max:20'
            ],
            'candidate_email' => [
                'required',
                'email:rfc,dns',
                'max:255'
            ],
            'position' => [
                'required',
                'string',
                'min:2',
                'max:255'
            ],
            'source' => [
                'nullable',
                'string',
                'max:100',
                'in:API,Website,Referral,LinkedIn,HeadHunter,Direct,Other'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'resume_file' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:10240' // 10MB максимум
            ],
            // Дополнительные поля для интеграций
            'funnel_id' => [
                'nullable',
                'string',
                'max:100'
            ],
            'stage_id' => [
                'nullable',
                'string',
                'max:100'
            ],
            'priority' => [
                'nullable',
                'in:low,normal,high,urgent'
            ],
            'tags' => [
                'nullable',
                'array',
                'max:10'
            ],
            'tags.*' => [
                'string',
                'max:50'
            ],
            'custom_fields' => [
                'nullable',
                'array'
            ]
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'candidate_name.required' => 'Имя кандидата обязательно для заполнения',
            'candidate_name.min' => 'Имя кандидата должно содержать минимум 2 символа',
            'candidate_name.max' => 'Имя кандидата не должно превышать 255 символов',
            'candidate_name.regex' => 'Имя кандидата может содержать только буквы, пробелы, дефисы и точки',

            'candidate_phone.required' => 'Телефон кандидата обязателен для заполнения',
            'candidate_phone.regex' => 'Неверный формат телефона',
            'candidate_phone.min' => 'Телефон должен содержать минимум 10 символов',
            'candidate_phone.max' => 'Телефон не должен превышать 20 символов',

            'candidate_email.required' => 'Email кандидата обязателен для заполнения',
            'candidate_email.email' => 'Неверный формат email адреса',
            'candidate_email.max' => 'Email не должен превышать 255 символов',

            'position.required' => 'Должность обязательна для заполнения',
            'position.min' => 'Должность должна содержать минимум 2 символа',
            'position.max' => 'Должность не должна превышать 255 символов',

            'source.in' => 'Недопустимый источник кандидата',

            'notes.max' => 'Заметки не должны превышать 2000 символов',

            'resume_file.file' => 'Загруженный файл поврежден',
            'resume_file.mimes' => 'Поддерживаются только файлы PDF, DOC, DOCX',
            'resume_file.max' => 'Размер файла не должен превышать 10MB',

            'priority.in' => 'Недопустимый приоритет. Доступны: low, normal, high, urgent',

            'tags.array' => 'Теги должны быть массивом',
            'tags.max' => 'Максимально можно указать 10 тегов',
            'tags.*.string' => 'Каждый тег должен быть строкой',
            'tags.*.max' => 'Тег не должен превышать 50 символов',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'candidate_name' => 'имя кандидата',
            'candidate_phone' => 'телефон кандидата',
            'candidate_email' => 'email кандидата',
            'position' => 'должность',
            'source' => 'источник',
            'notes' => 'заметки',
            'resume_file' => 'файл резюме',
            'funnel_id' => 'ID воронки',
            'stage_id' => 'ID этапа',
            'priority' => 'приоритет',
            'tags' => 'теги',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Очищаем и нормализуем данные
        if ($this->has('candidate_name')) {
            $this->merge([
                'candidate_name' => trim($this->candidate_name)
            ]);
        }

        if ($this->has('candidate_phone')) {
            // Очищаем телефон от лишних символов, оставляя только цифры и +
            $phone = preg_replace('/[^\d\+]/', '', $this->candidate_phone);
            $this->merge([
                'candidate_phone' => $phone
            ]);
        }

        if ($this->has('candidate_email')) {
            $this->merge([
                'candidate_email' => strtolower(trim($this->candidate_email))
            ]);
        }

        if ($this->has('position')) {
            $this->merge([
                'position' => trim($this->position)
            ]);
        }

        if ($this->has('notes')) {
            $this->merge([
                'notes' => trim($this->notes)
            ]);
        }

        // Устанавливаем источник по умолчанию если не указан
        if (!$this->has('source') || empty($this->source)) {
            $this->merge([
                'source' => 'API'
            ]);
        }
    }
}
