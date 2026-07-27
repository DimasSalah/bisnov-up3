<?php

namespace App\Services;

use App\Models\Project;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class ProjectMetrics
{
    public function value(Project $project): float
    {
        return (float) ($project->nilai_project_inc_ppn
            ?? $project->penawaran?->nilai_dpp_ppn_pelanggan
            ?? 0);
    }

    public function stageStartedAt(Project $project): ?CarbonInterface
    {
        if ($project->import_source_key) {
            $sourceDate = match ($project->status) {
                'spk' => $project->spk?->tanggal_awal_kontrak,
                'bast' => $project->spk?->bast->max('tanggal_bast')
                    ?? $project->spk?->tanggal_awal_kontrak,
                'tagihan_mitra', 'selesai' => $project->spk?->tagihanMitra->max('tgl_verifikator')
                    ?? $project->spk?->bast->max('tanggal_bast')
                    ?? $project->spk?->tanggal_awal_kontrak,
                default => $project->tanggal_probing,
            };

            if ($sourceDate) {
                return Carbon::parse($sourceDate);
            }
        }

        return $project->latestStatusHistory?->changed_at
            ?? $project->updated_at
            ?? $project->created_at;
    }

    public function ageDays(Project $project): int
    {
        $startedAt = $this->stageStartedAt($project);

        return $startedAt ? max(0, $startedAt->startOfDay()->diffInDays(today())) : 0;
    }

    /** @return array<string, mixed> */
    public function card(Project $project): array
    {
        return [
            'id' => $project->id,
            'nama_perusahaan' => $project->nama_perusahaan,
            'unit_layanan' => $project->unit_layanan,
            'status' => $project->status,
            'tanggal_probing' => $project->tanggal_probing?->toDateString(),
            'opsi_selanjutnya' => $project->opsi_selanjutnya,
            'nilai_project' => $this->value($project),
            'umur_tahap_hari' => $this->ageDays($project),
            'pic' => $project->penawaran?->pic_penawaran
                ?? $project->nama_pic_beyond_kwh
                ?? $project->nama_pic_pelanggan,
            'penawaran' => $project->penawaran ? [
                'no_penawaran' => $project->penawaran->no_penawaran,
                'pic_penawaran' => $project->penawaran->pic_penawaran,
            ] : null,
            'spk' => $project->spk ? [
                'no_spk_pelanggan' => $project->spk->no_spk_pelanggan,
                'nama_mitra' => $project->spk->nama_mitra,
            ] : null,
        ];
    }
}
