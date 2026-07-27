<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'spk_id', 'nama_mitra', 'no_spk', 'tanggal_spk', 'no_parking', 'material', 'jasa',
    'nilai_spk_exc_ppn', 'nilai_spk_inc_ppn', 'status_smartpayment', 'tgl_verifikator',
    'tgl_approval', 'tgl_man_perbendaharaan', 'durasi_lama_hari', 'nilai_terbayar',
    'tanggal_bayar', 'status_nodin', 'tanggal_nodin', 'no_nodin', 'scan_nodin',
    'scan_tagihan_mitra',
])]
class TagihanMitra extends Model
{
    protected $table = 'tagihan_mitra';

    protected function casts(): array
    {
        return [
            'tanggal_spk' => 'date',
            'tgl_verifikator' => 'date',
            'tgl_approval' => 'date',
            'tgl_man_perbendaharaan' => 'date',
            'durasi_lama_hari' => 'integer',
            'material' => 'decimal:2',
            'jasa' => 'decimal:2',
            'nilai_spk_exc_ppn' => 'decimal:2',
            'nilai_spk_inc_ppn' => 'decimal:2',
            'nilai_terbayar' => 'decimal:2',
            'tanggal_bayar' => 'date',
            'tanggal_nodin' => 'date',
        ];
    }

    public function spk(): BelongsTo
    {
        return $this->belongsTo(Spk::class);
    }
}
