<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenawaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_penawaran' => ['required', 'string', 'max:50'],
            'pic_penawaran' => ['required', 'string', 'max:150'],
            'nilai_jasa_pelanggan' => ['required', 'numeric', 'min:0'],
            'nilai_material_pelanggan' => ['required', 'numeric', 'min:0'],
            'nilai_jasa_mitra' => ['required', 'numeric', 'min:0'],
            'nilai_material_mitra' => ['required', 'numeric', 'min:0'],
        ];
    }
}
