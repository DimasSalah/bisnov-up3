<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, CalendarDays, Filter, FolderKanban, Plus, RotateCcw, TrendingUp } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

type FunnelStage = { status: string; count: number; conversion: number };

const props = defineProps<{
    filters: { date_from: string | null; date_to: string | null };
    statusCounts: Record<string, number>;
    statusValues: Record<string, number>;
    funnel: FunnelStage[];
}>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: dashboard() }] } });

const stages = [
    { status: 'probing', label: 'Probing', color: 'bg-[#14A2BA]', hex: '#14A2BA' },
    { status: 'penawaran', label: 'Penawaran', color: 'bg-[#125D72]', hex: '#125D72' },
    { status: 'spk', label: 'SPK', color: 'bg-[#D5C900]', hex: '#D5C900' },
    { status: 'bast', label: 'BAST', color: 'bg-[#DA2E09]', hex: '#DA2E09' },
    { status: 'tagihan_mitra', label: 'Tagihan Mitra', color: 'bg-sky-600', hex: '#0284C7' },
    { status: 'selesai', label: 'Selesai', color: 'bg-emerald-500', hex: '#10B981' },
    { status: 'gagal_wo', label: 'Gagal WO', color: 'bg-slate-400', hex: '#94A3B8' },
] as const;

const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const todayLabel = new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
}).format(new Date());
const money = (value: number, compact = false) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
        notation: compact ? 'compact' : 'standard',
    }).format(value || 0);
const statusMeta = (status: string) => stages.find((stage) => stage.status === status) ?? stages[0];
const totalTracked = computed(() => Object.values(props.statusCounts).reduce((total, count) => total + count, 0));
const totalValue = computed(() => Object.values(props.statusValues).reduce((total, value) => total + value, 0));
const distribution = computed(() =>
    stages.map((stage) => {
        const count = props.statusCounts[stage.status] ?? 0;

        return {
            ...stage,
            count,
            value: props.statusValues[stage.status] ?? 0,
            percentage: totalTracked.value ? (count / totalTracked.value) * 100 : 0,
        };
    }),
);
const periodLabel = computed(() => {
    if (!props.filters.date_from && !props.filters.date_to) {
        return 'Seluruh tanggal probing';
    }

    const format = (date: string) =>
        new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(`${date}T00:00:00`));

    if (props.filters.date_from && props.filters.date_to) {
        return `${format(props.filters.date_from)} – ${format(props.filters.date_to)}`;
    }

    return props.filters.date_from ? `Mulai ${format(props.filters.date_from)}` : `Sampai ${format(props.filters.date_to!)}`;
});
const applyFilter = () => {
    router.get(
        '/dashboard',
        {
            ...(dateFrom.value ? { date_from: dateFrom.value } : {}),
            ...(dateTo.value ? { date_to: dateTo.value } : {}),
        },
        { preserveScroll: true, replace: true },
    );
};
const resetFilter = () => {
    dateFrom.value = '';
    dateTo.value = '';
    router.get('/dashboard', {}, { preserveScroll: true, replace: true });
};
const projectLink = (status: string) => {
    const params = new URLSearchParams({ status });

    if (props.filters.date_from) {
        params.set('date_from', props.filters.date_from);
    }

    if (props.filters.date_to) {
        params.set('date_to', props.filters.date_to);
    }

    return `/projects?${params.toString()}`;
};
</script>

