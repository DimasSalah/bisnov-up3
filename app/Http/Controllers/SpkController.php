<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpkRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SpkController extends Controller
{
    public function create(Project $project): Response
    {
        abort_unless($project->status === 'penawaran', 422, 'SPK hanya dapat dibuat setelah Penawaran.');

        return Inertia::render('projects/spk/Create', ['project' => $project]);
    }

    public function store(StoreSpkRequest $request, Project $project): RedirectResponse
    {
        abort_unless($project->status === 'penawaran', 422, 'Project tidak dapat dinaikkan ke SPK dari status ini.');

        DB::transaction(function () use ($request, $project): void {
            $project->spk()->create($request->validated());
            $project->update(['status' => 'spk']);
            $project->statusHistory()->create([
                'status_dari' => 'penawaran',
                'status_ke' => 'spk',
                'keterangan' => 'SPK berhasil disimpan.',
            ]);
        });

        return to_route('projects.bast.create', $project)->with('success', 'SPK berhasil disimpan. Tambahkan termin BAST.');
    }
}
