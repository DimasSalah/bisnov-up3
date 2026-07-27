<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('admin can create a probing project and upload field documentation', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    $response = $this->post(route('projects.store'), [
        'tanggal_probing' => '2026-07-26',
        'unit_layanan' => 'UP3 Jakarta Selatan',
        'nama_perusahaan' => 'PT Contoh Energi',
        'alamat_perusahaan' => 'Jl. Contoh No. 1, Jakarta',
        'scope_pekerjaan' => 'electric_services',
        'nama_pic_pelanggan' => 'Budi',
        'nomor_pic_pelanggan' => '08123456789',
        'opsi_selanjutnya' => 'permintaan_penawaran',
        'dokumentasi_probing' => [UploadedFile::fake()->image('probing.jpg')],
        'evidence_inspeksi' => [UploadedFile::fake()->image('inspeksi.jpg')],
    ]);

    $project = Project::query()->firstOrFail();

    $response->assertRedirect(route('projects.penawaran.create', $project));
    expect($project->status)->toBe('probing')
        ->and($project->probingAttachments)->toHaveCount(2)
        ->and($project->statusHistory)->toHaveCount(1);
    Storage::disk('public')->assertExists($project->probingAttachments->first()->file_path);
});

test('admin can save an offer and backend calculates financial values', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::create([
        'tanggal_probing' => '2026-07-26',
        'unit_layanan' => 'UP3 Jakarta Selatan',
        'nama_perusahaan' => 'PT Contoh Energi',
        'alamat_perusahaan' => 'Jl. Contoh No. 1, Jakarta',
        'scope_pekerjaan' => 'electric_services',
        'nama_pic_pelanggan' => 'Budi',
        'nomor_pic_pelanggan' => '08123456789',
        'status' => 'probing',
        'opsi_selanjutnya' => 'permintaan_penawaran',
    ]);

    $response = $this->post(route('projects.penawaran.store', $project), [
        'no_penawaran' => 'OFF-001',
        'pic_penawaran' => 'Siti',
        'nilai_jasa_pelanggan' => 100000,
        'nilai_material_pelanggan' => 50000,
        'nilai_jasa_mitra' => 60000,
        'nilai_material_mitra' => 20000,
    ]);

    $project->refresh();
    $penawaran = $project->penawaran;

    $response->assertRedirect(route('projects.index'));
    expect($project->status)->toBe('penawaran')
        ->and($penawaran->nilai_dpp_pelanggan)->toEqual('150000.00')
        ->and($penawaran->nilai_ppn_pelanggan)->toEqual('16500.00')
        ->and($penawaran->nilai_pph_pelanggan)->toEqual('2000.00')
        ->and($penawaran->nilai_terbayar_pelanggan)->toEqual('164500.00')
        ->and($penawaran->nilai_dpp_mitra)->toEqual('80000.00')
        ->and($penawaran->nilai_pph_mitra)->toEqual('1200.00')
        ->and($penawaran->nilai_terbayar_mitra)->toEqual('87600.00')
        ->and($penawaran->margin_dpp)->toEqual('70000.00')
        ->and($project->statusHistory)->toHaveCount(1);
});

test('a project with another vendor is closed as gagal wo', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('projects.store'), [
        'tanggal_probing' => '2026-07-26',
        'unit_layanan' => 'UP3 Jakarta Selatan',
        'nama_perusahaan' => 'PT Vendor Lain',
        'alamat_perusahaan' => 'Jl. Contoh No. 2, Jakarta',
        'scope_pekerjaan' => 'ev_ecosystem',
        'nama_pic_pelanggan' => 'Andi',
        'nomor_pic_pelanggan' => '08987654321',
        'opsi_selanjutnya' => 'sudah_punya_vendor_lain',
    ]);

    expect(Project::query()->firstOrFail()->status)->toBe('gagal_wo');
});
