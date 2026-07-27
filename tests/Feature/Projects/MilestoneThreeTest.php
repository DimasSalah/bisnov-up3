<?php

use App\Models\Project;
use App\Models\Spk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function projectAtStatus(string $status): Project
{
    return Project::create([
        'tanggal_probing' => '2026-07-26',
        'unit_layanan' => 'UP3 Jakarta Selatan',
        'nama_perusahaan' => 'PT Contoh Energi',
        'alamat_perusahaan' => 'Jl. Contoh No. 1, Jakarta',
        'scope_pekerjaan' => 'electric_services',
        'nama_pic_pelanggan' => 'Budi',
        'nomor_pic_pelanggan' => '08123456789',
        'opsi_selanjutnya' => 'permintaan_penawaran',
        'status' => $status,
    ]);
}

function spkFor(Project $project): Spk
{
    return $project->spk()->create([
        'no_spk_pelanggan' => 'SPK/001/VII/2026',
        'tanggal_awal_kontrak' => '2026-07-01',
        'tanggal_akhir_kontrak' => '2026-08-31',
        'nama_mitra' => 'PT Mitra Energi',
    ]);
}

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can create an spk and project advances from penawaran', function () {
    $project = projectAtStatus('penawaran');

    $response = $this->post(route('projects.spk.store', $project), [
        'no_spk_pelanggan' => 'SPK/001/VII/2026',
        'tanggal_awal_kontrak' => '2026-07-01',
        'tanggal_akhir_kontrak' => '2026-08-31',
        'tanggal_pekerjaan' => '2026-07-15',
        'nama_pic_bast' => 'Budi',
        'nama_pic_bapp' => 'Siti',
        'nama_mitra' => 'PT Mitra Energi',
        'berkas_spk' => 'https://drive.google.com/file/d/spk',
    ]);

    $response->assertRedirect(route('projects.bast.create', $project));
    expect($project->refresh()->status)->toBe('spk')
        ->and($project->spk->nama_mitra)->toBe('PT Mitra Energi')
        ->and($project->statusHistory)->toHaveCount(1);
});

test('admin can save multiple bast terms and project advances to bast', function () {
    $project = projectAtStatus('spk');
    $spk = spkFor($project);

    $response = $this->post(route('projects.bast.store', $project), [
        'bast' => [
            [
                'termin' => 'Termin I', 'persentase' => 50, 'no_bast' => 'BAST-001',
                'tanggal_bast' => '2026-07-16', 'nominal_bayar_pelanggan' => 500000,
                'status_bayar_pelanggan' => 'lunas', 'tanggal_bayar_pelanggan' => '2026-07-20',
            ],
            [
                'termin' => 'Termin II', 'persentase' => 50, 'no_bast' => 'BAST-002',
                'tanggal_bast' => '2026-08-16', 'nominal_bayar_pelanggan' => 500000,
                'status_bayar_pelanggan' => 'lunas', 'tanggal_bayar_pelanggan' => '2026-08-20',
            ],
        ],
    ]);

    $response->assertRedirect(route('projects.tagihan-mitra.create', $project));
    expect($project->refresh()->status)->toBe('bast')
        ->and($spk->bast)->toHaveCount(2);
});

test('project is completed when all customer and partner bills are paid', function () {
    $project = projectAtStatus('spk');
    $spk = spkFor($project);

    $this->post(route('projects.bast.store', $project), [
        'bast' => [[
            'termin' => 'Termin I', 'persentase' => 100, 'no_bast' => 'BAST-001',
            'tanggal_bast' => '2026-07-16', 'nominal_bayar_pelanggan' => 1000000,
            'status_bayar_pelanggan' => 'lunas', 'tanggal_bayar_pelanggan' => '2026-07-20',
        ]],
    ]);

    $response = $this->post(route('projects.tagihan-mitra.store', $project), [
        'tagihan_mitra' => [[
            'no_parking' => 'PARK-001', 'material' => 200000, 'jasa' => 300000,
            'nilai_spk_exc_ppn' => 500000, 'nilai_spk_inc_ppn' => 555000,
            'nilai_terbayar' => 555000, 'tanggal_bayar' => '2026-07-21',
        ]],
    ]);

    $response->assertRedirect(route('projects.index'));
    expect($project->refresh()->status)->toBe('selesai')
        ->and($spk->tagihanMitra)->toHaveCount(1)
        ->and($project->statusHistory()->latest('id')->first()->status_ke)->toBe('selesai');
});

test('tagihan mitra cannot skip the bast stage', function () {
    $project = projectAtStatus('spk');
    spkFor($project);

    $this->get(route('projects.tagihan-mitra.create', $project))->assertStatus(422);
});
