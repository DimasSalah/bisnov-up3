<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    ArrowUpDown,
    CircleAlert,
    Clock3,
    GripVertical,
    LayoutGrid,
    List,
    Plus,
    Search,
    SlidersHorizontal,
    WalletCards,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Project = {
    id: number;
    nama_perusahaan: string;
    unit_layanan: string;
    status: string;
    tanggal_probing: string;
    opsi_selanjutnya?: string | null;
    nilai_project: number;
    umur_tahap_hari: number;
    pic?: string | null;
    penawaran?: { no_penawaran: string; pic_penawaran: string } | null;
    spk?: { no_spk_pelanggan?: string | null; nama_mitra: string } | null;
};
type Filters = {
    status?: string;
    mitra?: string;
    marketing?: string;
    unit?: string;
    date_from?: string;
    date_to?: string;
    search?: string;
    view?: 'table' | 'kanban';
    sort?: string;
    direction?: 'asc' | 'desc';
};
type Pagination = {
    data: Project[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};
type StatusSummary = Record<string, { count: number; value: number }>;

const props = defineProps<{
    projects: Project[];
    tableProjects: Pagination | null;
    filters: Filters;
    statusSummaries: StatusSummary;
    mitras: string[];
    marketings: string[];
    units: string[];
}>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Projects', href: '/projects' }] } });

const statuses = [
    { id: 'probing', title: 'Probing', accent: 'border-t-[#14A2BA]', badge: 'bg-cyan-50 text-[#125D72]' },
    { id: 'penawaran', title: 'Penawaran', accent: 'border-t-[#125D72]', badge: 'bg-[#E7F6F9] text-[#125D72]' },
    { id: 'spk', title: 'SPK', accent: 'border-t-[#EFE62F]', badge: 'bg-yellow-50 text-amber-700' },
    { id: 'bast', title: 'BAST', accent: 'border-t-[#DA2E09]', badge: 'bg-orange-50 text-[#B6290B]' },
    { id: 'tagihan_mitra', title: 'Tagihan Mitra', accent: 'border-t-[#14A2BA]', badge: 'bg-sky-50 text-sky-700' },
    { id: 'selesai', title: 'Selesai', accent: 'border-t-emerald-500', badge: 'bg-emerald-50 text-emerald-700' },
    { id: 'gagal_wo', title: 'Gagal WO', accent: 'border-t-slate-400', badge: 'bg-slate-100 text-slate-600' },
] as const;

const emptyFilters: Filters = {
    status: '',
    mitra: '',
    marketing: '',
    unit: '',
    date_from: '',
    date_to: '',
    search: '',
    view: 'table',
    sort: 'umur_tahap',
    direction: 'desc',
};
const filter = ref<Filters>({ ...emptyFilters, ...props.filters });
const draggingProject = ref<Project | null>(null);
const notice = ref('');
const grouped = computed(
    () =>
        Object.fromEntries(
            statuses.map((status) => [status.id, props.projects.filter((project) => project.status === status.id)]),
        ) as Record<string, Project[]>,
);
const statusOrder: Record<string, number> = { probing: 0, penawaran: 1, spk: 2, bast: 3, tagihan_mitra: 4, selesai: 5 };

watch(
    () => props.filters,
    (value) => {
        filter.value = { ...emptyFilters, ...value };
    },
    { deep: true },
);

const statusMeta = (status: string) => statuses.find((item) => item.id === status) ?? statuses[0];
const money = (value: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
        notation: value >= 1_000_000_000 ? 'compact' : 'standard',
    }).format(value || 0);
const initials = (name?: string | null) =>
    (name || '?')
        .split(/\s+/)
        .slice(0, 2)
        .map((word) => word[0])
        .join('')
        .toUpperCase();
const ageClass = (days: number) => {
    if (days >= 30) {
return 'bg-red-50 text-[#B6290B] ring-red-100';
}

    if (days >= 14) {
return 'bg-yellow-50 text-amber-700 ring-yellow-100';
}

    return 'bg-slate-50 text-slate-500 ring-slate-100';
};
const reference = (project: Project) => project.penawaran?.no_penawaran || project.spk?.no_spk_pelanggan || 'Belum ada referensi';

