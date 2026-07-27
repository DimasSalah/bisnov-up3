<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'spk_id', 'termin', 'persentase', 'no_bast', 'tanggal_bast', 'no_invoice',
    'tanggal_invoice', 'no_faktur_pajak', 'tanggal_faktur_pajak',
    'nominal_bayar_pelanggan', 'tanggal_bayar_pelanggan', 'status_bayar_pelanggan',
    'keterangan', 'berkas_penawaran', 'berkas_bast_bapp', 'berkas_tagihan',
])]
class Bast extends Model
{
    protected $table = 'bast';

    protected function casts(): array
    {
        return [
            'persentase' => 'decimal:2',
            'tanggal_bast' => 'date',
            'tanggal_invoice' => 'date',
            'tanggal_faktur_pajak' => 'date',
            'nominal_bayar_pelanggan' => 'decimal:2',
            'tanggal_bayar_pelanggan' => 'date',
        ];
    }

    public function spk(): BelongsTo
    {
        return $this->belongsTo(Spk::class);
    }
}
