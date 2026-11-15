<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FadeIn from '@/Components/FadeIn.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    daily_revenue: Array,
    cash_drawer_balance: Array,
    summary: Object,
    from: String,
    to: String,
});

const { formatMoney, centsToReais } = useMoney();

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
        // This month
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
    router.get(route('vendas.dashboard'), {
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

function formatShortDate(dateStr) {
    const [year, month, day] = dateStr.split('-');
    return `${day}/${month}`;
}

// Revenue chart calculations
const maxRevenue = computed(() => {
    if (!props.daily_revenue || props.daily_revenue.length === 0) return 1;
    return Math.max(...props.daily_revenue.map((d) => d.total_revenue_cents), 1);
});

// Cash drawer chart calculations
const cashDrawerValues = computed(() => {
    if (!props.cash_drawer_balance || props.cash_drawer_balance.length === 0) return [];
    return props.cash_drawer_balance.map((d) => d.cash_drawer_cents);
});

const maxCashDrawer = computed(() => {
    if (cashDrawerValues.value.length === 0) return 1;
    return Math.max(...cashDrawerValues.value, 1);
});

const minCashDrawer = computed(() => {
    if (cashDrawerValues.value.length === 0) return 0;
    return Math.min(...cashDrawerValues.value, 0);
});

const cashDrawerRange = computed(() => {
    return maxCashDrawer.value - minCashDrawer.value || 1;
});

// Build SVG line path for cash drawer
const cashDrawerPath = computed(() => {
    if (cashDrawerValues.value.length === 0) return '';
    const width = 800;
    const height = 200;
    const padding = 10;
    const usableWidth = width - padding * 2;
    const usableHeight = height - padding * 2;
    const step = cashDrawerValues.value.length > 1 ? usableWidth / (cashDrawerValues.value.length - 1) : 0;

    return cashDrawerValues.value.map((val, i) => {
        const x = padding + i * step;
        const y = padding + usableHeight - ((val - minCashDrawer.value) / cashDrawerRange.value) * usableHeight;
        return `${i === 0 ? 'M' : 'L'}${x},${y}`;
    }).join(' ');
});

// Build filled area path for cash drawer
const cashDrawerAreaPath = computed(() => {
    if (cashDrawerValues.value.length === 0) return '';
    const width = 800;
    const height = 200;
    const padding = 10;
    const usableWidth = width - padding * 2;
    const usableHeight = height - padding * 2;
    const step = cashDrawerValues.value.length > 1 ? usableWidth / (cashDrawerValues.value.length - 1) : 0;

    let path = cashDrawerValues.value.map((val, i) => {
        const x = padding + i * step;
        const y = padding + usableHeight - ((val - minCashDrawer.value) / cashDrawerRange.value) * usableHeight;
        return `${i === 0 ? 'M' : 'L'}${x},${y}`;
    }).join(' ');

    const lastX = padding + (cashDrawerValues.value.length - 1) * step;
    path += ` L${lastX},${height - padding} L${padding},${height - padding} Z`;
    return path;
});

const statCards = computed(() => [
    {
        label: 'Receita Total',
        value: formatMoney(props.summary.total_revenue_cents),
        color: 'bg-indigo-50 text-indigo-700',
        iconBg: 'bg-indigo-100',
        icon: 'revenue',
    },
    {
        label: 'Vendas Cartao',
        value: formatMoney(props.summary.total_card_sales_cents),
        color: 'bg-violet-50 text-violet-700',
        iconBg: 'bg-violet-100',
        icon: 'card',
    },
    {
        label: 'Vendas Dinheiro',
        value: formatMoney(props.summary.total_cash_sales_cents),
        color: 'bg-emerald-50 text-emerald-700',
        iconBg: 'bg-emerald-100',
        icon: 'cash',
    },
    {
        label: 'Perdas',
        value: formatMoney(props.summary.total_waste_cents),
        color: 'bg-red-50 text-red-700',
        iconBg: 'bg-red-100',
        icon: 'waste',
    },
    {
        label: 'Fechamentos',
        value: props.summary.closing_count,
        color: 'bg-amber-50 text-amber-700',
        iconBg: 'bg-amber-100',
        icon: 'count',
    },
]);
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Vendas" />

        <FadeIn>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-[#1e293b]">Dashboard de Vendas</h1>
                <p class="mt-1 text-sm text-gray-500">Acompanhe a receita e o fluxo de caixa do periodo</p>
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
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <FadeIn
                v-for="(card, i) in statCards"
                :key="card.label"
                :delay="160 + i * 80"
            >
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl" :class="card.iconBg">
                            <svg v-if="card.icon === 'revenue'" class="h-6 w-6" :class="card.color.split(' ')[1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg v-if="card.icon === 'card'" class="h-6 w-6" :class="card.color.split(' ')[1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <svg v-if="card.icon === 'cash'" class="h-6 w-6" :class="card.color.split(' ')[1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <svg v-if="card.icon === 'waste'" class="h-6 w-6" :class="card.color.split(' ')[1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <svg v-if="card.icon === 'count'" class="h-6 w-6" :class="card.color.split(' ')[1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ card.value }}</p>
                            <p class="text-sm text-gray-500">{{ card.label }}</p>
                        </div>
                    </div>
                </div>
            </FadeIn>
        </div>

        <!-- Revenue chart -->
        <FadeIn :delay="500">
        <div class="mt-6 rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Receita Diaria</h2>

            <div v-if="daily_revenue && daily_revenue.length > 0" class="overflow-x-auto">
                <div class="min-w-[600px]">
                    <!-- Stacked bar chart -->
                    <div class="flex items-end gap-1" style="height: 220px;">
                        <div
                            v-for="day in daily_revenue"
                            :key="day.date"
                            class="group relative flex flex-1 flex-col items-center justify-end"
                            style="min-width: 24px;"
                        >
                            <!-- Tooltip -->
                            <div class="pointer-events-none absolute -top-16 left-1/2 z-10 hidden -translate-x-1/2 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg group-hover:block whitespace-nowrap">
                                <div class="font-semibold">{{ formatShortDate(day.date) }}</div>
                                <div class="text-violet-300">Cartao: {{ formatMoney(day.card_sales_cents) }}</div>
                                <div class="text-emerald-300">Dinheiro: {{ formatMoney(day.cash_sales_cents) }}</div>
                                <div class="mt-1 border-t border-gray-700 pt-1 font-semibold">Total: {{ formatMoney(day.total_revenue_cents) }}</div>
                            </div>
                            <!-- Cash sales bar (top of stack) -->
                            <div
                                class="w-full bg-emerald-400 transition-all duration-300 hover:bg-emerald-500"
                                :style="{
                                    height: (day.cash_sales_cents / maxRevenue * 180) + 'px',
                                    borderRadius: day.cash_sales_cents > 0 ? '0.25rem 0.25rem 0 0' : '0',
                                }"
                            ></div>
                            <!-- Card sales bar (bottom of stack) -->
                            <div
                                class="w-full bg-violet-400 transition-all duration-300 hover:bg-violet-500"
                                :style="{
                                    height: (day.card_sales_cents / maxRevenue * 180) + 'px',
                                    borderRadius: day.cash_sales_cents === 0 && day.card_sales_cents > 0 ? '0.25rem 0.25rem 0 0' : '0',
                                }"
                            ></div>
                            <!-- Date label -->
                            <span class="mt-2 text-[10px] text-gray-400 leading-none">{{ formatShortDate(day.date) }}</span>
                        </div>
                    </div>
                    <!-- Legend -->
                    <div class="mt-4 flex items-center justify-center gap-6">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded bg-violet-400"></span>
                            <span class="text-xs text-gray-500">Cartao</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded bg-emerald-400"></span>
                            <span class="text-xs text-gray-500">Dinheiro</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="py-12 text-center">
                <p class="text-sm text-gray-400">Nenhum dado de receita no periodo selecionado.</p>
            </div>
        </div>
        </FadeIn>

        <!-- Cash drawer balance chart -->
        <FadeIn :delay="580">
        <div class="mt-6 rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Saldo do Caixa</h2>

            <div v-if="cash_drawer_balance && cash_drawer_balance.length > 0">
                <svg viewBox="0 0 800 200" class="w-full" preserveAspectRatio="none">
                    <!-- Fill area -->
                    <path
                        :d="cashDrawerAreaPath"
                        fill="url(#cashGradient)"
                        opacity="0.3"
                    />
                    <!-- Line -->
                    <path
                        :d="cashDrawerPath"
                        fill="none"
                        stroke="#6366f1"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <!-- Dots -->
                    <circle
                        v-for="(day, i) in cash_drawer_balance"
                        :key="day.date"
                        :cx="10 + (cash_drawer_balance.length > 1 ? i * (780 / (cash_drawer_balance.length - 1)) : 0)"
                        :cy="10 + 180 - ((day.cash_drawer_cents - minCashDrawer) / cashDrawerRange) * 180"
                        r="4"
                        fill="#6366f1"
                        class="opacity-0 hover:opacity-100 transition-opacity"
                    />
                    <defs>
                        <linearGradient id="cashGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#6366f1" />
                            <stop offset="100%" stop-color="#6366f1" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                </svg>
                <!-- Date labels -->
                <div class="mt-2 flex justify-between px-2">
                    <span v-for="(day, i) in cash_drawer_balance" :key="'label-' + day.date"
                        class="text-[10px] text-gray-400"
                        :class="{ 'hidden': cash_drawer_balance.length > 15 && i % Math.ceil(cash_drawer_balance.length / 10) !== 0 && i !== cash_drawer_balance.length - 1 }"
                    >
                        {{ formatShortDate(day.date) }}
                    </span>
                </div>
                <!-- Summary under chart -->
                <div class="mt-4 flex items-center justify-center gap-6">
                    <div class="text-center">
                        <p class="text-xs text-gray-400">Ultimo saldo</p>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ formatMoney(cash_drawer_balance[cash_drawer_balance.length - 1].cash_drawer_cents) }}
                        </p>
                    </div>
                </div>
            </div>

            <div v-else class="py-12 text-center">
                <p class="text-sm text-gray-400">Nenhum dado de caixa no periodo selecionado.</p>
            </div>
        </div>
        </FadeIn>

        <!-- Empty state when no closings at all -->
        <FadeIn v-if="summary.closing_count === 0" :delay="500">
            <div class="mt-6 rounded-xl border-2 border-dashed border-gray-200 bg-white p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-3 text-sm font-semibold text-gray-900">Nenhum fechamento no periodo</h3>
                <p class="mt-1 text-sm text-gray-500">Registre o fechamento diario para acompanhar suas vendas.</p>
                <Link
                    :href="route('fechamentos.create')"
                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800"
                >
                    Registrar Fechamento
                </Link>
            </div>
        </FadeIn>
    </AppLayout>
</template>
