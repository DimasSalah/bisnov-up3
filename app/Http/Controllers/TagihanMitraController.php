<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagihanMitraRequest;
use App\Models\Project;
use App\Services\ProjectCompletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TagihanMitraController extends Controller
{
    public function create(Project $project): Response
    {
        abort_unless(in_array($project->status, ['bast', 'tagihan_mitra'], true), 422, 'Tagihan Mitra hanya dapat dibuat setelah BAST.');
        $spk = $project->spk()->with('tagihanMitra')->firstOrFail();

        return Inertia::render('projects/tagihan-mitra/Create', ['project' => $project, 'spk' => $spk]);
    }

    public function store(StoreTagihanMitraRequest $request, Project $project, ProjectCompletionService $completion): RedirectResponse
    {
        abort_unless(in_array($project->status, ['bast', 'tagihan_mitra'], true), 422, 'Project tidak dapat ditambahkan Tagihan Mitra dari status ini.');

        DB::transaction(function () use ($request, $project): void {
            $spk = $project->spk()->firstOrFail();
            $tagihanMitra = collect($request->validated('tagihan_mitra'))
                ->map(fn (array $tagihan) => array_merge($tagihan, [
                    'nama_mitra' => $spk->nama_mitra,
                    'no_spk' => $spk->no_spk_pelanggan,
                    'tanggal_spk' => $spk->tanggal_awal_kontrak,
                ]))
                ->all();
            $spk->tagihanMitra()->createMany($tagihanMitra);

            if ($project->status === 'bast') {
                $project->update(['status' => 'tagihan_mitra']);
                $project->statusHistory()->create([
                    'status_dari' => 'bast',
                    'status_ke' => 'tagihan_mitra',
                    'keterangan' => 'Tagihan Mitra berhasil disimpan.',
                ]);
            }
        });

        $completed = $completion->completeIfPaid($project->fresh('spk'));

        return to_route('projects.index')->with('success', $completed
            ? 'Seluruh tagihan telah lunas. Project selesai.'
            : 'Tagihan Mitra berhasil disimpan.');
    }
}
