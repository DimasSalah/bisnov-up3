<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Building2, Calculator, CheckCircle2, ChevronRight, FileText, HandCoins, UserRound } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Project = { id: number; nama_perusahaan: string; unit_layanan: string; scope_pekerjaan: string };
const props = defineProps<{ project: Project }>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Projects', href: '/projects' }, { title: 'Penawaran', href: '#' }] } });

const form = useForm({
    no_penawaran: '',
    pic_penawaran: '',
    nilai_jasa_pelanggan: 0,
    nilai_material_pelanggan: 0,
    nilai_jasa_mitra: 0,
    nilai_material_mitra: 0,
});

const side = (jasa: number, material: number) => {
    const dpp = Number(jasa || 0) + Number(material || 0);
    const ppn = (dpp * 11 / 12) * 0.12;
    const pph = Number(jasa || 0) * 0.02;

    return { dpp, ppn, pph, dppPpn: dpp + ppn, terbayar: dpp + ppn - pph };
};
const pelanggan = computed(() => side(form.nilai_jasa_pelanggan, form.nilai_material_pelanggan));
const mitra = computed(() => side(form.nilai_jasa_mitra, form.nilai_material_mitra));
const margin = computed(() => ({ dpp: pelanggan.value.dpp - mitra.value.dpp, ppn: pelanggan.value.dppPpn - mitra.value.dppPpn, persen: mitra.value.dpp ? ((pelanggan.value.dpp - mitra.value.dpp) / mitra.value.dpp) * 100 : 0 }));
const money = (value: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 2 }).format(value);

function submit() {
    form.post(`/projects/${props.project.id}/penawaran`);
}
</script>