function cleanedFilters(overrides: Filters = {}) {
    return Object.fromEntries(
        Object.entries({ ...filter.value, ...overrides }).filter(([, value]) => value !== '' && value !== undefined && value !== null),
    );
}
function applyFilters() {
    router.get('/projects', cleanedFilters({ page: undefined } as Filters), { preserveState: true, replace: true });
}
function clearFilters() {
    filter.value = { ...emptyFilters, view: filter.value.view ?? 'table' };
    applyFilters();
}
function switchView(view: 'table' | 'kanban') {
    filter.value.view = view;
    applyFilters();
}
function sortBy(field: string) {
    filter.value.direction = filter.value.sort === field && filter.value.direction === 'desc' ? 'asc' : 'desc';
    filter.value.sort = field;
    applyFilters();
}
function viewAll(status: string) {
    const query = new URLSearchParams(cleanedFilters({ view: 'table', status }) as Record<string, string>);

    return `/projects?${query.toString()}`;
}
function nextForm(project: Project): string | null {
    if (project.status === 'probing' && project.opsi_selanjutnya === 'permintaan_penawaran') {
        return `/projects/${project.id}/penawaran/create`;
    }

    const routes: Record<string, string> = {
        penawaran: `/projects/${project.id}/spk/create`,
        spk: `/projects/${project.id}/bast/create`,
        bast: `/projects/${project.id}/tagihan-mitra/create`,
    };

    return routes[project.status] ?? null;
}
function startDrag(project: Project, event: DragEvent) {
    draggingProject.value = project;
    event.dataTransfer?.setData('text/plain', String(project.id));

    if (event.dataTransfer) {
event.dataTransfer.effectAllowed = 'move';
}
}
function finishDrag() {
    draggingProject.value = null;
}
function drop(project: Project, target: string) {
    draggingProject.value = null;
    notice.value = '';

    if (project.status === target) {
return;
}

    const currentOrder = statusOrder[project.status];
    const targetOrder = statusOrder[target];

    if (targetOrder !== undefined && targetOrder > currentOrder + 1) {
        notice.value = 'Project tidak dapat melompati tahap.';

        return;
    }

    const targetForms: Record<string, string> = {
        penawaran: `/projects/${project.id}/penawaran/create`,
        spk: `/projects/${project.id}/spk/create`,
        bast: `/projects/${project.id}/bast/create`,
        tagihan_mitra: `/projects/${project.id}/tagihan-mitra/create`,
    };

    if (targetOrder === currentOrder + 1 && targetForms[target]) {
        router.visit(targetForms[target]);

        return;
    }

    if (target === 'selesai') {
        notice.value = 'Status Selesai ditetapkan otomatis setelah seluruh tagihan pelanggan dan Mitra lunas.';

        return;
    }

    if (target === 'gagal_wo') {
        router.patch(`/projects/${project.id}/status`, { status_tujuan: 'gagal_wo' });

        return;
    }

    router.patch(
        `/projects/${project.id}/move`,
        { status_tujuan: target, position: 0 },
        { onError: (errors) => (notice.value = Object.values(errors)[0] ?? 'Perpindahan status tidak valid.') },
    );
}
</script>

