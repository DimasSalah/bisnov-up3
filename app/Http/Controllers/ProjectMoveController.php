<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectMoveController extends Controller
{
    /** @var array<string, int> */
    private const STATUS_ORDER = [
        'probing' => 0,
        'penawaran' => 1,
        'spk' => 2,
        'bast' => 3,
        'tagihan_mitra' => 4,
        'selesai' => 5,
    ];

    public function __invoke(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'status_tujuan' => ['required', 'in:probing,penawaran,spk,bast,tagihan_mitra,selesai,gagal_wo'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);
        $target = $data['status_tujuan'];

        if ($target === $project->status) {
            return back();
        }

        if ($target === 'gagal_wo') {
            abort_unless(in_array($project->status, ['probing', 'penawaran'], true), 422, 'Gagal WO hanya dapat dipilih pada tahap Probing atau Penawaran.');
        } else {
            abort_unless(isset(self::STATUS_ORDER[$project->status]) && isset(self::STATUS_ORDER[$target]), 422, 'Transisi status tidak valid.');
            abort_if($target === 'selesai', 422, 'Status Selesai hanya ditetapkan otomatis setelah semua tagihan lunas.');
            abort_if(self::STATUS_ORDER[$target] > self::STATUS_ORDER[$project->status] + 1, 422, 'Project tidak dapat melompati tahap.');
            abort_if(self::STATUS_ORDER[$target] === self::STATUS_ORDER[$project->status] + 1, 422, 'Naikkan status melalui form tahap berikutnya agar data wajib lengkap.');
        }

        DB::transaction(function () use ($project, $target): void {
            $statusDari = $project->status;
            $project->update(['status' => $target]);
            $project->statusHistory()->create([
                'status_dari' => $statusDari,
                'status_ke' => $target,
                'keterangan' => 'Status dikoreksi dari kanban.',
            ]);
        });

        return to_route('projects.index')->with('success', 'Status project berhasil diperbarui.');
    }
}
