<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'import_source_key', 'tanggal_probing', 'unit_layanan', 'nama_pic_beyond_kwh', 'nama_perusahaan',
    'alamat_perusahaan', 'latitude', 'longitude', 'scope_pekerjaan',
    'potensi_pekerjaan', 'nama_pic_pelanggan', 'nomor_pic_pelanggan',
    'tanggal_inspeksi', 'opsi_selanjutnya', 'status', 'nilai_project_dpp',
    'nilai_project_inc_ppn',
])]
class Project extends Model
{
    protected function casts(): array
    {
        return [
            'tanggal_probing' => 'date',
            'tanggal_inspeksi' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'nilai_project_dpp' => 'decimal:2',
            'nilai_project_inc_ppn' => 'decimal:2',
        ];
    }

    public function probingAttachments(): HasMany
    {
        return $this->hasMany(ProbingAttachment::class);
    }

    public function penawaran(): HasOne
    {
        return $this->hasOne(Penawaran::class);
    }

    public function spk(): HasOne
    {
        return $this->hasOne(Spk::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(StatusHistory::class);
    }

    public function latestStatusHistory(): HasOne
    {
        return $this->hasOne(StatusHistory::class)->latestOfMany('changed_at');
    }
}
