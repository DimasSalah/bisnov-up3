<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_probing' => ['required', 'date'],
            'unit_layanan' => ['required', 'string', 'max:150'],
            'nama_pic_beyond_kwh' => ['nullable', 'string', 'max:150'],
            'nama_perusahaan' => ['required', 'string', 'max:200'],
            'alamat_perusahaan' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'scope_pekerjaan' => ['required', 'in:electric_services,ev_ecosystem'],
            'potensi_pekerjaan' => ['nullable', 'string', 'max:150'],
            'nama_pic_pelanggan' => ['required', 'string', 'max:150'],
            'nomor_pic_pelanggan' => ['required', 'string', 'max:30'],
            'tanggal_inspeksi' => ['nullable', 'date'],
            'opsi_selanjutnya' => ['required', 'in:permintaan_penawaran,butuh_survey_ulang,masih_mempertimbangkan,sudah_punya_vendor_lain'],
            'dokumentasi_probing' => ['nullable', 'array'],
            'dokumentasi_probing.*' => ['image', 'max:10240'],
            'evidence_inspeksi' => ['nullable', 'array'],
            'evidence_inspeksi.*' => ['image', 'max:10240'],
        ];
    }
}
