<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CmvBadge from '@/Components/CmvBadge.vue';
import FadeIn from '@/Components/FadeIn.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    month_revenue_cents: Number,
    month_profit_cents: Number,
    month_profit_percent: Number,
    month_closing_count: Number,
    month_card_sales_cents: Number,
    month_cash_sales_cents: Number,
    pending_bills_total_cents: Number,
    bills_overdue_count: Number,
    upcoming_bills: Object,
    forecast: Object,
    cmv: Object,
    daily_revenue: Array,
    daily_bills: Array,
    last_closing: Object,
    current_month: String,
});

const { formatMoney } = useMoney();

// ── Helpers ─────────────────────────────────────────────────────────
function daysUntilDue(dueDateStr) {
    if (!dueDateStr) return null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const due = new Date(dueDateStr + 'T00:00:00');
    return Math.ceil((due - today) / (1000 * 60 * 60 * 24));
}

function formatShortDate(dateStr) {
    const [year, month, day] = dateStr.split('-');
    return `${day}/${month}`;
}

function formatDate(dateStr) {
    if (!dateStr) return '\u2014';
    const [year, month, day] = dateStr.split('-');
    return `${day}/${month}/${year}`;
}

function billTypeClass(type) {
    const map = {
        fixed: 'bg-indigo-50 text-indigo-700',
        supplier: 'bg-amber-50 text-amber-700',
        personal: 'bg-violet-50 text-violet-700',
        tax: 'bg-red-50 text-red-700',
        other: 'bg-gray-100 text-gray-500',
    };
    return map[type] || 'bg-gray-100 text-gray-500';
}

function payBill(billId) {
    router.patch(route('contas.pay', billId), {}, {
        preserveScroll: true,
    });
}

// ── CMV product health bar total ────────────────────────────────────
const totalProductsWithCmv = computed(() => {
    const h = props.cmv?.health_counts;
    if (!h) return 0;
    return h.healthy + h.tight + h.critical;
});

// ── CMV products sorted: critical first, then tight, then healthy ──
const sortedCmvProducts = computed(() => {
    if (!props.cmv?.products) return [];
    const order = { critical: 0, tight: 1, healthy: 2, unknown: 3 };
    return [...props.cmv.products].sort((a, b) => (order[a.health] ?? 3) - (order[b.health] ?? 3));
});

const criticalAndTightProducts = computed(() =>
    sortedCmvProducts.value.filter(p => p.health === 'critical' || p.health === 'tight'),
);

const showAllCmv = ref(false);

const visibleCmvProducts = computed(() => {
    if (showAllCmv.value) return sortedCmvProducts.value;
    if (criticalAndTightProducts.value.length > 0) return criticalAndTightProducts.value;
    return sortedCmvProducts.value.slice(0, 5);
});

const hasHiddenCmvProducts = computed(() =>
    visibleCmvProducts.value.length < sortedCmvProducts.value.length,
);

// ── Revenue vs Expenses chart ───────────────────────────────────────
const allDates = computed(() => {
    const dateSet = new Set();
    (props.daily_revenue || []).forEach(d => dateSet.add(d.date));
    (props.daily_bills || []).forEach(d => dateSet.add(d.date));
    return [...dateSet].sort();
});

function revenueOnDate(date) {
    return (props.daily_revenue || []).find(d => d.date === date)?.total_revenue_cents || 0;
}
function cardSalesOnDate(date) {
    return (props.daily_revenue || []).find(d => d.date === date)?.card_sales_cents || 0;
}
function cashSalesOnDate(date) {
    return (props.daily_revenue || []).find(d => d.date === date)?.cash_sales_cents || 0;
}
function billsOnDate(date) {
    return (props.daily_bills || []).find(d => d.date === date)?.total_cents || 0;
}

const chartMax = computed(() => {
    if (allDates.value.length === 0) return 1;
    let max = 0;
    allDates.value.forEach(d => {
        max = Math.max(max, revenueOnDate(d), billsOnDate(d));
    });
    return max || 1;
});

function buildPath(valueFn) {
    const dates = allDates.value;
    if (dates.length === 0) return '';
    const width = 800;
    const height = 200;
    const padding = 10;
    const usableWidth = width - padding * 2;
    const usableHeight = height - padding * 2;
    const step = dates.length > 1 ? usableWidth / (dates.length - 1) : 0;

    return dates.map((date, i) => {
        const x = padding + i * step;
        const val = valueFn(date);
        const y = padding + usableHeight - (val / chartMax.value) * usableHeight;
        return `${i === 0 ? 'M' : 'L'}${x.toFixed(1)},${y.toFixed(1)}`;
    }).join(' ');
}

