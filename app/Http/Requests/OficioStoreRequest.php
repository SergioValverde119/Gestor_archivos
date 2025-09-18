<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OficioStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'folio_oficio' => 'required|string|max:255|unique:oficios,folio_oficio',
            'folio_interno' => 'nullable|string|max:255|unique:oficios,folio_interno',
            'remitente' => 'nullable|string|max:255',
            'asunto' => 'nullable|string',
            'situacion' => 'nullable|string',
            'fecha_recepcion' => 'nullable|date',
            'fecha_limite' => 'nullable|date',
            'prioridad_id' => 'nullable|exists:prioridades,id',
            'area_id' => 'nullable|exists:areas,id',
            'asignado_a_user_id' => 'nullable|exists:users,id',
            'status' => 'nullable|string',
            'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ];
    }
    
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'folio_oficio.unique' => 'El folio de oficio ingresado ya existe. Por favor, ingrese uno diferente.',
            'folio_interno.unique' => 'El folio interno ingresado ya existe. Por favor, ingrese uno diferente.',
        ];
    }
}