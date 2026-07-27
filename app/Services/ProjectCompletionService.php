<?php

namespace App\Services;

use App\Models\Project;

class ProjectCompletionService
{
    public function completeIfPaid(Project $project): bool
    {
        $spk = $project->spk;

        if (! $spk || $project->status === 'selesai') {
            return false;
        }

        $allCustomerInvoicesPaid = $spk->bast()->exists()
            && ! $spk->bast()->where('status_bayar_pelanggan', '!=', 'lunas')->exists();
        $allPartnerInvoicesPaid = $spk->tagihanMitra()->exists()
            && ! $spk->tagihanMitra()->whereNull('tanggal_bayar')->exists();

        if (! $allCustomerInvoicesPaid || ! $allPartnerInvoicesPaid) {
            return false;
        }

        $statusDari = $project->status;
        $project->update(['status' => 'selesai']);
        $project->statusHistory()->create([
            'status_dari' => $statusDari,
            'status_ke' => 'selesai',
            'keterangan' => 'Seluruh tagihan pelanggan dan Mitra telah lunas.',
        ]);

        return true;
    }
}
