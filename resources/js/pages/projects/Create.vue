<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Building2,
    Camera,
    ChevronRight,
    ClipboardPenLine,
    MapPin,
    Navigation,
    UserRound,
} from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Projects', href: '/projects' },
            { title: 'Project baru', href: '/projects/create' },
        ],
    },
});

const form = useForm({
    tanggal_probing: new Date().toISOString().slice(0, 10),
    unit_layanan: '',
    nama_pic_beyond_kwh: '',
    nama_perusahaan: '',
    alamat_perusahaan: '',
    latitude: '',
    longitude: '',
    scope_pekerjaan: 'electric_services',
    potensi_pekerjaan: '',
    nama_pic_pelanggan: '',
    nomor_pic_pelanggan: '',
    tanggal_inspeksi: '',
    opsi_selanjutnya: 'permintaan_penawaran',
    dokumentasi_probing: [] as File[],
    evidence_inspeksi: [] as File[],
});

const probingFilesLabel = computed(() => form.dokumentasi_probing.length ? `${form.dokumentasi_probing.length} foto dipilih` : 'Pilih foto dari perangkat');
const inspectionFilesLabel = computed(() => form.evidence_inspeksi.length ? `${form.evidence_inspeksi.length} foto dipilih` : 'Pilih foto dari perangkat');

function setFiles(field: 'dokumentasi_probing' | 'evidence_inspeksi', event: Event) {
    form[field] = Array.from((event.target as HTMLInputElement).files ?? []);
}

function submit() {
    form.post('/projects', { forceFormData: true });
}
</script>

