<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ChevronRight, Plus, ReceiptText, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Project = { id: number; nama_perusahaan: string };
type Spk = { no_spk_pelanggan: string; nama_mitra: string; bast: unknown[] };
const props = defineProps<{ project: Project; spk: Spk }>();
defineOptions({ layout: { breadcrumbs: [{ title: 'Projects', href: '/projects' }, { title: 'BAST', href: '#' }] } });

const entry = () => ({ termin: '', persentase: 0, no_bast: '', tanggal_bast: '', no_invoice: '', tanggal_invoice: '', no_faktur_pajak: '', tanggal_faktur_pajak: '', nominal_bayar_pelanggan: 0, tanggal_bayar_pelanggan: '', status_bayar_pelanggan: 'belum_ditagih', keterangan: '', berkas_penawaran: '', berkas_bast_bapp: '', berkas_tagihan: '' });
const form = useForm({ bast: [entry()] });
const expanded = ref<number | null>(0);
const error = (index: number, field: string) => (form.errors as unknown as Record<string, string | undefined>)[`bast.${index}.${field}`];
function addEntry() {
 form.bast.push(entry()); expanded.value = form.bast.length - 1; 
}
function removeEntry(index: number) {
 if (form.bast.length > 1) {
form.bast.splice(index, 1);
} 
}
function submit() {
 form.post(`/projects/${props.project.id}/bast`); 
}
</script>