<template>
    <Head title="Penawaran" />

    <main class="min-h-full bg-[#F7FAFB] px-4 py-6 md:px-8 md:py-10">
        <div class="mx-auto max-w-6xl">
            <Link href="/projects" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-[#125D72]"><ArrowLeft class="size-4" /> Kembali ke projects</Link>
            <div class="mt-6 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 md:p-8">
                <div class="flex flex-col justify-between gap-6 md:flex-row md:items-center"><div><div class="inline-flex items-center gap-2 rounded-full bg-[#E7F6F9] px-3 py-1 text-xs font-semibold tracking-wide text-[#125D72]"><FileText class="size-3.5" /> TAHAP 02 — PENAWARAN</div><h1 class="mt-4 text-3xl font-semibold tracking-tight text-[#125D72] md:text-4xl">Siapkan nilai penawaran</h1><p class="mt-2 text-sm text-slate-500">Nilai turunan dihitung otomatis agar mengikuti rumus resmi.</p></div><div class="rounded-2xl border border-[#14A2BA]/20 bg-[#E7F6F9] p-4 md:min-w-65"><div class="flex items-center gap-3"><div class="grid size-9 place-items-center rounded-xl bg-white text-[#125D72]"><Building2 class="size-4" /></div><div><p class="text-xs text-[#125D72]/70">Pelanggan</p><p class="font-semibold text-[#125D72]">{{ project.nama_perusahaan }}</p></div></div><p class="mt-3 text-xs text-[#125D72]/70">{{ project.unit_layanan }} · {{ project.scope_pekerjaan === 'ev_ecosystem' ? 'EV Ecosystem' : 'Electric Services' }}</p></div></div>
            </div>

            <form class="mt-7 space-y-6" @submit.prevent="submit">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-7"><div class="flex items-center gap-4"><div class="grid size-10 place-items-center rounded-xl bg-[#E7F6F9] text-[#125D72]"><UserRound class="size-5" /></div><div><h2 class="font-semibold text-slate-900">Identitas penawaran</h2><p class="mt-0.5 text-sm text-slate-500">Referensi dokumen dan PIC yang menyiapkan penawaran.</p></div></div><div class="mt-6 grid gap-5 md:grid-cols-2"><div class="space-y-2"><Label for="no_penawaran">No Penawaran <span class="text-[#DA2E09]">*</span></Label><Input id="no_penawaran" v-model="form.no_penawaran" class="h-11" placeholder="Contoh: OFF/UP3/001/VII/2026" /><InputError :message="form.errors.no_penawaran" /></div><div class="space-y-2"><Label for="pic_penawaran">PIC Penawaran <span class="text-[#DA2E09]">*</span></Label><Input id="pic_penawaran" v-model="form.pic_penawaran" class="h-11" placeholder="Nama PIC penawaran" /><InputError :message="form.errors.pic_penawaran" /></div></div></section>

                <section class="grid gap-6 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="flex items-center gap-4 border-b border-slate-100 p-5"><div class="grid size-10 place-items-center rounded-xl bg-[#E7F6F9] text-[#125D72]"><HandCoins class="size-5" /></div><div><h2 class="font-semibold text-slate-900">Sisi Pelanggan</h2><p class="text-sm text-slate-500">Nilai yang ditagihkan ke pelanggan.</p></div></div><div class="space-y-5 p-5"><div class="space-y-2"><Label for="nilai_jasa_pelanggan">Nilai Jasa Pelanggan <span class="text-[#DA2E09]">*</span></Label><Input id="nilai_jasa_pelanggan" v-model.number="form.nilai_jasa_pelanggan" type="number" min="0" step="0.01" class="h-11" /><InputError :message="form.errors.nilai_jasa_pelanggan" /></div><div class="space-y-2"><Label for="nilai_material_pelanggan">Nilai Material Pelanggan <span class="text-[#DA2E09]">*</span></Label><Input id="nilai_material_pelanggan" v-model.number="form.nilai_material_pelanggan" type="number" min="0" step="0.01" class="h-11" /><InputError :message="form.errors.nilai_material_pelanggan" /></div><div class="space-y-2 rounded-xl bg-slate-50 p-4 text-sm"><div class="flex justify-between"><span class="text-slate-500">DPP</span><strong>{{ money(pelanggan.dpp) }}</strong></div><div class="flex justify-between"><span class="text-slate-500">PPN</span><span>{{ money(pelanggan.ppn) }}</span></div><div class="flex justify-between"><span class="text-slate-500">PPH 23</span><span>{{ money(pelanggan.pph) }}</span></div><div class="mt-2 flex justify-between border-t border-slate-200 pt-3"><span class="font-medium text-slate-700">Nilai Terbayar</span><strong class="text-[#125D72]">{{ money(pelanggan.terbayar) }}</strong></div></div></div></div>
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="flex items-center gap-4 border-b border-slate-100 p-5"><div class="grid size-10 place-items-center rounded-xl bg-[#E7F6F9] text-[#125D72]"><HandCoins class="size-5" /></div><div><h2 class="font-semibold text-slate-900">Sisi Mitra</h2><p class="text-sm text-slate-500">Nilai pekerjaan dari Mitra.</p></div></div><div class="space-y-5 p-5"><div class="space-y-2"><Label for="nilai_jasa_mitra">Nilai Jasa Mitra <span class="text-[#DA2E09]">*</span></Label><Input id="nilai_jasa_mitra" v-model.number="form.nilai_jasa_mitra" type="number" min="0" step="0.01" class="h-11" /><InputError :message="form.errors.nilai_jasa_mitra" /></div><div class="space-y-2"><Label for="nilai_material_mitra">Nilai Material Mitra <span class="text-[#DA2E09]">*</span></Label><Input id="nilai_material_mitra" v-model.number="form.nilai_material_mitra" type="number" min="0" step="0.01" class="h-11" /><InputError :message="form.errors.nilai_material_mitra" /></div><div class="space-y-2 rounded-xl bg-slate-50 p-4 text-sm"><div class="flex justify-between"><span class="text-slate-500">DPP</span><strong>{{ money(mitra.dpp) }}</strong></div><div class="flex justify-between"><span class="text-slate-500">PPN</span><span>{{ money(mitra.ppn) }}</span></div><div class="flex justify-between"><span class="text-slate-500">PPH 23</span><span>{{ money(mitra.pph) }}</span></div><div class="mt-2 flex justify-between border-t border-slate-200 pt-3"><span class="font-medium text-slate-700">Nilai Terbayar</span><strong class="text-[#125D72]">{{ money(mitra.terbayar) }}</strong></div></div></div></div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-[#14A2BA]/20 bg-[#125D72] text-white shadow-[0_16px_40px_rgba(18,93,114,0.17)]"><div class="flex flex-col gap-5 p-5 md:flex-row md:items-center md:justify-between md:p-7"><div class="flex items-center gap-4"><div class="grid size-11 place-items-center rounded-xl bg-white/12 text-[#EFE62F]"><Calculator class="size-5" /></div><div><p class="text-sm font-medium text-[#E7F6F9]">Ringkasan margin</p><h2 class="mt-1 text-xl font-semibold">Hasil kalkulasi otomatis</h2></div></div><div class="grid grid-cols-3 gap-6 text-right text-sm"><div><p class="text-[#E7F6F9]/75">Margin DPP</p><p class="mt-1 font-semibold">{{ money(margin.dpp) }}</p></div><div><p class="text-[#E7F6F9]/75">Margin PPN</p><p class="mt-1 font-semibold">{{ money(margin.ppn) }}</p></div><div><p class="text-[#E7F6F9]/75">Margin %</p><p class="mt-1 font-semibold text-[#EFE62F]">{{ margin.persen.toFixed(2) }}%</p></div></div></div></section>

                <div class="sticky bottom-4 z-10 flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur md:flex-row md:items-center md:justify-between"><p class="flex items-center gap-2 text-sm text-slate-500"><CheckCircle2 class="size-4 text-[#14A2BA]" /> Perhitungan final selalu dilakukan ulang di server.</p><div class="flex gap-3"><Button as-child variant="outline" class="h-11 border-slate-200"><Link href="/projects">Batal</Link></Button><Button type="submit" :disabled="form.processing" class="h-11 bg-[#125D72] px-6 hover:bg-[#0D4858]">{{ form.processing ? 'Menyimpan…' : 'Simpan Penawaran' }} <ChevronRight class="size-4" /></Button></div></div>
            </form>
        </div>
    </main>
</template>
