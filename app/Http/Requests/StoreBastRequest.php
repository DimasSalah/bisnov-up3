<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bast' => ['required', 'array', 'min:1'],
            'bast.*.termin' => ['required', 'string', 'max:30'],
            'bast.*.persentase' => ['required', 'numeric', 'min:0', 'max:100'],
            'bast.*.no_bast' => ['required', 'string', 'max:50'],
            'bast.*.tanggal_bast' => ['required', 'date'],
            'bast.*.no_invoice' => ['nullable', 'string', 'max:50'],
            'bast.*.tanggal_invoice' => ['nullable', 'date'],
            'bast.*.no_faktur_pajak' => ['nullable', 'string', 'max:50'],
            'bast.*.tanggal_faktur_pajak' => ['nullable', 'date'],
            'bast.*.nominal_bayar_pelanggan' => ['required', 'numeric', 'min:0'],
            'bast.*.tanggal_bayar_pelanggan' => ['nullable', 'date', 'required_if:bast.*.status_bayar_pelanggan,lunas'],
            'bast.*.status_bayar_pelanggan' => ['required', 'in:belum_ditagih,sudah_invoice,lunas'],
            'bast.*.keterangan' => ['nullable', 'string'],
            'bast.*.berkas_penawaran' => ['nullable', 'url', 'max:500'],
            'bast.*.berkas_bast_bapp' => ['nullable', 'url', 'max:500'],
            'bast.*.berkas_tagihan' => ['nullable', 'url', 'max:500'],
        ];
    }
}
