<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_id', 'no_penawaran', 'pic_penawaran',
    'nilai_jasa_pelanggan', 'nilai_material_pelanggan', 'nilai_dpp_pelanggan',
    'nilai_ppn_pelanggan', 'nilai_pph_pelanggan', 'nilai_dpp_ppn_pelanggan',
    'nilai_terbayar_pelanggan', 'nilai_jasa_mitra', 'nilai_material_mitra',
    'nilai_dpp_mitra', 'nilai_ppn_mitra', 'nilai_pph_mitra', 'nilai_dpp_ppn_mitra',
    'nilai_terbayar_mitra', 'margin_dpp', 'margin_ppn', 'margin_persen',
])]
class Penawaran extends Model
{
    protected $table = 'penawaran';

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
