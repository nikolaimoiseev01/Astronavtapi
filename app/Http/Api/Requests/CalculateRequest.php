<?php

namespace App\Http\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $date
 * @property string $time
 * @property int $city_id
 */
class CalculateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'string', 'regex:/\d{4}-\d{2}-\d{2}/'],
            'time' => ['required', 'string', 'regex:/\d{2}:\d{2}/'],
            'city_id' => ['required', 'integer', 'exists:pb_cities,id'],
        ];
    }
}
