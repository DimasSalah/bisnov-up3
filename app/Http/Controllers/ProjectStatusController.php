<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectStatusController extends Controller
{
    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'status_tujuan' => ['required', 'in:gagal_wo'],
            'keterangan' => ['nullable', 'string'],
        ]);

        abort_unless(in_array($project->status, ['probing', 'penawaran'], true), 422, 'Status project tidak dapat diubah ke Gagal WO dari tahap ini.');

        DB::transaction(function () use ($project, $data): void {
            $statusDari = $project->status;
            $project->update(['status' => $data['status_tujuan']]);
            $project->statusHistory()->create([
                'status_dari' => $statusDari,
                'status_ke' => $data['status_tujuan'],
                'keterangan' => $data['keterangan'] ?? 'Project ditutup sebagai Gagal WO.',
            ]);
        });

        return to_route('projects.index')->with('success', 'Status project berhasil diperbarui.');
    }
}
