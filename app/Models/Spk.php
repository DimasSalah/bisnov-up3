<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'project_id', 'no_spk_pelanggan', 'tanggal_awal_kontrak', 'tanggal_akhir_kontrak',
    'tanggal_pekerjaan', 'nama_pic_bast', 'nama_pic_bapp', 'nama_mitra', 'berkas_spk',
])]
class Spk extends Model
{
    protected $table = 'spk';

    protected function casts(): array
    {
        return [
            'tanggal_awal_kontrak' => 'date',
            'tanggal_akhir_kontrak' => 'date',
            'tanggal_pekerjaan' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function bast(): HasMany
    {
        return $this->hasMany(Bast::class);
    }

    public function tagihanMitra(): HasMany
    {
        return $this->hasMany(TagihanMitra::class);
    }
}