<template>
    <Head title="Project Baru" />

    <main class="min-h-full bg-[#F7FAFB] px-4 py-6 md:px-8 md:py-10">
        <div class="mx-auto max-w-6xl">
            <Link href="/projects" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-[#125D72]">
                <ArrowLeft class="size-4" /> Kembali ke projects
            </Link>

            <div class="mt-6 overflow-hidden rounded-3xl bg-[#125D72] px-6 py-7 text-white shadow-[0_20px_50px_rgba(18,93,114,0.18)] md:px-9 md:py-9">
                <div class="flex flex-col justify-between gap-7 md:flex-row md:items-end">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/12 px-3 py-1 text-xs font-semibold tracking-wide text-[#E7F6F9]"><ClipboardPenLine class="size-3.5" /> TAHAP 01 — PROBING</div>
                        <h1 class="mt-4 text-3xl font-semibold tracking-tight md:text-4xl">Probing</h1>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-[#E7F6F9] md:text-base">Lengkapi data awal pelanggan dan hasil kunjungan lapangan. Data ini akan menjadi dasar tahap Penawaran.</p>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs font-medium">
                        <div class="rounded-xl bg-[#14A2BA] px-3 py-3 text-white">1<br><span class="text-[11px] text-white/80">Probing</span></div>
                        <div class="rounded-xl bg-white/10 px-3 py-3 text-white/70">2<br><span class="text-[11px]">Penawaran</span></div>
                        <div class="rounded-xl bg-white/10 px-3 py-3 text-white/70">3<br><span class="text-[11px]">SPK</span></div>
                    </div>
                </div>
            </div>

            <form class="mt-7 space-y-6" @submit.prevent="submit">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-4 border-b border-slate-100 px-5 py-5 md:px-7">
                        <div class="grid size-10 place-items-center rounded-xl bg-[#E7F6F9] text-[#125D72]"><Building2 class="size-5" /></div>
                        <div><h2 class="font-semibold text-slate-900">Informasi project</h2><p class="mt-0.5 text-sm text-slate-500">Identitas pelanggan dan kebutuhan pekerjaan.</p></div>
                    </div>
                    <div class="grid gap-x-6 gap-y-5 p-5 md:grid-cols-2 md:p-7">
                        <div class="space-y-2"><Label for="tanggal_probing">Tanggal Probing <span class="text-[#DA2E09]">*</span></Label><Input id="tanggal_probing" v-model="form.tanggal_probing" type="date" class="h-11" /><InputError :message="form.errors.tanggal_probing" /></div>
                        <div class="space-y-2"><Label for="unit_layanan">Unit Layanan <span class="text-[#DA2E09]">*</span></Label><Input id="unit_layanan" v-model="form.unit_layanan" class="h-11" placeholder="Contoh: UP3 Jakarta Selatan" /><InputError :message="form.errors.unit_layanan" /></div>
                        <div class="space-y-2"><Label for="nama_perusahaan">Nama Perusahaan (Pelanggan) <span class="text-[#DA2E09]">*</span></Label><Input id="nama_perusahaan" v-model="form.nama_perusahaan" class="h-11" placeholder="Nama perusahaan pelanggan" /><InputError :message="form.errors.nama_perusahaan" /></div>
                        <div class="space-y-2"><Label for="nama_pic_beyond_kwh">PIC Beyond KWH</Label><Input id="nama_pic_beyond_kwh" v-model="form.nama_pic_beyond_kwh" class="h-11" placeholder="Nama PIC internal" /><InputError :message="form.errors.nama_pic_beyond_kwh" /></div>
                        <div class="space-y-2 md:col-span-2"><Label for="alamat_perusahaan">Alamat Perusahaan <span class="text-[#DA2E09]">*</span></Label><textarea id="alamat_perusahaan" v-model="form.alamat_perusahaan" rows="3" class="w-full resize-y rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#14A2BA] focus:ring-4 focus:ring-[#14A2BA]/10" placeholder="Alamat lengkap lokasi pelanggan" /><InputError :message="form.errors.alamat_perusahaan" /></div>
                        <div class="space-y-2"><Label for="scope_pekerjaan">Scope Pekerjaan <span class="text-[#DA2E09]">*</span></Label><select id="scope_pekerjaan" v-model="form.scope_pekerjaan" class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none transition focus:border-[#14A2BA] focus:ring-4 focus:ring-[#14A2BA]/10"><option value="electric_services">Electric Services</option><option value="ev_ecosystem">EV Ecosystem</option></select><InputError :message="form.errors.scope_pekerjaan" /></div>
                        <div class="space-y-2"><Label for="potensi_pekerjaan">Potensi Pekerjaan</Label><Input id="potensi_pekerjaan" v-model="form.potensi_pekerjaan" class="h-11" placeholder="Contoh: Instalasi panel listrik" /><InputError :message="form.errors.potensi_pekerjaan" /></div>
                    </div>
                </section>

                <div class="grid gap-6 lg:grid-cols-5">
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-3">
                        <div class="flex items-center gap-4 border-b border-slate-100 px-5 py-5 md:px-7"><div class="grid size-10 place-items-center rounded-xl bg-[#E7F6F9] text-[#125D72]"><UserRound class="size-5" /></div><div><h2 class="font-semibold text-slate-900">PIC & inspeksi</h2><p class="mt-0.5 text-sm text-slate-500">Kontak pelanggan dan detail survey lanjutan.</p></div></div>
                        <div class="grid gap-x-6 gap-y-5 p-5 md:grid-cols-2 md:p-7">
                            <div class="space-y-2"><Label for="nama_pic_pelanggan">Nama PIC Pelanggan <span class="text-[#DA2E09]">*</span></Label><Input id="nama_pic_pelanggan" v-model="form.nama_pic_pelanggan" class="h-11" placeholder="Nama lengkap PIC" /><InputError :message="form.errors.nama_pic_pelanggan" /></div>
                            <div class="space-y-2"><Label for="nomor_pic_pelanggan">Nomor PIC Pelanggan <span class="text-[#DA2E09]">*</span></Label><Input id="nomor_pic_pelanggan" v-model="form.nomor_pic_pelanggan" class="h-11" placeholder="08xx xxxx xxxx" /><InputError :message="form.errors.nomor_pic_pelanggan" /></div>
                            <div class="space-y-2"><Label for="tanggal_inspeksi">Tanggal Inspeksi</Label><Input id="tanggal_inspeksi" v-model="form.tanggal_inspeksi" type="date" class="h-11" /><InputError :message="form.errors.tanggal_inspeksi" /></div>
                            <div class="space-y-2"><Label for="opsi_selanjutnya">Opsi Selanjutnya <span class="text-[#DA2E09]">*</span></Label><select id="opsi_selanjutnya" v-model="form.opsi_selanjutnya" class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none transition focus:border-[#14A2BA] focus:ring-4 focus:ring-[#14A2BA]/10"><option value="permintaan_penawaran">Permintaan Penawaran</option><option value="butuh_survey_ulang">Butuh Survey Ulang</option><option value="masih_mempertimbangkan">Pelanggan Masih Mempertimbangkan</option><option value="sudah_punya_vendor_lain">Pelanggan Sudah Punya Vendor Lain</option></select><InputError :message="form.errors.opsi_selanjutnya" /></div>
                        </div>
                    </section>
                    <aside class="rounded-2xl border border-[#14A2BA]/20 bg-[#E7F6F9] p-5 lg:col-span-2"><MapPin class="size-5 text-[#125D72]" /><h2 class="mt-4 font-semibold text-[#125D72]">Lokasi project</h2><p class="mt-1 text-sm leading-6 text-[#125D72]/70">Koordinat membantu tim menemukan lokasi saat tindak lanjut lapangan.</p><div class="mt-5 grid gap-4"><div class="space-y-2"><Label for="latitude">Latitude</Label><Input id="latitude" v-model="form.latitude" type="number" step="any" class="h-11 border-white bg-white" placeholder="-6.2088" /><InputError :message="form.errors.latitude" /></div><div class="space-y-2"><Label for="longitude">Longitude</Label><Input id="longitude" v-model="form.longitude" type="number" step="any" class="h-11 border-white bg-white" placeholder="106.8456" /><InputError :message="form.errors.longitude" /></div></div><p class="mt-4 flex items-center gap-2 text-xs text-[#125D72]/70"><Navigation class="size-3.5" /> Boleh dikosongkan bila titik lokasi belum tersedia.</p></aside>
                </div>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-4 border-b border-slate-100 px-5 py-5 md:px-7"><div class="grid size-10 place-items-center rounded-xl bg-[#E7F6F9] text-[#125D72]"><Camera class="size-5" /></div><div><h2 class="font-semibold text-slate-900">Dokumentasi lapangan</h2><p class="mt-0.5 text-sm text-slate-500">Tambahkan foto pendukung. Maksimal 10 MB per file.</p></div></div>
                    <div class="grid gap-5 p-5 md:grid-cols-2 md:p-7">
                        <label for="dokumentasi_probing" class="group cursor-pointer rounded-xl border border-dashed border-[#14A2BA]/40 bg-[#F7FCFD] p-5 transition hover:border-[#14A2BA] hover:bg-[#E7F6F9]"><input id="dokumentasi_probing" type="file" accept="image/*" multiple class="sr-only" @change="setFiles('dokumentasi_probing', $event)" /><Camera class="size-5 text-[#14A2BA]" /><p class="mt-3 font-medium text-slate-800">Dokumentasi Probing</p><p class="mt-1 text-sm text-slate-500">{{ probingFilesLabel }}</p><span class="mt-4 inline-flex text-sm font-semibold text-[#125D72]">Pilih foto <ChevronRight class="ml-1 size-4" /></span><InputError class="mt-2" :message="form.errors.dokumentasi_probing" /></label>
                        <label for="evidence_inspeksi" class="group cursor-pointer rounded-xl border border-dashed border-[#14A2BA]/40 bg-[#F7FCFD] p-5 transition hover:border-[#14A2BA] hover:bg-[#E7F6F9]"><input id="evidence_inspeksi" type="file" accept="image/*" multiple class="sr-only" @change="setFiles('evidence_inspeksi', $event)" /><Camera class="size-5 text-[#14A2BA]" /><p class="mt-3 font-medium text-slate-800">Evidence Inspeksi</p><p class="mt-1 text-sm text-slate-500">{{ inspectionFilesLabel }}</p><span class="mt-4 inline-flex text-sm font-semibold text-[#125D72]">Pilih foto <ChevronRight class="ml-1 size-4" /></span><InputError class="mt-2" :message="form.errors.evidence_inspeksi" /></label>
                    </div>
                </section>

                <div class="sticky bottom-4 z-10 flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur md:flex-row md:items-center md:justify-between"><p class="text-sm text-slate-500"><span class="text-[#DA2E09]">*</span> Field wajib diisi sebelum melanjutkan.</p><div class="flex gap-3"><Button as-child variant="outline" class="h-11 border-slate-200"><Link href="/projects">Batal</Link></Button><Button type="submit" :disabled="form.processing" class="h-11 bg-[#125D72] px-6 hover:bg-[#0D4858]">{{ form.processing ? 'Menyimpan…' : 'Simpan & lanjutkan' }} <ChevronRight class="size-4" /></Button></div></div>
            </form>
        </div>
    </main>
</template>
