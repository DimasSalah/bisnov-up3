<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_spk_pelanggan' => ['required', 'string', 'max:50'],
            'tanggal_awal_kontrak' => ['required', 'date'],
            'tanggal_akhir_kontrak' => ['required', 'date', 'after_or_equal:tanggal_awal_kontrak'],
            'tanggal_pekerjaan' => ['nullable', 'date'],
            'nama_pic_bast' => ['nullable', 'string', 'max:150'],
            'nama_pic_bapp' => ['nullable', 'string', 'max:150'],
            'nama_mitra' => ['required', 'string', 'max:200'],
            'berkas_spk' => ['nullable', 'url', 'max:500'],
        ];
    }
}