const revenuePath = computed(() => buildPath(revenueOnDate));
const cardSalesPath = computed(() => buildPath(cardSalesOnDate));
const cashSalesPath = computed(() => buildPath(cashSalesOnDate));
const billsPath = computed(() => buildPath(billsOnDate));

const labelStep = computed(() => {
    const len = allDates.value.length;
    if (len <= 10) return 1;
    if (len <= 20) return 2;
    return Math.ceil(len / 10);
});

// ── Forecast ────────────────────────────────────────────────────────
const forecastCoverage = computed(() => {
    if (!props.pending_bills_total_cents || props.pending_bills_total_cents <= 0) return 0;
    if (!props.month_profit_cents || props.month_profit_cents <= 0) return 0;
    return Math.min(100, Math.max(0, (props.month_profit_cents / props.pending_bills_total_cents) * 100));
});

const forecastBarColor = computed(() => {
    if (forecastCoverage.value >= 100) return 'bg-emerald-500';
    if (forecastCoverage.value >= 50) return 'bg-amber-500';
    return 'bg-red-500';
});
</script>

<template>
    <AppLayout>
        <Head title="Painel" />

        <FadeIn>
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-[#1e293b]">Painel</h1>
                <p class="mt-1 text-sm text-gray-500">{{ current_month }} — resumo operacional</p>
            </div>
        </FadeIn>

        <!-- Row 1: Monthly snapshot cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Card 1: Receita do Mes -->
            <FadeIn :delay="80">
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-wider text-gray-400">Receita do Mes</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ formatMoney(month_revenue_cents) }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ month_closing_count }} fechamento{{ month_closing_count !== 1 ? 's' : '' }}</p>
                    <div class="mt-2 flex items-center gap-3 text-xs text-gray-400">
                        <span class="flex items-center gap-1">
                            <span class="inline-block h-2 w-2 rounded-full bg-violet-400"></span>
                            Cartao {{ formatMoney(month_card_sales_cents) }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="inline-block h-2 w-2 rounded-full bg-emerald-400"></span>
                            Dinheiro {{ formatMoney(month_cash_sales_cents) }}
                        </span>
                    </div>
                </div>
            </FadeIn>

            <!-- Card 2: Lucro do Mes -->
            <FadeIn :delay="160">
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-wider text-gray-400">Lucro do Mes</p>
                    <p
                        class="mt-2 text-3xl font-bold"
                        :class="month_profit_cents >= 0 ? 'text-emerald-600' : 'text-red-600'"
                    >
                        {{ formatMoney(month_profit_cents) }}
                    </p>
                    <p v-if="month_profit_percent !== null" class="mt-1 text-sm text-gray-500">
                        {{ month_profit_percent }}% da receita
                    </p>
                    <p v-else class="mt-1 text-sm text-gray-400">Sem receita ainda</p>
                </div>
            </FadeIn>

            <!-- Card 3: Contas Pendentes -->
            <FadeIn :delay="240">
                <Link
                    :href="route('contas.index')"
                    class="group block rounded-xl bg-white p-5 shadow-sm transition hover:shadow-md"
                    :class="bills_overdue_count > 0 ? 'ring-2 ring-red-200' : ''"
                >
                    <p class="text-sm font-semibold uppercase tracking-wider text-gray-400">Contas Pendentes</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ formatMoney(pending_bills_total_cents) }}</p>
                    <p v-if="bills_overdue_count > 0" class="mt-1 text-sm font-semibold text-red-600">
                        {{ bills_overdue_count }} vencida{{ bills_overdue_count !== 1 ? 's' : '' }}!
                    </p>
                    <p v-else class="mt-1 text-sm text-gray-500 group-hover:text-indigo-600 transition">Ver detalhes</p>
                </Link>
            </FadeIn>

            <!-- Card 4: CMV Medio -->
            <FadeIn :delay="320">
                <Link
                    :href="route('produtos.index')"
                    class="group block rounded-xl bg-white p-5 shadow-sm transition hover:shadow-md"
                >
                    <p class="text-sm font-semibold uppercase tracking-wider text-gray-400">CMV Medio</p>
                    <div class="mt-2 flex items-end gap-3">
                        <span class="text-3xl font-bold text-gray-900">
                            {{ cmv.average_percent !== null ? cmv.average_percent + '%' : '\u2014' }}
                        </span>
                        <CmvBadge v-if="cmv.average_percent !== null" :percent="cmv.average_percent" />
                    </div>
                    <!-- Mini health bar -->
                    <div v-if="totalProductsWithCmv > 0" class="mt-3 flex h-2 overflow-hidden rounded-full bg-gray-100">
                        <div
                            class="bg-emerald-400 transition-all duration-500"
                            :style="{ width: (cmv.health_counts.healthy / totalProductsWithCmv * 100) + '%' }"
                        ></div>
                        <div
                            class="bg-amber-400 transition-all duration-500"
                            :style="{ width: (cmv.health_counts.tight / totalProductsWithCmv * 100) + '%' }"
                        ></div>
                        <div
                            class="bg-red-400 transition-all duration-500"
                            :style="{ width: (cmv.health_counts.critical / totalProductsWithCmv * 100) + '%' }"
                        ></div>
                    </div>
                    <div class="mt-2 flex items-center gap-3 text-xs text-gray-400">
                        <span class="flex items-center gap-1">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            {{ cmv.health_counts.healthy }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                            {{ cmv.health_counts.tight }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="h-2 w-2 rounded-full bg-red-400"></span>
                            {{ cmv.health_counts.critical }}
                        </span>
                    </div>
                </Link>
            </FadeIn>
        </div>

        <!-- Row 2: Chart + Upcoming Bills -->
        <div class="mt-8 grid gap-6 lg:grid-cols-5">
            <!-- Left: Chart (3/5) -->
            <FadeIn :delay="400" class="lg:col-span-3">
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Vendas vs Despesas — Ultimos 30 dias</h2>

                    <template v-if="allDates.length > 0">
                        <svg viewBox="0 0 800 200" class="w-full" preserveAspectRatio="none">
                            <path :d="revenuePath" fill="none" stroke="#4338ca" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path :d="cardSalesPath" fill="none" stroke="#7c3aed" stroke-width="1.5" stroke-dasharray="6,4" stroke-linecap="round" stroke-linejoin="round" />
                            <path :d="cashSalesPath" fill="none" stroke="#059669" stroke-width="1.5" stroke-dasharray="6,4" stroke-linecap="round" stroke-linejoin="round" />
                            <path :d="billsPath" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>

                        <div class="mt-2 flex justify-between px-2">
                            <span
                                v-for="(date, i) in allDates"
                                :key="'lbl-' + date"
                                class="text-[10px] text-gray-400"
                                :class="{ 'hidden': i % labelStep !== 0 && i !== allDates.length - 1 }"
                            >
                                {{ formatShortDate(date) }}
                            </span>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-center gap-5">
                            <div class="flex items-center gap-2">
                                <span class="h-0.5 w-5 rounded bg-indigo-700"></span>
                                <span class="text-xs text-gray-500">Receita Total</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-0.5 w-5 rounded border-t-2 border-dashed border-violet-600"></span>
                                <span class="text-xs text-gray-500">Cartao</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-0.5 w-5 rounded border-t-2 border-dashed border-emerald-600"></span>
                                <span class="text-xs text-gray-500">Dinheiro</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-0.5 w-5 rounded bg-red-600"></span>
                                <span class="text-xs text-gray-500">Despesas</span>
                            </div>
                        </div>
                    </template>

                    <div v-else class="py-12 text-center text-sm text-gray-400">
                        Nenhum dado nos ultimos 30 dias
                    </div>

                    <!-- Last closing summary -->
                    <div v-if="last_closing" class="mt-4 border-t border-gray-100 pt-3">
                        <Link :href="route('fechamentos.index')" class="text-sm text-gray-500 hover:text-indigo-600 transition">
                            Ultimo fechamento: {{ formatShortDate(last_closing.date) }} — {{ formatMoney(last_closing.total_revenue_cents) }}
                        </Link>
                    </div>
                </div>
            </FadeIn>

            <!-- Right: Upcoming Bills (2/5) -->
            <FadeIn :delay="480" class="lg:col-span-2">
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Proximos Vencimentos</h2>

                    <template v-if="upcoming_bills.data && upcoming_bills.data.length > 0">
                        <div class="space-y-3">
                            <div
                                v-for="bill in upcoming_bills.data"
                                :key="bill.id"
                                class="rounded-lg border border-gray-100 p-3"
                                :class="daysUntilDue(bill.due_date) !== null && daysUntilDue(bill.due_date) < 0 ? 'border-red-200 bg-red-50/50' : ''"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="truncate text-sm font-medium text-gray-900">{{ bill.description }}</span>
                                            <span class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="billTypeClass(bill.bill_type)">
                                                {{ bill.bill_type_label }}
                                            </span>
                                        </div>
                                        <p class="mt-0.5 text-xs" :class="daysUntilDue(bill.due_date) !== null && daysUntilDue(bill.due_date) < 0 ? 'font-semibold text-red-600' : 'text-gray-400'">
                                            <template v-if="daysUntilDue(bill.due_date) === null">Sem vencimento</template>
                                            <template v-else-if="daysUntilDue(bill.due_date) < 0">Vencida!</template>
                                            <template v-else-if="daysUntilDue(bill.due_date) === 0">Vence hoje</template>
                                            <template v-else>Vence em {{ daysUntilDue(bill.due_date) }} dia{{ daysUntilDue(bill.due_date) !== 1 ? 's' : '' }}</template>
                                        </p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <span class="text-sm font-semibold text-gray-900">{{ formatMoney(bill.amount_cents) }}</span>
                                        <button
                                            @click="payBill(bill.id)"
                                            class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                        >
                                            Pagar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <Link :href="route('contas.index')" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition">
                                Ver todas as contas &rarr;
                            </Link>
                        </div>
                    </template>

                    <div v-else class="py-8 text-center">
                        <svg class="mx-auto h-10 w-10 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-sm font-medium text-emerald-600">Todas as contas estao em dia!</p>
                    </div>
                </div>
            </FadeIn>
        </div>

        <!-- Row 3: Forecast (only if pending bills) -->
        <FadeIn v-if="pending_bills_total_cents > 0" :delay="560">
            <div class="mt-8 rounded-xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Previsao Financeira</h2>

                <!-- Progress bar -->
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="text-gray-500">Cobertura das contas pendentes</span>
                    <span class="font-semibold" :class="forecastCoverage >= 100 ? 'text-emerald-600' : forecastCoverage >= 50 ? 'text-amber-600' : 'text-red-600'">
                        {{ Math.round(forecastCoverage) }}% coberto
                    </span>
                </div>
                <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100">
                    <div
                        class="h-full rounded-full transition-all duration-700"
                        :class="forecastBarColor"
                        :style="{ width: forecastCoverage + '%' }"
                    ></div>
                </div>

                <!-- Stats -->
                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="text-center">
                        <p class="text-xs uppercase tracking-wider text-gray-400">Media diaria</p>
                        <p class="mt-1 text-lg font-bold text-gray-900">{{ formatMoney(forecast.daily_avg_cents) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs uppercase tracking-wider text-gray-400">Dias p/ cobrir</p>
                        <p class="mt-1 text-lg font-bold text-gray-900">{{ forecast.days_to_cover_bills ?? '\u2014' }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs uppercase tracking-wider text-gray-400">Falta receber</p>
                        <p class="mt-1 text-lg font-bold text-gray-900">{{ formatMoney(forecast.revenue_needed_cents) }}</p>
                    </div>
                </div>
            </div>
        </FadeIn>

        <!-- Row 4: CMV por Produto -->
        <FadeIn v-if="cmv.products.length > 0" :delay="640">
            <div class="mt-8 rounded-xl bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400">CMV por Produto</h2>
                    <button
                        v-if="hasHiddenCmvProducts || showAllCmv"
                        @click="showAllCmv = !showAllCmv"
                        class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition"
                    >
                        {{ showAllCmv ? 'Mostrar menos' : 'Ver todos (' + sortedCmvProducts.length + ')' }}
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Produto</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Preco de Venda</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">CMV</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">CMV %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="product in visibleCmvProducts"
                                :key="product.name"
                                class="transition hover:bg-gray-50"
                                :class="product.health === 'critical' ? 'border-l-4 border-l-red-400' : product.health === 'tight' ? 'border-l-4 border-l-amber-400' : ''"
                            >
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ product.name }}</td>
                                <td class="px-6 py-3 text-right text-sm text-gray-700">{{ formatMoney(product.sale_price_cents) }}</td>
                                <td class="px-6 py-3 text-right text-sm text-gray-700">{{ formatMoney(product.cmv_cents) }}</td>
                                <td class="px-6 py-3 text-center">
                                    <CmvBadge :percent="product.cmv_percent" size="sm" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </FadeIn>

        <!-- Empty state (no products at all) -->
        <FadeIn v-else :delay="640">
            <div class="mt-8 rounded-xl border-2 border-dashed border-gray-200 bg-white p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
                <h3 class="mt-3 text-sm font-semibold text-gray-900">Nenhum produto cadastrado</h3>
                <p class="mt-1 text-sm text-gray-500">Comece cadastrando ingredientes, receitas e depois crie seus produtos.</p>
                <Link
                    :href="route('ingredientes.create')"
                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800"
                >
                    Comecar
                </Link>
            </div>
        </FadeIn>
    </AppLayout>
</template>
