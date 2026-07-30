<?php

namespace App\Modules\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JustificarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'documento' => 'required|string|max:20',
            'fecha_inasistencia' => 'required|date',
            'motivo' => 'required|string|max:1000',
        ];

        if ($this->hasFile('archivo')) {
            $rules['archivo'] = 'nullable|file|mimes:pdf,doc,docx|max:5120';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'documento.required' => 'El documento de identidad es obligatorio.',
            'fecha_inasistencia.required' => 'La fecha de inasistencia es obligatoria.',
            'fecha_inasistencia.date' => 'La fecha debe ser válida.',
            'motivo.required' => 'El motivo de la justificación es obligatorio.',
            'archivo.mimes' => 'El archivo debe ser un PDF o Word (.doc, .docx).',
            'archivo.max' => 'El archivo no debe superar los 5 MB.',
        ];
    }
}
