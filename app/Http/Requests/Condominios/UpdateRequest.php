<?php

namespace App\Http\Requests\Condominios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return  Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "nombre" => "required|string|max:255",
            "rut" => "required|string|max:20", // Eliminamos el regex para que acepte cualquier string
            "direccion" => "required|string|max:255",
            "telefono" => "required|string|max:20",
            "email" => "required|email|max:255|unique:users,email",
            "logo" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
            "firma" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
            "fecha_contable_inicial" => "required|date",
            "fondo_reserva" => "nullable|numeric|min:0",
            "datos_bancarios" => "nullable|string",
            "mandato_khipu" => "nullable|string",
        ];
    }
}