<template>
    <Head title="BAST" />
    <main class="min-h-full bg-[#F7FAFB] px-4 py-6 md:px-8 md:py-10"><div class="mx-auto max-w-6xl"><Link href="/projects" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-[#125D72]"><ArrowLeft class="size-4" /> Kembali ke projects</Link>
        <header class="mt-6 rounded-3xl bg-[#125D72] p-6 text-white shadow-[0_20px_50px_rgba(18,93,114,0.18)] md:p-8"><div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between"><div><div class="inline-flex items-center gap-2 rounded-full bg-white/12 px-3 py-1 text-xs font-semibold text-[#E7F6F9]"><ReceiptText class="size-3.5" /> TAHAP 04 — BAST</div><h1 class="mt-4 text-3xl font-semibold">Termin BAST & tagihan pelanggan</h1><p class="mt-2 text-sm text-[#E7F6F9]">Tambah satu atau beberapa termin sekaligus untuk SPK ini.</p></div><div class="rounded-2xl bg-white/10 p-4 text-sm"><p class="text-[#E7F6F9]">{{ project.nama_perusahaan }}</p><p class="mt-1 font-semibold">{{ spk.no_spk_pelanggan }}</p><p class="mt-1 text-xs text-white/70">Mitra: {{ spk.nama_mitra }}</p></div></div></header>
        <form class="mt-7 space-y-5" @submit.prevent="submit"><article v-for="(item, index) in form.bast" :key="index" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="flex items-center justify-between gap-4 p-5"><button type="button" class="flex min-w-0 items-center gap-3 text-left" @click="expanded = expanded === index ? null : index"><span class="grid size-9 shrink-0 place-items-center rounded-xl bg-[#E7F6F9] font-semibold text-[#125D72]">{{ index + 1 }}</span><span><span class="block font-semibold text-slate-900">{{ item.termin || `Termin ${index + 1}` }}</span><span class="block text-sm text-slate-500">{{ item.no_bast || 'Lengkapi informasi BAST' }}</span></span></button><button v-if="form.bast.length > 1" type="button" class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-[#DA2E09]" @click="removeEntry(index)"><Trash2 class="size-4" /></button></div>
            <div v-show="expanded === index" class="border-t border-slate-100 p-5 md:p-7"><div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3"><div class="space-y-2"><Label>Termin <span class="text-[#DA2E09]">*</span></Label><Input v-model="item.termin" class="h-11" placeholder="Termin I" /><InputError :message="error(index, 'termin')" /></div><div class="space-y-2"><Label>Persentase <span class="text-[#DA2E09]">*</span></Label><Input v-model.number="item.persentase" type="number" min="0" max="100" step="0.01" class="h-11" /><InputError :message="error(index, 'persentase')" /></div><div class="space-y-2"><Label>No BAST <span class="text-[#DA2E09]">*</span></Label><Input v-model="item.no_bast" class="h-11" /><InputError :message="error(index, 'no_bast')" /></div><div class="space-y-2"><Label>Tanggal BAST <span class="text-[#DA2E09]">*</span></Label><Input v-model="item.tanggal_bast" type="date" class="h-11" /><InputError :message="error(index, 'tanggal_bast')" /></div><div class="space-y-2"><Label>No Invoice</Label><Input v-model="item.no_invoice" class="h-11" /></div><div class="space-y-2"><Label>Tanggal Invoice</Label><Input v-model="item.tanggal_invoice" type="date" class="h-11" /></div><div class="space-y-2"><Label>No Faktur Pajak</Label><Input v-model="item.no_faktur_pajak" class="h-11" /></div><div class="space-y-2"><Label>Tanggal Faktur Pajak</Label><Input v-model="item.tanggal_faktur_pajak" type="date" class="h-11" /></div><div class="space-y-2"><Label>Nominal Bayar Pelanggan <span class="text-[#DA2E09]">*</span></Label><Input v-model.number="item.nominal_bayar_pelanggan" type="number" min="0" step="0.01" class="h-11" /><InputError :message="error(index, 'nominal_bayar_pelanggan')" /></div><div class="space-y-2"><Label>Status Bayar Pelanggan <span class="text-[#DA2E09]">*</span></Label><select v-model="item.status_bayar_pelanggan" class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm"><option value="belum_ditagih">Belum Ditagih</option><option value="sudah_invoice">Sudah Invoice</option><option value="lunas">Lunas</option></select><InputError :message="error(index, 'status_bayar_pelanggan')" /></div><div class="space-y-2"><Label>Tanggal Bayar Pelanggan</Label><Input v-model="item.tanggal_bayar_pelanggan" type="date" class="h-11" /></div></div>
                <div class="mt-5 grid gap-5 md:grid-cols-3"><div class="space-y-2"><Label>Berkas Penawaran (Google Drive)</Label><Input v-model="item.berkas_penawaran" type="url" class="h-11" placeholder="https://..." /></div><div class="space-y-2"><Label>Berkas BAST & BAPP</Label><Input v-model="item.berkas_bast_bapp" type="url" class="h-11" placeholder="https://..." /></div><div class="space-y-2"><Label>Berkas Tagihan</Label><Input v-model="item.berkas_tagihan" type="url" class="h-11" placeholder="https://..." /></div></div><div class="mt-5 space-y-2"><Label>Keterangan</Label><textarea v-model="item.keterangan" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#14A2BA] focus:ring-4 focus:ring-[#14A2BA]/10" placeholder="Catatan tambahan untuk termin ini" /></div></div>
        </article><button type="button" class="flex w-full items-center justify-center gap-2 rounded-2xl border border-dashed border-[#14A2BA]/50 bg-[#E7F6F9]/50 py-4 text-sm font-semibold text-[#125D72] transition hover:bg-[#E7F6F9]" @click="addEntry"><Plus class="size-4" /> Tambah termin BAST</button>
        <div class="sticky bottom-4 z-10 flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur md:flex-row md:items-center md:justify-between"><p class="text-sm text-slate-500">Status <strong>Lunas</strong> diperlukan sebelum project dapat selesai.</p><div class="flex gap-3"><Button as-child variant="outline" class="h-11"><Link href="/projects">Batal</Link></Button><Button type="submit" :disabled="form.processing" class="h-11 bg-[#125D72] hover:bg-[#0D4858]">{{ form.processing ? 'Menyimpan…' : 'Simpan termin' }} <ChevronRight class="size-4" /></Button></div></div></form>
    </div></main>
</template>
