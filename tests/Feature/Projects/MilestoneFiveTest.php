<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can complete the full e-solution workflow', function () {
    $this->post(route('projects.store'), [
        'tanggal_probing' => '2026-07-01',
        'unit_layanan' => 'UP3 Jakarta Selatan',
        'nama_perusahaan' => 'PT Energi Nusantara',
        'alamat_perusahaan' => 'Jl. Gatot Subroto No. 10, Jakarta',
        'scope_pekerjaan' => 'electric_services',
        'nama_pic_pelanggan' => 'Andi',
        'nomor_pic_pelanggan' => '081234567890',
        'opsi_selanjutnya' => 'permintaan_penawaran',
    ])->assertRedirect();

    $project = Project::firstOrFail();

    $this->post(route('projects.penawaran.store', $project), [
        'no_penawaran' => 'ESOL/001/VII/2026',
        'pic_penawaran' => 'Sari',
        'nilai_jasa_pelanggan' => 1000000,
        'nilai_material_pelanggan' => 500000,
        'nilai_jasa_mitra' => 700000,
        'nilai_material_mitra' => 300000,
    ])->assertRedirect(route('projects.index'));

    $this->post(route('projects.spk.store', $project), [
        'no_spk_pelanggan' => 'SPK/001/VII/2026',
        'tanggal_awal_kontrak' => '2026-07-05',
        'tanggal_akhir_kontrak' => '2026-08-05',
        'tanggal_pekerjaan' => '2026-07-10',
        'nama_mitra' => 'PT Mitra PLN',
        'berkas_spk' => 'https://drive.google.com/file/d/spk-001',
    ])->assertRedirect(route('projects.bast.create', $project));

    $this->post(route('projects.bast.store', $project), [
        'bast' => [[
            'termin' => 'Termin I',
            'persentase' => 100,
            'no_bast' => 'BAST/001/VII/2026',
            'tanggal_bast' => '2026-07-15',
            'nominal_bayar_pelanggan' => 1645000,
            'status_bayar_pelanggan' => 'lunas',
            'tanggal_bayar_pelanggan' => '2026-07-20',
            'berkas_bast_bapp' => 'https://drive.google.com/file/d/bast-001',
        ]],
    ])->assertRedirect(route('projects.tagihan-mitra.create', $project));

    $this->post(route('projects.tagihan-mitra.store', $project), [
        'tagihan_mitra' => [[
            'no_parking' => 'PARK-001',
            'material' => 300000,
            'jasa' => 700000,
            'nilai_spk_exc_ppn' => 1000000,
            'nilai_spk_inc_ppn' => 1110000,
            'status_smartpayment' => 'Paid',
            'tgl_verifikator' => '2026-07-16',
            'tgl_approval' => '2026-07-17',
            'tgl_man_perbendaharaan' => '2026-07-18',
            'durasi_lama_hari' => 4,
            'nilai_terbayar' => 1096000,
            'tanggal_bayar' => '2026-07-21',
            'status_nodin' => 'Terbit',
            'tanggal_nodin' => '2026-07-19',
            'no_nodin' => 'ND/001/VII/2026',
            'scan_nodin' => 'https://drive.google.com/file/d/nodin-001',
            'scan_tagihan_mitra' => 'https://drive.google.com/file/d/tagihan-mitra-001',
        ]],
    ])->assertRedirect(route('projects.index'));

    $project->refresh()->load('penawaran', 'spk.bast', 'spk.tagihanMitra', 'statusHistory');

    expect($project->status)->toBe('selesai')
        ->and($project->penawaran->nilai_pph_pelanggan)->toEqual('20000.00')
        ->and($project->penawaran->nilai_pph_mitra)->toEqual('14000.00')
        ->and($project->spk->bast)->toHaveCount(1)
        ->and($project->spk->tagihanMitra)->toHaveCount(1)
        ->and($project->statusHistory->pluck('status_ke')->all())
        ->toBe(['probing', 'penawaran', 'spk', 'bast', 'tagihan_mitra', 'selesai']);
});

test('forms reject invalid contract dates, drive links, and paid BAST without a payment date', function () {
    $project = Project::create([
        'tanggal_probing' => '2026-07-01',
        'unit_layanan' => 'UP3 Jakarta Selatan',
        'nama_perusahaan' => 'PT Energi Nusantara',
        'alamat_perusahaan' => 'Jl. Gatot Subroto No. 10, Jakarta',
        'scope_pekerjaan' => 'electric_services',
        'nama_pic_pelanggan' => 'Andi',
        'nomor_pic_pelanggan' => '081234567890',
        'opsi_selanjutnya' => 'permintaan_penawaran',
        'status' => 'penawaran',
    ]);

    $this->post(route('projects.spk.store', $project), [
        'no_spk_pelanggan' => 'SPK/INVALID',
        'tanggal_awal_kontrak' => '2026-08-01',
        'tanggal_akhir_kontrak' => '2026-07-31',
        'nama_mitra' => 'PT Mitra PLN',
        'berkas_spk' => 'bukan-url',
    ])->assertInvalid(['tanggal_akhir_kontrak', 'berkas_spk']);

    $project->update(['status' => 'spk']);
    $project->spk()->create([
        'no_spk_pelanggan' => 'SPK/002/VII/2026',
        'tanggal_awal_kontrak' => '2026-07-01',
        'tanggal_akhir_kontrak' => '2026-08-01',
        'nama_mitra' => 'PT Mitra PLN',
    ]);

    $this->post(route('projects.bast.store', $project), [
        'bast' => [[
            'termin' => 'Termin I',
            'persentase' => 100,
            'no_bast' => 'BAST/INVALID',
            'tanggal_bast' => '2026-07-15',
            'nominal_bayar_pelanggan' => 1000000,
            'status_bayar_pelanggan' => 'lunas',
            'berkas_tagihan' => 'bukan-url',
        ]],
    ])->assertInvalid([
        'bast.0.tanggal_bayar_pelanggan',
        'bast.0.berkas_tagihan',
    ]);
});
