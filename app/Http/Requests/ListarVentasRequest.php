<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListarVentasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return false;
        // Cambiamos a true para poder tener acceso
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sucursal' => 'required|integer',
            'status' => 'required|integer|in:0,1'
        ];
    }
}