<template>
    <Head title="Dashboard Monitoring" />
    <main class="min-h-full bg-[#F5F8F9] p-4 md:p-7">
        <div class="mx-auto max-w-[1500px] space-y-5">
            <header class="rounded-2xl border border-slate-200 bg-white px-5 py-5 shadow-sm md:px-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="flex items-center gap-2 text-xs font-medium text-slate-400"><CalendarDays class="size-3.5" /> {{ todayLabel }}</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-[#125D72]">Monitoring E-Solution</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ periodLabel }} · {{ totalTracked }} project terpantau</p>
                </div>
                <div class="flex gap-2">
                    <Button as-child variant="outline" class="h-10 border-[#125D72]/20 text-[#125D72]">
                        <Link href="/projects"><FolderKanban class="size-4" /> Buka workspace</Link>
                    </Button>
                    <Button as-child class="h-10 bg-[#125D72] hover:bg-[#0D4858]">
                        <Link href="/projects/create"><Plus class="size-4" /> Project Baru</Link>
                    </Button>
                </div>
                </div>
                <form class="mt-5 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end" @submit.prevent="applyFilter">
                    <label class="grid gap-1.5 text-xs font-semibold text-slate-600">
                        Tanggal awal
                        <input v-model="dateFrom" type="date" :max="dateTo || undefined" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm font-normal text-slate-700 outline-none transition focus:border-[#14A2BA] focus:ring-2 focus:ring-[#14A2BA]/15" />
                    </label>
                    <label class="grid gap-1.5 text-xs font-semibold text-slate-600">
                        Tanggal akhir
                        <input v-model="dateTo" type="date" :min="dateFrom || undefined" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm font-normal text-slate-700 outline-none transition focus:border-[#14A2BA] focus:ring-2 focus:ring-[#14A2BA]/15" />
                    </label>
                    <div class="flex gap-2">
                        <Button type="submit" class="h-10 flex-1 bg-[#14A2BA] hover:bg-[#11899E] sm:flex-none">
                            <Filter class="size-4" /> Terapkan
                        </Button>
                        <Button v-if="dateFrom || dateTo" type="button" variant="outline" class="h-10" aria-label="Reset filter tanggal" @click="resetFilter">
                            <RotateCcw class="size-4" />
                        </Button>
                    </div>
                </form>
            </header>

            <section class="grid gap-5 xl:grid-cols-[1fr_1fr]">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-[#14A2BA]">Konversi pipeline</p>
                            <h2 class="mt-1 text-lg font-semibold text-slate-900">Project yang mencapai setiap tahap</h2>
                        </div>
                        <TrendingUp class="size-5 text-[#125D72]" />
                    </div>
                    <div class="mt-6 space-y-4">
                        <div v-for="stage in funnel" :key="stage.status" class="grid grid-cols-[110px_1fr_74px] items-center gap-3">
                            <div>
                                <p class="text-xs font-semibold text-slate-700">{{ statusMeta(stage.status).label }}</p>
                                <p class="mt-0.5 text-[11px] text-slate-400">{{ stage.count }} project</p>
                            </div>
                            <div class="h-8 overflow-hidden rounded-lg bg-slate-100">
                                <div
                                    class="flex h-full items-center justify-end rounded-lg px-2 transition-all"
                                    :class="[statusMeta(stage.status).color, stage.conversion > 0 ? 'min-w-8' : '']"
                                    :style="{ width: stage.conversion > 0 ? `${Math.max(stage.conversion, 4)}%` : '0' }"
                                >
                                    <span v-if="stage.conversion >= 18" class="text-[10px] font-bold text-white">{{ stage.conversion }}%</span>
                                </div>
                            </div>
                            <p class="text-right text-xs font-semibold text-slate-700">{{ stage.conversion }}%</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-[#14A2BA]">Distribusi saat ini</p>
                            <h2 class="mt-1 text-lg font-semibold text-slate-900">Sebaran project per tahap</h2>
                            <p class="mt-1 text-xs text-slate-400">{{ periodLabel }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-semibold text-[#125D72]">{{ totalTracked }}</p>
                            <p class="text-[11px] text-slate-400">{{ money(totalValue, true) }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex h-5 overflow-hidden rounded-full bg-slate-100" aria-label="Proporsi jumlah project per tahap">
                        <div
                            v-for="stage in distribution"
                            v-show="stage.count > 0"
                            :key="stage.status"
                            class="h-full border-r border-white/50 last:border-0"
                            :style="{ width: `${stage.percentage}%`, backgroundColor: stage.hex }"
                            :title="`${stage.label}: ${stage.count} project (${stage.percentage.toFixed(1)}%)`"
                        />
                    </div>

                    <div class="mt-6 space-y-4">
                        <Link
                            v-for="stage in distribution"
                            :key="stage.status"
                            :href="projectLink(stage.status)"
                            class="group block rounded-xl transition"
                        >
                            <div class="mb-1.5 flex items-end justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="size-2.5 rounded-full" :class="stage.color" />
                                    <p class="text-sm font-semibold text-slate-700 group-hover:text-[#125D72]">{{ stage.label }}</p>
                                </div>
                                <div class="flex items-center gap-2 text-right">
                                    <span class="text-xs text-slate-400">{{ stage.percentage.toFixed(1) }}%</span>
                                    <span class="min-w-7 text-sm font-semibold text-slate-900">{{ stage.count }}</span>
                                    <ArrowRight class="size-3.5 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-[#14A2BA]" />
                                </div>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full transition-all" :style="{ width: `${stage.percentage}%`, backgroundColor: stage.hex }" />
                            </div>
                            <p class="mt-1.5 text-right text-[11px] text-slate-400">{{ money(stage.value, true) }}</p>
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>