<template>
    <Head title="Monitoring Project" />
    <main class="min-h-full bg-[#F5F8F9] p-4 md:p-7">
        <div class="mx-auto max-w-[1800px]">
            <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold tracking-[0.16em] text-[#14A2BA]">MONITORING E-SOLUTION</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-[#125D72] md:text-3xl">Workspace project</h1>
                    <p class="mt-1 text-sm text-slate-500">Pantau prioritas, nilai, PIC, dan progres seluruh project.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
                        <button
                            class="flex h-9 items-center gap-2 rounded-lg px-3 text-sm font-medium transition"
                            :class="filter.view !== 'kanban' ? 'bg-[#125D72] text-white' : 'text-slate-500 hover:bg-slate-50'"
                            @click="switchView('table')"
                        >
                            <List class="size-4" /> Tabel
                        </button>
                        <button
                            class="flex h-9 items-center gap-2 rounded-lg px-3 text-sm font-medium transition"
                            :class="filter.view === 'kanban' ? 'bg-[#125D72] text-white' : 'text-slate-500 hover:bg-slate-50'"
                            @click="switchView('kanban')"
                        >
                            <LayoutGrid class="size-4" /> Kanban
                        </button>
                    </div>
                    <Button as-child class="h-11 bg-[#125D72] hover:bg-[#0D4858]">
                        <Link href="/projects/create"><Plus class="size-4" /> Project Baru</Link>
                    </Button>
                </div>
            </header>

            <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <SlidersHorizontal class="size-4 text-[#14A2BA]" /> Filter project
                </div>
                <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-7">
                    <div class="relative md:col-span-2">
                        <Search class="absolute left-3 top-3 size-4 text-slate-400" />
                        <Input
                            v-model="filter.search"
                            class="h-10 pl-9"
                            placeholder="Nama pelanggan atau No. Penawaran"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <select v-model="filter.status" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm">
                        <option value="">Semua status</option>
                        <option v-for="status in statuses" :key="status.id" :value="status.id">{{ status.title }}</option>
                    </select>
                    <select v-model="filter.mitra" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm">
                        <option value="">Semua Mitra</option>
                        <option v-for="mitra in mitras" :key="mitra" :value="mitra">{{ mitra }}</option>
                    </select>
                    <select v-model="filter.marketing" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm">
                        <option value="">Semua PIC</option>
                        <option v-for="marketing in marketings" :key="marketing" :value="marketing">{{ marketing }}</option>
                    </select>
                    <select v-model="filter.unit" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm">
                        <option value="">Semua Unit</option>
                        <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
                    </select>
                    <div class="grid grid-cols-2 gap-2">
                        <Input v-model="filter.date_from" type="date" class="h-10 px-2 text-xs" title="Tanggal mulai" />
                        <Input v-model="filter.date_to" type="date" class="h-10 px-2 text-xs" title="Tanggal akhir" />
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <Button size="sm" class="bg-[#125D72] hover:bg-[#0D4858]" @click="applyFilters">Terapkan filter</Button>
                    <Button size="sm" variant="ghost" @click="clearFilters">Reset</Button>
                </div>
            </section>

            <div
                v-if="notice"
                class="mt-4 flex items-center gap-2 rounded-xl border border-[#EFE62F] bg-[#FFF9CC] px-4 py-3 text-sm text-slate-700"
            >
                <CircleAlert class="size-4 shrink-0 text-[#9C8700]" /> {{ notice }}
            </div>

            <section v-if="filter.view !== 'kanban'" class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-slate-900">Daftar project</h2>
                        <p class="mt-0.5 text-xs text-slate-500">{{ tableProjects?.total ?? 0 }} project sesuai filter</p>
                    </div>
                    <p class="text-xs text-slate-400">Klik judul kolom untuk mengurutkan</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1180px] text-left text-sm">
                        <thead class="bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3">
                                    <button class="flex items-center gap-1.5" @click="sortBy('nama_perusahaan')">Project <ArrowUpDown class="size-3.5" /></button>
                                </th>
                                <th class="px-4 py-3">
                                    <button class="flex items-center gap-1.5" @click="sortBy('status')">Tahap <ArrowUpDown class="size-3.5" /></button>
                                </th>
                                <th class="px-4 py-3">Referensi</th>
                                <th class="px-4 py-3">PIC</th>
                                <th class="px-4 py-3">Mitra / Unit</th>
                                <th class="px-4 py-3 text-right">
                                    <button class="ml-auto flex items-center gap-1.5" @click="sortBy('nilai_project')">Nilai <ArrowUpDown class="size-3.5" /></button>
                                </th>
                                <th class="px-4 py-3">
                                    <button class="flex items-center gap-1.5" @click="sortBy('umur_tahap')">Umur tahap <ArrowUpDown class="size-3.5" /></button>
                                </th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="project in tableProjects?.data ?? []" :key="project.id" class="transition hover:bg-[#F7FBFC]">
                                <td class="px-5 py-3.5">
                                    <Link :href="`/projects/${project.id}`" class="font-semibold text-[#125D72] hover:underline">{{ project.nama_perusahaan }}</Link>
                                    <p class="mt-1 text-xs text-slate-400">Probing {{ project.tanggal_probing }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusMeta(project.status).badge">{{ statusMeta(project.status).title }}</span>
                                </td>
                                <td class="max-w-52 px-4 py-3.5">
                                    <p class="truncate text-xs font-medium text-slate-600">{{ reference(project) }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="grid size-8 shrink-0 place-items-center rounded-full bg-[#E7F6F9] text-[11px] font-bold text-[#125D72]">{{ initials(project.pic) }}</span>
                                        <span class="max-w-28 truncate text-xs text-slate-600">{{ project.pic || 'Belum ditentukan' }}</span>
                                    </div>
                                </td>
                                <td class="max-w-48 px-4 py-3.5">
                                    <p class="truncate text-xs font-medium text-slate-700">{{ project.spk?.nama_mitra || 'Belum ada Mitra' }}</p>
                                    <p class="mt-1 truncate text-xs text-slate-400">{{ project.unit_layanan }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-right font-semibold text-slate-800">{{ project.nilai_project ? money(project.nilai_project) : '—' }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold ring-1" :class="ageClass(project.umur_tahap_hari)">
                                        <Clock3 class="size-3" /> {{ project.umur_tahap_hari }} hari
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <Button v-if="nextForm(project)" as-child size="sm" variant="outline" class="h-8 border-[#14A2BA]/30 text-[#125D72] hover:bg-[#E7F6F9]">
                                        <Link :href="nextForm(project)!">Lanjut <ArrowRight class="size-3.5" /></Link>
                                    </Button>
                                    <Link v-else :href="`/projects/${project.id}`" class="text-xs font-semibold text-[#125D72] hover:underline">Detail</Link>
                                </td>
                            </tr>
                            <tr v-if="!tableProjects?.data.length">
                                <td colspan="8" class="px-5 py-16 text-center">
                                    <Search class="mx-auto size-7 text-slate-300" />
                                    <p class="mt-3 font-medium text-slate-600">Project tidak ditemukan</p>
                                    <p class="mt-1 text-xs text-slate-400">Ubah atau reset filter untuk melihat data lain.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <footer v-if="tableProjects && tableProjects.last_page > 1" class="flex items-center justify-between border-t border-slate-100 px-5 py-4">
                    <p class="text-xs text-slate-500">Menampilkan {{ tableProjects.from }}–{{ tableProjects.to }} dari {{ tableProjects.total }}</p>
                    <div class="flex items-center gap-2">
                        <Button as-child size="sm" variant="outline" :disabled="!tableProjects.prev_page_url">
                            <Link v-if="tableProjects.prev_page_url" :href="tableProjects.prev_page_url" preserve-state>Sebelumnya</Link>
                            <span v-else>Sebelumnya</span>
                        </Button>
                        <span class="px-2 text-xs font-medium text-slate-500">{{ tableProjects.current_page }} / {{ tableProjects.last_page }}</span>
                        <Button as-child size="sm" variant="outline" :disabled="!tableProjects.next_page_url">
                            <Link v-if="tableProjects.next_page_url" :href="tableProjects.next_page_url" preserve-state>Berikutnya</Link>
                            <span v-else>Berikutnya</span>
                        </Button>
                    </div>
                </footer>
            </section>

            <section v-else class="mt-5 flex gap-4 overflow-x-auto pb-4">
                <div
                    v-for="status in statuses"
                    :key="status.id"
                    class="w-80 shrink-0 rounded-2xl border border-slate-200 border-t-4 bg-slate-100/70 p-3"
                    :class="status.accent"
                    @dragover.prevent
                    @drop="draggingProject && drop(draggingProject, status.id)"
                >
                    <div class="px-1 pb-3">
                        <div class="flex items-center justify-between">
                            <h2 class="font-semibold text-slate-800">{{ status.title }}</h2>
                            <span class="rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-slate-500">{{ statusSummaries[status.id]?.count ?? 0 }}</span>
                        </div>
                        <p class="mt-1 flex items-center gap-1 text-xs font-medium text-slate-500">
                            <WalletCards class="size-3.5 text-[#14A2BA]" /> {{ money(statusSummaries[status.id]?.value ?? 0) }}
                        </p>
                    </div>
                    <div class="min-h-32 space-y-3">
                        <article
                            v-for="project in grouped[status.id]"
                            :key="project.id"
                            :draggable="!['selesai', 'gagal_wo'].includes(project.status)"
                            class="group rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                            @dragstart="startDrag(project, $event)"
                            @dragend="finishDrag"
                        >
                            <div class="flex gap-2">
                                <GripVertical class="mt-0.5 size-4 shrink-0 cursor-grab text-slate-300" />
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <Link :href="`/projects/${project.id}`" class="truncate font-semibold text-[#125D72] group-hover:underline">{{ project.nama_perusahaan }}</Link>
                                        <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[10px] font-semibold ring-1" :class="ageClass(project.umur_tahap_hari)">{{ project.umur_tahap_hari }}h</span>
                                    </div>
                                    <p class="mt-1 truncate text-[11px] text-slate-400">{{ reference(project) }}</p>
                                    <p class="mt-3 text-sm font-bold text-slate-800">{{ project.nilai_project ? money(project.nilai_project) : 'Nilai belum tercatat' }}</p>
                                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-[#E7F6F9] text-[10px] font-bold text-[#125D72]">{{ initials(project.pic) }}</span>
                                            <span class="truncate text-[11px] text-slate-500">{{ project.pic || project.unit_layanan }}</span>
                                        </div>
                                        <Link
                                            v-if="nextForm(project)"
                                            :href="nextForm(project)!"
                                            class="grid size-7 shrink-0 place-items-center rounded-lg bg-[#125D72] text-white transition hover:bg-[#0D4858]"
                                            title="Lanjutkan ke tahap berikutnya"
                                            @click.stop
                                        >
                                            <ArrowRight class="size-3.5" />
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </article>
                        <div v-if="!grouped[status.id].length" class="rounded-xl border border-dashed border-slate-200 bg-white/55 px-3 py-7 text-center text-xs text-slate-400">Belum ada project</div>
                        <Link
                            v-if="(statusSummaries[status.id]?.count ?? 0) > grouped[status.id].length"
                            :href="viewAll(status.id)"
                            class="flex h-10 items-center justify-center rounded-xl border border-dashed border-[#14A2BA]/40 bg-white text-xs font-semibold text-[#125D72] hover:bg-[#E7F6F9]"
                        >
                            Lihat semua {{ statusSummaries[status.id].count }} project
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>
