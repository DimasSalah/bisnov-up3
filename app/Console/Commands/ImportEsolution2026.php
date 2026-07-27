<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JsonException;

class ImportEsolution2026 extends Command
{
    protected $signature = 'esolution:import-2026
        {path=storage/app/imports/esolution-2026.json : File JSON hasil ekstraksi workbook}
        {--dry-run : Tampilkan hasil tanpa menyimpan data}';

    protected $description = 'Impor data Recap E-Solution 2026 secara idempoten.';

    public function handle(): int
    {
        $path = base_path($this->argument('path'));

        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        try {
            $projects = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->error('File impor bukan JSON yang valid.');

            return self::FAILURE;
        }

        if (! is_array($projects)) {
            $this->error('Format JSON impor tidak valid.');

            return self::FAILURE;
        }

        $created = 0;
        $skipped = 0;

        foreach ($projects as $item) {
            if (! isset($item['source_key'], $item['project'])) {
                $this->warn('Satu baris proyek dilewati karena formatnya tidak lengkap.');

                continue;
            }

            $project = Project::query()->where('import_source_key', $item['source_key'])->first();

            if ($project) {
                $skipped++;

                if (! $this->option('dry-run')) {
                    DB::transaction(function () use ($project, $item): void {
                        $project->update([
                            'nilai_project_dpp' => $item['project']['nilai_project_dpp'] ?? null,
                            'nilai_project_inc_ppn' => $item['project']['nilai_project_inc_ppn'] ?? null,
                        ]);
                        $this->importRelations($project, $item);
                    });
                }

                continue;
            }

            if ($this->option('dry-run')) {
                $created++;

                continue;
            }

            DB::transaction(function () use ($item): void {
                $project = Project::create(array_merge($item['project'], [
                    'import_source_key' => $item['source_key'],
                ]));

                $project->statusHistory()->create([
                    'status_dari' => null,
                    'status_ke' => $project->status,
                    'keterangan' => 'Data historis diimpor dari Recap E-Solution 2026.',
                ]);

                $this->importRelations($project, $item);
            });

            $created++;
        }

        $this->info("Impor selesai. Dibuat: {$created}; sudah ada/dilewati: {$skipped}.");

        return self::SUCCESS;
    }

    private function importRelations(Project $project, array $item): void
    {
        if (($item['penawaran'] ?? null) && ! $project->penawaran()->exists()) {
            $project->penawaran()->create($item['penawaran']);
        }

        if (! ($item['spk'] ?? null)) {
            return;
        }

        $spk = $project->spk()->first() ?? $project->spk()->create($item['spk']);

        if (($item['bast'] ?? []) && ! $spk->bast()->exists()) {
            $spk->bast()->createMany($item['bast']);
        }

        if (($item['tagihan_mitra'] ?? []) && ! $spk->tagihanMitra()->exists()) {
            $spk->tagihanMitra()->createMany($item['tagihan_mitra']);
        }
    }
}
