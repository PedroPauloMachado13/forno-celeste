<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FadeIn from '@/Components/FadeIn.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    revenue: Object,
    costs: Object,
    profit_cents: Number,
    profit_percent: Number,
    break_even: Object,
    comparison: Object,
    best_day: Object,
    worst_day: Object,
    holidays_in_period: Array,
    forecast: Object,
    upcoming_bills: Object,
    from: String,
    to: String,
});

const { formatMoney } = useMoney();

const dateFrom = ref(props.from);
const dateTo = ref(props.to);

const presets = [
    { label: 'Ultimos 7 dias', days: 7 },
    { label: 'Ultimos 30 dias', days: 30 },
    { label: 'Este Mes', days: 0 },
];

function applyPreset(preset) {
    const now = new Date();
    if (preset.days === 0) {
        const first = new Date(now.getFullYear(), now.getMonth(), 1);
        dateFrom.value = formatIsoDate(first);
        dateTo.value = formatIsoDate(now);
    } else {
        const past = new Date(now);
        past.setDate(past.getDate() - preset.days + 1);
        dateFrom.value = formatIsoDate(past);
        dateTo.value = formatIsoDate(now);
    }
    applyFilter();
}

function applyFilter() {
    router.get(route('financeiro.dashboard'), {
        from: dateFrom.value,
        to: dateTo.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function formatIsoDate(date) {
    return date.toISOString().split('T')[0];
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const [year, month, day] = dateStr.split('-');
    return `${day}/${month}/${year}`;
}

function formatPercent(value) {
    if (value === null || value === undefined) return '0%';
    return (value >= 0 ? '+' : '') + value.toFixed(1) + '%';
}

function billTypeBadgeClass(billType) {
    const map = {
        fixed: 'bg-indigo-50 text-indigo-700',
        supplier: 'bg-amber-50 text-amber-700',
        personal: 'bg-violet-50 text-violet-700',
        tax: 'bg-red-50 text-red-700',
        other: 'bg-gray-100 text-gray-500',
    };
    return map[billType] || 'bg-gray-100 text-gray-500';
}

function daysUntilDue(dueDateStr) {
    if (!dueDateStr) return null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const due = new Date(dueDateStr + 'T00:00:00');
    const diff = Math.ceil((due - today) / (1000 * 60 * 60 * 24));
    return diff;
}

function daysUntilDueLabel(dueDateStr) {
    const days = daysUntilDue(dueDateStr);
    if (days === null) return 'Sem vencimento';
    if (days < 0) return 'Vencida!';
    if (days === 0) return 'Vence hoje';
    if (days === 1) return 'Amanha';
    return `${days} dias`;
}

// Color palette for pie slices — cycles for many items
const palette = [
    { bg: 'bg-indigo-500', hex: '#6366f1' },
    { bg: 'bg-emerald-500', hex: '#10b981' },
    { bg: 'bg-amber-500', hex: '#f59e0b' },
    { bg: 'bg-rose-500', hex: '#f43f5e' },
    { bg: 'bg-violet-500', hex: '#8b5cf6' },
    { bg: 'bg-sky-500', hex: '#0ea5e9' },
    { bg: 'bg-teal-500', hex: '#14b8a6' },
    { bg: 'bg-pink-500', hex: '#ec4899' },
    { bg: 'bg-lime-500', hex: '#84cc16' },
    { bg: 'bg-fuchsia-500', hex: '#d946ef' },
    { bg: 'bg-blue-400', hex: '#60a5fa' },
    { bg: 'bg-red-400', hex: '#f87171' },
];
// Reserved colors for variable costs
const variableColors = {
    waste: { bg: 'bg-orange-400', hex: '#fb923c' },
    card_fee: { bg: 'bg-cyan-500', hex: '#06b6d4' },
};

// Full cost breakdown: individual fixed costs + bill types + waste + card fee
const costBreakdown = computed(() => {
    const total = props.costs.total_cents || 1;
    const items = [];
    let colorIdx = 0;

    // Detailed breakdown from backend (individual fixed costs + other bill types)
    if (props.costs.breakdown) {
        for (const entry of props.costs.breakdown) {
            if (entry.total_cents > 0) {
                const color = palette[colorIdx % palette.length];
                items.push({
                    key: entry.key,
                    label: entry.label,
                    category: entry.category,
                    amount: entry.total_cents,
                    percent: ((entry.total_cents / total) * 100).toFixed(1),
                    bg: color.bg,
                    hex: color.hex,
                });
                colorIdx++;
            }
        }
    }

    // Variable costs: waste + card fee
    if (props.costs.waste_cents > 0) {
        items.push({
            key: 'waste',
            label: 'Perdas',
            category: 'variable',
            amount: props.costs.waste_cents,
            percent: ((props.costs.waste_cents / total) * 100).toFixed(1),
            bg: variableColors.waste.bg,
            hex: variableColors.waste.hex,
        });
    }
    if (props.costs.card_fee_cents > 0) {
        items.push({
            key: 'card_fee',
            label: 'Taxa Cartao',
            category: 'variable',
            amount: props.costs.card_fee_cents,
            percent: ((props.costs.card_fee_cents / total) * 100).toFixed(1),
            bg: variableColors.card_fee.bg,
            hex: variableColors.card_fee.hex,
        });
    }

    // Sort descending by amount
    items.sort((a, b) => b.amount - a.amount);
    return items;
});

// SVG donut chart arcs
const donutArcs = computed(() => {
    const items = costBreakdown.value;
    if (!items.length) return [];

    const radius = 80;
    const cx = 100;
    const cy = 100;
    const circumference = 2 * Math.PI * radius;
    const total = props.costs.total_cents || 1;
    const arcs = [];
    let offset = 0;

    for (const item of items) {
        const pct = item.amount / total;
        const dashLength = pct * circumference;
        arcs.push({
            key: item.key,
            hex: item.hex,
            label: item.label,
            percent: item.percent,
            dashArray: `${dashLength} ${circumference - dashLength}`,
            dashOffset: -offset,
            radius,
            cx,
            cy,
        });
        offset += dashLength;
    }
    return arcs;
});

// Forecast coverage percentage
const forecastCoverage = computed(() => {
    if (!props.forecast || !props.forecast.pending_bills_total_cents || props.forecast.pending_bills_total_cents <= 0) return 0;
    // How much of pending bills is covered by current profit
    const profit = props.profit_cents || 0;
    if (profit <= 0) return 0;
    const pct = (profit / props.forecast.pending_bills_total_cents) * 100;
    return Math.min(pct, 100);
});

// Upcoming bills (max 10)
const upcomingBillsList = computed(() => {
    if (!props.upcoming_bills || !props.upcoming_bills.data) return [];
    return props.upcoming_bills.data.slice(0, 10);
});
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Financeiro" />

        <FadeIn>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-[#1e293b]">Dashboard Financeiro</h1>
                <p class="mt-1 text-sm text-gray-500">Visao geral de receitas, custos e lucro do periodo</p>
            </div>
        </FadeIn>

        <!-- Period selector -->
        <FadeIn :delay="80">
        <div class="mb-6 rounded-xl bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label for="date_from" class="mb-1 block text-xs font-medium text-gray-500">De</label>
                    <input
                        id="date_from"
                        v-model="dateFrom"
                        type="date"
                        @change="applyFilter"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                    />
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-xs font-medium text-gray-500">Ate</label>
                    <input
                        id="date_to"
                        v-model="dateTo"
                        type="date"
                        @change="applyFilter"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                    />
                </div>
                <div class="flex gap-2">
                    <button
                        v-for="preset in presets"
                        :key="preset.label"
                        @click="applyPreset(preset)"
                        class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700"
                    >
                        {{ preset.label }}
                    </button>
                </div>
            </div>
        </div>
        </FadeIn>

        <!-- Summary cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Receita Total -->
            <FadeIn :delay="160">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100">
                        <svg class="h-6 w-6 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatMoney(revenue.total_cents) }}</p>
                        <p class="text-sm text-gray-500">Receita Total</p>
                        <p class="mt-0.5 text-xs text-gray-400">{{ revenue.closing_count }} fechamento(s)</p>
                    </div>
                </div>
            </div>
            </FadeIn>

            <!-- Custos Totais -->
            <FadeIn :delay="240">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-100">
                        <svg class="h-6 w-6 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatMoney(costs.total_cents) }}</p>
                        <p class="text-sm text-gray-500">Custos Totais</p>
                    </div>
                </div>
            </div>
            </FadeIn>

            <!-- Lucro -->
            <FadeIn :delay="320">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl"
                        :class="profit_cents >= 0 ? 'bg-emerald-100' : 'bg-red-100'"
                    >
                        <svg
                            class="h-6 w-6"
                            :class="profit_cents >= 0 ? 'text-emerald-700' : 'text-red-700'"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path v-if="profit_cents >= 0" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-2xl font-bold"
                            :class="profit_cents >= 0 ? 'text-emerald-700' : 'text-red-700'"
                        >
                            {{ formatMoney(profit_cents) }}
                        </p>
                        <p class="text-sm text-gray-500">Lucro</p>
                        <p class="mt-0.5 text-xs" :class="profit_percent >= 0 ? 'text-emerald-600' : 'text-red-600'">
                            {{ profit_percent !== null && profit_percent !== undefined ? profit_percent.toFixed(1) + '%' : '—' }} da receita
                        </p>
                    </div>
                </div>
            </div>
            </FadeIn>

            <!-- Ponto de Equilibrio -->
            <FadeIn :delay="400">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl"
                        :class="break_even.reached ? 'bg-emerald-100' : 'bg-amber-100'"
                    >
                        <svg
                            class="h-6 w-6"
                            :class="break_even.reached ? 'text-emerald-700' : 'text-amber-700'"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-bold" :class="break_even.reached ? 'text-emerald-700' : 'text-amber-700'">
                            {{ break_even.reached ? 'Atingido!' : `Faltam ${break_even.days_remaining} dias` }}
                        </p>
                        <p class="text-sm text-gray-500">Ponto de Equilibrio</p>
                        <p v-if="!break_even.reached && break_even.daily_avg_revenue_cents" class="mt-0.5 text-xs text-gray-400">
                            Media diaria: {{ formatMoney(break_even.daily_avg_revenue_cents) }}
                        </p>
                    </div>
                </div>
            </div>
            </FadeIn>
        </div>

        <!-- Financial Forecast -->
        <FadeIn v-if="forecast && forecast.pending_bills_total_cents > 0" :delay="480">
        <div class="mt-6 rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Previsao Financeira</h2>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-gray-100 p-4">
                    <p class="text-xs font-medium uppercase text-gray-400">Total Contas Pendentes</p>
                    <p class="mt-1 text-xl font-bold text-red-700">{{ formatMoney(forecast.pending_bills_total_cents) }}</p>
                </div>
                <div class="rounded-lg border border-gray-100 p-4">
                    <p class="text-xs font-medium uppercase text-gray-400">Media Diaria de Receita</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">{{ formatMoney(forecast.daily_avg_revenue_cents) }}</p>
                </div>
                <div class="rounded-lg border border-gray-100 p-4">
                    <p class="text-xs font-medium uppercase text-gray-400">Dias para Cobrir Contas</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">{{ forecast.days_to_cover_bills ?? '—' }}</p>
                </div>
                <div class="rounded-lg border border-gray-100 p-4">
                    <p class="text-xs font-medium uppercase text-gray-400">Receita Necessaria</p>
                    <p class="mt-1 text-xl font-bold" :class="forecast.revenue_needed_cents > 0 ? 'text-amber-700' : 'text-emerald-700'">
                        {{ formatMoney(forecast.revenue_needed_cents) }}
                    </p>
                </div>
            </div>

            <!-- Coverage progress bar -->
            <div class="mt-4">
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>Cobertura pelo lucro atual</span>
                    <span class="font-semibold">{{ forecastCoverage.toFixed(0) }}%</span>
                </div>
                <div class="mt-1 h-3 overflow-hidden rounded-full bg-gray-100">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        :class="forecastCoverage >= 100 ? 'bg-emerald-400' : forecastCoverage >= 50 ? 'bg-amber-400' : 'bg-red-400'"
                        :style="{ width: forecastCoverage + '%' }"
                    ></div>
                </div>
            </div>
        </div>
        </FadeIn>

        <!-- Upcoming Bills -->
        <FadeIn v-if="upcomingBillsList.length > 0" :delay="540">
        <div class="mt-6 rounded-xl bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Proximas Contas</h2>
                <Link
                    :href="route('contas.index')"
                    class="text-xs font-medium text-indigo-600 transition hover:text-indigo-800"
                >
                    Ver todas
                </Link>
            </div>

            <div class="space-y-2">
                <Link
                    v-for="bill in upcomingBillsList"
                    :key="bill.id"
                    :href="route('contas.show', bill.id)"
                    class="flex items-center justify-between rounded-lg border border-gray-100 px-4 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/30"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-900">{{ bill.description }}</p>
                            <div class="mt-0.5 flex items-center gap-2">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="billTypeBadgeClass(bill.bill_type)"
                                >
                                    {{ bill.bill_type_label || 'Outro' }}
                                </span>
                                <span class="text-xs text-gray-400">{{ formatDate(bill.due_date) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="ml-4 flex shrink-0 items-center gap-3">
                        <span
                            class="text-xs font-semibold"
                            :class="daysUntilDue(bill.due_date) === null ? 'text-gray-400' : daysUntilDue(bill.due_date) < 0 ? 'text-red-600' : daysUntilDue(bill.due_date) <= 3 ? 'text-amber-600' : 'text-gray-500'"
                        >
                            {{ daysUntilDueLabel(bill.due_date) }}
                        </span>
                        <span class="text-sm font-bold text-gray-900">{{ formatMoney(bill.amount_cents) }}</span>
                    </div>
                </Link>
            </div>
        </div>
        </FadeIn>

        <!-- Cost breakdown pie chart -->
        <FadeIn :delay="600">
        <div class="mt-6 rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Composicao dos Custos</h2>

            <div v-if="costs.total_cents > 0" class="flex flex-col items-center gap-8 lg:flex-row lg:items-start">
                <!-- Donut chart -->
                <div class="relative shrink-0">
                    <svg viewBox="0 0 200 200" class="h-56 w-56">
                        <circle
                            v-for="arc in donutArcs"
                            :key="arc.key"
                            :r="arc.radius"
                            :cx="arc.cx"
                            :cy="arc.cy"
                            fill="none"
                            :stroke="arc.hex"
                            stroke-width="32"
                            :stroke-dasharray="arc.dashArray"
                            :stroke-dashoffset="arc.dashOffset"
                            transform="rotate(-90 100 100)"
                            class="transition-all duration-700"
                        />
                        <!-- Center hole -->
                        <circle cx="100" cy="100" r="58" fill="white" />
                    </svg>
                    <!-- Center text -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-lg font-bold text-gray-900">{{ formatMoney(costs.total_cents) }}</span>
                        <span class="text-[10px] uppercase text-gray-400">Total</span>
                    </div>
                </div>

                <!-- Legend list -->
                <div class="w-full flex-1 space-y-3">
                    <div
                        v-for="item in costBreakdown"
                        :key="'legend-' + item.key"
                        class="flex items-center justify-between rounded-lg border border-gray-50 px-3 py-2.5 transition hover:bg-gray-50"
                    >
                        <div class="flex items-center gap-3">
                            <span class="h-3.5 w-3.5 shrink-0 rounded-full" :class="item.bg"></span>
                            <span class="text-sm font-medium text-gray-700">{{ item.label }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-semibold text-gray-900">{{ formatMoney(item.amount) }}</span>
                            <span class="min-w-[3rem] text-right text-xs font-medium text-gray-400">{{ item.percent }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="py-8 text-center">
                <p class="text-sm text-gray-400">Nenhum custo registrado no periodo.</p>
            </div>
        </div>
        </FadeIn>

        <!-- Period comparison -->
        <FadeIn :delay="680">
        <div class="mt-6 rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Comparacao com Periodo Anterior</h2>

            <div class="grid gap-6 sm:grid-cols-2">
                <!-- Revenue comparison -->
                <div class="rounded-lg border border-gray-100 p-4">
                    <p class="mb-2 text-xs font-medium uppercase text-gray-400">Receita</p>
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-lg font-bold text-gray-900">{{ formatMoney(comparison.current.revenue_cents) }}</p>
                            <p class="text-xs text-gray-400">Anterior: {{ formatMoney(comparison.previous.revenue_cents) }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <svg
                                v-if="comparison.revenue_change_percent >= 0"
                                class="h-4 w-4 text-emerald-500"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <svg
                                v-else
                                class="h-4 w-4 text-red-500"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            <span
                                class="text-sm font-semibold"
                                :class="comparison.revenue_change_percent >= 0 ? 'text-emerald-600' : 'text-red-600'"
                            >
                                {{ formatPercent(comparison.revenue_change_percent) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Profit comparison -->
                <div class="rounded-lg border border-gray-100 p-4">
                    <p class="mb-2 text-xs font-medium uppercase text-gray-400">Lucro</p>
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-lg font-bold text-gray-900">{{ formatMoney(comparison.current.profit_cents) }}</p>
                            <p class="text-xs text-gray-400">Anterior: {{ formatMoney(comparison.previous.profit_cents) }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <svg
                                v-if="comparison.profit_change_percent >= 0"
                                class="h-4 w-4 text-emerald-500"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <svg
                                v-else
                                class="h-4 w-4 text-red-500"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            <span
                                class="text-sm font-semibold"
                                :class="comparison.profit_change_percent >= 0 ? 'text-emerald-600' : 'text-red-600'"
                            >
                                {{ formatPercent(comparison.profit_change_percent) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </FadeIn>

        <!-- Best & worst days -->
        <FadeIn v-if="best_day || worst_day" :delay="760">
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div v-if="best_day" class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-gray-400">Melhor Dia</p>
                        <p class="text-lg font-bold text-gray-900">{{ formatMoney(best_day.total_revenue_cents) }}</p>
                        <p class="text-xs text-gray-500">{{ formatDate(best_day.date) }}</p>
                    </div>
                </div>
            </div>
            <div v-if="worst_day" class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-100">
                        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-gray-400">Pior Dia</p>
                        <p class="text-lg font-bold text-gray-900">{{ formatMoney(worst_day.total_revenue_cents) }}</p>
                        <p class="text-xs text-gray-500">{{ formatDate(worst_day.date) }}</p>
                    </div>
                </div>
            </div>
        </div>
        </FadeIn>

        <!-- Holidays in period -->
        <FadeIn v-if="holidays_in_period && holidays_in_period.length > 0" :delay="840">
        <div class="mt-6 rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Feriados no Periodo</h2>
            <div class="space-y-2">
                <div
                    v-for="holiday in holidays_in_period"
                    :key="holiday.date"
                    class="flex items-center justify-between rounded-lg border border-gray-100 px-4 py-2.5"
                >
                    <div class="flex items-center gap-3">
                        <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ holiday.name }}</p>
                            <p class="text-xs text-gray-400">{{ formatDate(holiday.date) }}</p>
                        </div>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                        :class="holiday.was_open
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-gray-100 text-gray-500'"
                    >
                        {{ holiday.was_open ? 'Aberto' : 'Fechado' }}
                    </span>
                </div>
            </div>
        </div>
        </FadeIn>

        <!-- Empty state -->
        <FadeIn v-if="revenue.closing_count === 0" :delay="480">
            <div class="mt-6 rounded-xl border-2 border-dashed border-gray-200 bg-white p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-3 text-sm font-semibold text-gray-900">Nenhum dado financeiro no periodo</h3>
                <p class="mt-1 text-sm text-gray-500">Registre fechamentos diarios para acompanhar suas financas.</p>
            </div>
        </FadeIn>
    </AppLayout>
</template>
