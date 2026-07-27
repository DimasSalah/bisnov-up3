<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function kanbanProject(string $name, string $status = 'bast'): Project
{
    return Project::create([
        'tanggal_probing' => '2026-07-26',
        'unit_layanan' => 'UP3 Jakarta Selatan',
        'nama_perusahaan' => $name,
        'alamat_perusahaan' => 'Jl. Contoh No. 1, Jakarta',
        'scope_pekerjaan' => 'electric_services',
        'nama_pic_pelanggan' => 'Budi',
        'nomor_pic_pelanggan' => '08123456789',
        'opsi_selanjutnya' => 'permintaan_penawaran',
        'status' => $status,
    ]);
}

beforeEach(function () {
    $this->withoutVite();
    $this->actingAs(User::factory()->create());
});

test('dashboard shares the count for every project status', function () {
    kanbanProject('PT Probing', 'probing');
    kanbanProject('PT Selesai', 'selesai');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('statusCounts.probing', 1)
            ->where('statusCounts.selesai', 1)
            ->where('statusCounts.spk', 0)
            ->has('funnel', 6)
            ->where('filters.date_from', null)
            ->where('filters.date_to', null));
});

test('dashboard filters monitoring data by probing date range', function () {
    kanbanProject('PT Dalam Periode', 'probing')->update(['tanggal_probing' => '2026-07-15']);
    kanbanProject('PT Di Luar Periode', 'probing')->update(['tanggal_probing' => '2026-06-15']);

    $this->get(route('dashboard', [
        'date_from' => '2026-07-01',
        'date_to' => '2026-07-31',
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('statusCounts.probing', 1)
            ->where('filters.date_from', '2026-07-01')
            ->where('filters.date_to', '2026-07-31'));
});

test('project table filters projects by offer number', function () {
    $matched = kanbanProject('PT Tersaring');
    $matched->penawaran()->create(['no_penawaran' => 'OFF-TERPILIH', 'pic_penawaran' => 'Rina']);
    $other = kanbanProject('PT Lain');
    $other->penawaran()->create(['no_penawaran' => 'OFF-LAIN', 'pic_penawaran' => 'Doni']);

    $this->get(route('projects.index', ['search' => 'OFF-TERPILIH']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('projects/Index')
            ->where('filters.view', 'table')
            ->has('tableProjects.data', 1)
            ->where('tableProjects.data.0.id', $matched->id));
});

test('project workspace paginates table data and caps kanban columns', function () {
    foreach (range(1, 28) as $number) {
        kanbanProject("PT BAST {$number}");
    }

    $this->get(route('projects.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('tableProjects.data', 25)
            ->where('tableProjects.total', 28));

    $this->get(route('projects.index', ['view' => 'kanban']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('projects', 15)
            ->where('statusSummaries.bast.count', 28)
            ->where('tableProjects', null));
});

test('project workspace filters by unit and probing date range', function () {
    $matched = kanbanProject('PT Unit Terpilih');
    $matched->update(['unit_layanan' => 'UL Gresik', 'tanggal_probing' => '2026-07-15']);
    kanbanProject('PT Unit Lain')->update(['unit_layanan' => 'UL Surabaya', 'tanggal_probing' => '2026-06-10']);

    $this->get(route('projects.index', [
        'unit' => 'UL Gresik',
        'date_from' => '2026-07-01',
        'date_to' => '2026-07-31',
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('tableProjects.data', 1)
            ->where('tableProjects.data.0.id', $matched->id));
});

test('project detail combines its stage data and timeline', function () {
    $project = kanbanProject('PT Detail', 'spk');
    $project->spk()->create([
        'no_spk_pelanggan' => 'SPK-DETAIL',
        'tanggal_awal_kontrak' => '2026-07-01',
        'tanggal_akhir_kontrak' => '2026-08-01',
        'nama_mitra' => 'PT Mitra Detail',
    ])->bast()->create([
        'termin' => 'Termin I',
        'persentase' => 100,
        'no_bast' => 'BAST-DETAIL',
        'tanggal_bast' => '2026-07-10',
        'nominal_bayar_pelanggan' => 100000,
        'status_bayar_pelanggan' => 'belum_ditagih',
    ]);
    $project->statusHistory()->create(['status_ke' => 'spk', 'keterangan' => 'SPK dibuat.']);

    $this->get(route('projects.show', $project))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('projects/Show')
            ->has('project.spk.bast', 1)
            ->has('project.status_history', 1));
});

test('kanban permits a backward correction and records it', function () {
    $project = kanbanProject('PT Koreksi', 'bast');

    $this->patch(route('projects.move', $project), [
        'status_tujuan' => 'spk',
        'position' => 0,
    ])->assertRedirect(route('projects.index'));

    expect($project->refresh()->status)->toBe('spk')
        ->and($project->statusHistory()->latest('id')->first()->keterangan)->toBe('Status dikoreksi dari kanban.');
});

test('kanban rejects a skipped status', function () {
    $project = kanbanProject('PT Tidak Lompat', 'probing');

    $this->patch(route('projects.move', $project), [
        'status_tujuan' => 'spk',
        'position' => 0,
    ])->assertStatus(422);
});
