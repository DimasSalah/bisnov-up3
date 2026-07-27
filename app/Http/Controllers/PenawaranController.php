<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenawaranRequest;
use App\Models\Project;
use App\Services\PenawaranCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PenawaranController extends Controller
{
    public function create(Project $project): Response
    {
        abort_unless($project->status === 'probing' && $project->opsi_selanjutnya === 'permintaan_penawaran', 422, 'Project tidak dapat dibuatkan Penawaran pada status ini.');

        return Inertia::render('projects/penawaran/Create', [
            'project' => $project,
        ]);
    }

    public function store(StorePenawaranRequest $request, Project $project, PenawaranCalculator $calculator): RedirectResponse
    {
        abort_unless($project->status === 'probing' && $project->opsi_selanjutnya === 'permintaan_penawaran', 422, 'Project tidak dapat dinaikkan ke Penawaran dari status ini.');

        DB::transaction(function () use ($request, $project, $calculator): void {
            $data = $request->validated();
            $calculated = $calculator->calculate(
                (float) $data['nilai_jasa_pelanggan'],
                (float) $data['nilai_material_pelanggan'],
                (float) $data['nilai_jasa_mitra'],
                (float) $data['nilai_material_mitra'],
            );

            $project->penawaran()->create(array_merge(
                [
                    'no_penawaran' => $data['no_penawaran'],
                    'pic_penawaran' => $data['pic_penawaran'],
                ],
                $calculated,
            ));

            $project->update(['status' => 'penawaran']);
            $project->statusHistory()->create([
                'status_dari' => 'probing',
                'status_ke' => 'penawaran',
                'keterangan' => 'Penawaran berhasil disimpan.',
            ]);
        });

        return to_route('projects.index')->with('success', 'Penawaran berhasil disimpan.');
    }
}
