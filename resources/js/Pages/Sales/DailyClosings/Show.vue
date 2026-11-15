<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FadeIn from '@/Components/FadeIn.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    closing: Object,
});

const { formatMoney } = useMoney();

const c = props.closing.data;

function formatDate(dateStr) {
    const [year, month, day] = dateStr.split('-');
    return `${day}/${month}/${year}`;
}
</script>

<template>
    <AppLayout>
        <Head :title="`Fechamento - ${formatDate(c.closing_date)}`" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('fechamentos.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Fechamentos
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">Fechamento - {{ formatDate(c.closing_date) }}</h1>
            </div>
        </FadeIn>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Financial summary card -->
            <FadeIn :delay="80">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Resumo Financeiro</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Caixa de Abertura</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ formatMoney(c.opening_cash_cents) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Vendas no Cartao</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ formatMoney(c.card_sales_cents) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Vendas em Dinheiro</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ formatMoney(c.cash_sales_cents) }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-3">
                        <dt class="text-base font-bold text-gray-900">Receita Total</dt>
                        <dd class="text-lg font-bold text-indigo-700">{{ formatMoney(c.total_revenue_cents) }}</dd>
                    </div>
                </dl>
            </div>
            </FadeIn>

            <!-- Cash drawer card -->
            <FadeIn :delay="160">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Controle de Caixa</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Dinheiro em Caixa</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ formatMoney(c.cash_drawer_cents) }}</dd>
                    </div>
                    <div v-if="c.total_waste_cents > 0" class="flex justify-between border-t border-gray-100 pt-3">
                        <dt class="text-sm font-semibold text-gray-700">Custo de Perdas</dt>
                        <dd class="text-sm font-bold text-red-600">- {{ formatMoney(c.total_waste_cents) }}</dd>
                    </div>
                </dl>
            </div>
            </FadeIn>
        </div>

        <!-- Withdrawals -->
        <FadeIn v-if="c.withdrawals && c.withdrawals.length > 0" :delay="240">
            <div class="mt-6 rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Saidas de Caixa</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Descricao</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="w in c.withdrawals" :key="w.id" class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ w.description }}</td>
                            <td class="px-6 py-3 text-right text-sm font-medium text-red-600">- {{ formatMoney(w.amount_cents) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </FadeIn>

        <!-- Wastes -->
        <FadeIn v-if="c.wastes && c.wastes.length > 0" :delay="320">
            <div class="mt-6 rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Perdas / Descartes</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Produto</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Quantidade</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Custo Unitario</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Custo Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="w in c.wastes" :key="w.id" class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ w.product_name }}</td>
                            <td class="px-6 py-3 text-right text-sm text-gray-900">{{ w.quantity }}</td>
                            <td class="px-6 py-3 text-right text-sm text-gray-600">{{ formatMoney(w.unit_cost_cents) }}</td>
                            <td class="px-6 py-3 text-right text-sm font-medium text-red-600">{{ formatMoney(w.total_cost_cents) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Total Perdas</td>
                            <td class="px-6 py-3 text-right text-sm font-bold text-red-600">{{ formatMoney(c.total_waste_cents) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </FadeIn>

        <!-- Actions -->
        <FadeIn :delay="400">
        <div class="mt-6 flex gap-3">
            <Link
                :href="route('fechamentos.edit', c.id)"
                class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800"
            >
                Editar Fechamento
            </Link>
            <Link
                :href="route('fechamentos.index')"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                Voltar
            </Link>
        </div>
        </FadeIn>
    </AppLayout>
</template>
