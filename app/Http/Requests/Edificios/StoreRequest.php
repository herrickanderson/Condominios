<?php

namespace App\Http\Requests\Edificios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        // Puedes agregar lógica para autorizar la solicitud.
        //return true;
        return Auth::check();
    }

    public function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'id_condominio' => 'required|exists:condominios,id_condominio',
        ];
    }
}
