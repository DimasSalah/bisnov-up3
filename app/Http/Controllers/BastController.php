<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBastRequest;
use App\Models\Project;
use App\Services\ProjectCompletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BastController extends Controller
{
    public function create(Project $project): Response
    {
        abort_unless(in_array($project->status, ['spk', 'bast'], true), 422, 'BAST hanya dapat dibuat setelah SPK.');
        $spk = $project->spk()->with('bast')->firstOrFail();

        return Inertia::render('projects/bast/Create', ['project' => $project, 'spk' => $spk]);
    }

    public function store(StoreBastRequest $request, Project $project, ProjectCompletionService $completion): RedirectResponse
    {
        abort_unless(in_array($project->status, ['spk', 'bast'], true), 422, 'Project tidak dapat ditambahkan BAST dari status ini.');

        DB::transaction(function () use ($request, $project): void {
            $spk = $project->spk()->firstOrFail();
            $spk->bast()->createMany($request->validated('bast'));

            if ($project->status === 'spk') {
                $project->update(['status' => 'bast']);
                $project->statusHistory()->create([
                    'status_dari' => 'spk',
                    'status_ke' => 'bast',
                    'keterangan' => 'BAST berhasil disimpan.',
                ]);
            }
        });

        $completion->completeIfPaid($project->fresh('spk'));

        return to_route('projects.tagihan-mitra.create', $project)->with('success', 'Termin BAST berhasil disimpan.');
    }
}
