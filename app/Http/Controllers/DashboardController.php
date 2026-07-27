<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $dateToRules = ['nullable', 'date'];

        if ($request->filled('date_from')) {
            $dateToRules[] = 'after_or_equal:date_from';
        }

        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => $dateToRules,
        ]);
        $statuses = ['probing', 'penawaran', 'spk', 'bast', 'tagihan_mitra', 'selesai', 'gagal_wo'];
        $pipelineStatuses = ['probing', 'penawaran', 'spk', 'bast', 'tagihan_mitra', 'selesai'];
        $summary = Project::query()
            ->leftJoin('penawaran', 'penawaran.project_id', '=', 'projects.id')
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('projects.tanggal_probing', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('projects.tanggal_probing', '<=', $date))
            ->selectRaw('projects.status, COUNT(DISTINCT projects.id) as total, SUM(COALESCE(projects.nilai_project_inc_ppn, penawaran.nilai_dpp_ppn_pelanggan, 0)) as nilai')
            ->groupBy('projects.status')
            ->get()
            ->keyBy('status');

        $statusCounts = collect($statuses)
            ->mapWithKeys(fn (string $status) => [$status => (int) ($summary->get($status)?->total ?? 0)])
            ->all();
        $statusValues = collect($statuses)
            ->mapWithKeys(fn (string $status) => [$status => (float) ($summary->get($status)?->nilai ?? 0)])
            ->all();
        $funnel = collect($pipelineStatuses)->map(function (string $status, int $index) use ($pipelineStatuses, $statusCounts): array {
            $reached = collect(array_slice($pipelineStatuses, $index))
                ->sum(fn (string $stage) => $statusCounts[$stage]);
            $base = collect($pipelineStatuses)->sum(fn (string $stage) => $statusCounts[$stage]);

            return [
                'status' => $status,
                'count' => $reached,
                'conversion' => $base > 0 ? round(($reached / $base) * 100, 1) : 0,
            ];
        })->values();

        return Inertia::render('Dashboard', [
            'filters' => [
                'date_from' => $filters['date_from'] ?? null,
                'date_to' => $filters['date_to'] ?? null,
            ],
            'statusCounts' => $statusCounts,
            'statusValues' => $statusValues,
            'funnel' => $funnel,
        ]);
    }
}
