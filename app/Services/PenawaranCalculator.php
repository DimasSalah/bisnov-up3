<?php

namespace App\Services;

class PenawaranCalculator
{
    /** @return array<string, float> */
    public function calculate(float $jasaPelanggan, float $materialPelanggan, float $jasaMitra, float $materialMitra): array
    {
        $pelanggan = $this->calculateSide($jasaPelanggan, $materialPelanggan);
        $mitra = $this->calculateSide($jasaMitra, $materialMitra);

        return [
            'nilai_jasa_pelanggan' => $pelanggan['jasa'],
            'nilai_material_pelanggan' => $pelanggan['material'],
            'nilai_dpp_pelanggan' => $pelanggan['dpp'],
            'nilai_ppn_pelanggan' => $pelanggan['ppn'],
            'nilai_pph_pelanggan' => $pelanggan['pph'],
            'nilai_dpp_ppn_pelanggan' => $pelanggan['dpp_ppn'],
            'nilai_terbayar_pelanggan' => $pelanggan['terbayar'],
            'nilai_jasa_mitra' => $mitra['jasa'],
            'nilai_material_mitra' => $mitra['material'],
            'nilai_dpp_mitra' => $mitra['dpp'],
            'nilai_ppn_mitra' => $mitra['ppn'],
            'nilai_pph_mitra' => $mitra['pph'],
            'nilai_dpp_ppn_mitra' => $mitra['dpp_ppn'],
            'nilai_terbayar_mitra' => $mitra['terbayar'],
            'margin_dpp' => $this->money($pelanggan['dpp'] - $mitra['dpp']),
            'margin_ppn' => $this->money($pelanggan['dpp_ppn'] - $mitra['dpp_ppn']),
            'margin_persen' => $mitra['dpp'] > 0
                ? $this->money((($pelanggan['dpp'] - $mitra['dpp']) / $mitra['dpp']) * 100)
                : 0,
        ];
    }

    /** @return array<string, float> */
    private function calculateSide(float $jasa, float $material): array
    {
        $jasa = $this->money($jasa);
        $material = $this->money($material);
        $dpp = $this->money($jasa + $material);
        $ppn = $this->money(($dpp * 11 / 12) * 0.12);
        $pph = $this->money($jasa * 0.02);
        $dppPpn = $this->money($dpp + $ppn);

        return [
            'jasa' => $jasa,
            'material' => $material,
            'dpp' => $dpp,
            'ppn' => $ppn,
            'pph' => $pph,
            'dpp_ppn' => $dppPpn,
            'terbayar' => $this->money($dppPpn - $pph),
        ];
    }

    private function money(float $value): float
    {
        return round($value, 2);
    }
}
