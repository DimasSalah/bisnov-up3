<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagihanMitraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tagihan_mitra' => ['required', 'array', 'min:1'],
            'tagihan_mitra.*.no_parking' => ['nullable', 'string', 'max:50'],
            'tagihan_mitra.*.material' => ['required', 'numeric', 'min:0'],
            'tagihan_mitra.*.jasa' => ['required', 'numeric', 'min:0'],
            'tagihan_mitra.*.nilai_spk_exc_ppn' => ['required', 'numeric', 'min:0'],
            'tagihan_mitra.*.nilai_spk_inc_ppn' => ['required', 'numeric', 'min:0'],
            'tagihan_mitra.*.status_smartpayment' => ['nullable', 'string', 'max:50'],
            'tagihan_mitra.*.tgl_verifikator' => ['nullable', 'date'],
            'tagihan_mitra.*.tgl_approval' => ['nullable', 'date'],
            'tagihan_mitra.*.tgl_man_perbendaharaan' => ['nullable', 'date'],
            'tagihan_mitra.*.durasi_lama_hari' => ['nullable', 'integer', 'min:0'],
            'tagihan_mitra.*.nilai_terbayar' => ['required', 'numeric', 'min:0'],
            'tagihan_mitra.*.tanggal_bayar' => ['nullable', 'date'],
            'tagihan_mitra.*.status_nodin' => ['nullable', 'string', 'max:50'],
            'tagihan_mitra.*.tanggal_nodin' => ['nullable', 'date'],
            'tagihan_mitra.*.no_nodin' => ['nullable', 'string', 'max:50'],
            'tagihan_mitra.*.scan_nodin' => ['nullable', 'url', 'max:500'],
            'tagihan_mitra.*.scan_tagihan_mitra' => ['nullable', 'url', 'max:500'],
        ];
    }
}
