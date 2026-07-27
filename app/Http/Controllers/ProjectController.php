<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Penawaran;
use App\Models\Project;
use App\Models\Spk;
use App\Services\ProjectMetrics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request, ProjectMetrics $metrics): Response
    {
        $statuses = ['probing', 'penawaran', 'spk', 'bast', 'tagihan_mitra', 'selesai', 'gagal_wo'];
        $filters = $request->validate([
            'status' => ['nullable', 'in:probing,penawaran,spk,bast,tagihan_mitra,selesai,gagal_wo'],
            'mitra' => ['nullable', 'string', 'max:200'],
            'marketing' => ['nullable', 'string', 'max:150'],
            'unit' => ['nullable', 'string', 'max:150'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'search' => ['nullable', 'string', 'max:200'],
            'view' => ['nullable', 'in:table,kanban'],
            'sort' => ['nullable', 'in:nama_perusahaan,status,tanggal_probing,nilai_project,umur_tahap'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $view = $filters['view'] ?? 'table';
        $sort = $filters['sort'] ?? 'umur_tahap';
        $direction = $filters['direction'] ?? 'desc';
        $relations = [
            'penawaran:id,project_id,no_penawaran,pic_penawaran,nilai_dpp_ppn_pelanggan',
            'spk:id,project_id,no_spk_pelanggan,nama_mitra,tanggal_awal_kontrak',
            'spk.bast:id,spk_id,tanggal_bast',
            'spk.tagihanMitra:id,spk_id,tgl_verifikator',
            'latestStatusHistory',
        ];

        $baseQuery = Project::query()
            ->leftJoin('penawaran as penawaran_sort', 'penawaran_sort.project_id', '=', 'projects.id')
            ->select('projects.*')
            ->with($relations)
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['mitra'] ?? null, fn ($query, string $mitra) => $query->whereHas('spk', fn ($spk) => $spk->where('nama_mitra', $mitra)))
            ->when($filters['marketing'] ?? null, fn ($query, string $marketing) => $query->whereHas('penawaran', fn ($penawaran) => $penawaran->where('pic_penawaran', $marketing)))
            ->when($filters['unit'] ?? null, fn ($query, string $unit) => $query->where('projects.unit_layanan', $unit))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('projects.tanggal_probing', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('projects.tanggal_probing', '<=', $date))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($projects) use ($search): void {
                    $projects->where('projects.nama_perusahaan', 'like', "%{$search}%")
                        ->orWhereHas('penawaran', fn ($penawaran) => $penawaran->where('no_penawaran', 'like', "%{$search}%"));
                });
            });

        $summaries = (clone $baseQuery)
            ->setEagerLoads([])
            ->select([])
            ->selectRaw('projects.status, COUNT(DISTINCT projects.id) as total, SUM(COALESCE(projects.nilai_project_inc_ppn, penawaran_sort.nilai_dpp_ppn_pelanggan, 0)) as nilai')
            ->groupBy('projects.status')
            ->get()
            ->keyBy('status');

        $kanbanProjects = collect();
        if ($view === 'kanban') {
            $kanbanProjects = collect($statuses)->flatMap(
                fn (string $status) => (clone $baseQuery)
                    ->where('projects.status', $status)
                    ->get()
                    ->sortByDesc(fn (Project $project) => $metrics->ageDays($project))
                    ->take(15)
            );
        }

        /** @var LengthAwarePaginator|null $tableProjects */
        $tableProjects = null;
        if ($view === 'table') {
            $tableQuery = clone $baseQuery;

            if ($sort === 'nilai_project') {
                $tableQuery->orderByRaw("COALESCE(projects.nilai_project_inc_ppn, penawaran_sort.nilai_dpp_ppn_pelanggan, 0) {$direction}");
            } elseif ($sort !== 'umur_tahap') {
                $tableQuery->orderBy("projects.{$sort}", $direction);
            }

            if ($sort === 'umur_tahap') {
                $page = LengthAwarePaginator::resolveCurrentPage();
                $sorted = $tableQuery->get()
                    ->map(fn (Project $project) => $metrics->card($project))
                    ->sort(function (array $left, array $right) use ($direction): int {
                        $leftClosed = in_array($left['status'], ['selesai', 'gagal_wo'], true);
                        $rightClosed = in_array($right['status'], ['selesai', 'gagal_wo'], true);

                        if ($leftClosed !== $rightClosed) {
                            return $leftClosed <=> $rightClosed;
                        }

                        return $direction === 'desc'
                            ? $right['umur_tahap_hari'] <=> $left['umur_tahap_hari']
                            : $left['umur_tahap_hari'] <=> $right['umur_tahap_hari'];
                    })
                    ->values();
                $tableProjects = new LengthAwarePaginator(
                    $sorted->forPage($page, 25)->values(),
                    $sorted->count(),
                    25,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()],
                );
            } else {
                $tableProjects = $tableQuery
                    ->paginate(25)
                    ->withQueryString()
                    ->through(fn (Project $project) => $metrics->card($project));
            }
        }

        return Inertia::render('projects/Index', [
            'projects' => $kanbanProjects
                ->map(fn (Project $project) => $metrics->card($project))
                ->values(),
            'tableProjects' => $tableProjects,
            'filters' => array_merge($filters, [
                'view' => $view,
                'sort' => $sort,
                'direction' => $direction,
            ]),
            'statusSummaries' => collect($statuses)->mapWithKeys(fn (string $status) => [
                $status => [
                    'count' => (int) ($summaries->get($status)?->total ?? 0),
                    'value' => (float) ($summaries->get($status)?->nilai ?? 0),
                ],
            ]),
            'mitras' => Spk::query()->orderBy('nama_mitra')->pluck('nama_mitra')
                ->filter(fn (?string $value) => $value && $value !== '-' && mb_strlen($value) <= 80 && preg_match('/[A-Za-z]/', $value))
                ->unique(fn (string $value) => mb_strtolower($value))
                ->values(),
            'marketings' => Penawaran::query()->orderBy('pic_penawaran')->pluck('pic_penawaran')
                ->filter(fn (?string $value) => $value && preg_match('/[A-Za-z]/', $value))
                ->unique(fn (string $value) => mb_strtolower($value))
                ->values(),
            'units' => Project::query()->distinct()->orderBy('unit_layanan')->pluck('unit_layanan')->values(),
        ]);
    }

    public function show(Project $project): Response
    {
        $project->load([
            'probingAttachments',
            'penawaran',
            'spk.bast',
            'spk.tagihanMitra',
            'statusHistory' => fn ($history) => $history->orderBy('changed_at'),
        ]);

        return Inertia::render('projects/Show', ['project' => $project]);
    }

    public function create(): Response
    {
        return Inertia::render('projects/Create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = DB::transaction(function () use ($request): Project {
            $data = $request->safe()->except(['dokumentasi_probing', 'evidence_inspeksi']);
            $data['status'] = $data['opsi_selanjutnya'] === 'sudah_punya_vendor_lain'
                ? 'gagal_wo'
                : 'probing';

            $project = Project::create($data);

            foreach (['dokumentasi_probing' => 'dokumentasi_probing', 'evidence_inspeksi' => 'evidence_inspeksi'] as $field => $type) {
                foreach ($request->file($field, []) as $file) {
                    $project->probingAttachments()->create([
                        'tipe' => $type,
                        'file_path' => $file->store('probing', 'public'),
                    ]);
                }
            }

            $project->statusHistory()->create([
                'status_dari' => null,
                'status_ke' => $project->status,
                'keterangan' => 'Project dibuat dari form Probing.',
            ]);

            return $project;
        });

        if ($project->status === 'probing' && $project->opsi_selanjutnya === 'permintaan_penawaran') {
            return to_route('projects.penawaran.create', $project);
        }

        return to_route('projects.index')->with('success', 'Project Probing berhasil disimpan.');
    }
}
