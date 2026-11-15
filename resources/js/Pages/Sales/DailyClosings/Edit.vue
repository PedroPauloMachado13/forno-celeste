<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FadeIn from '@/Components/FadeIn.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    closing: Object,
    products: Array,
});

const { formatMoney } = useMoney();

const c = props.closing.data;

const form = useForm({
    card_sales_cents: c.card_sales_cents || 0,
    cash_drawer_cents: c.cash_drawer_cents || 0,
    withdrawals: (c.withdrawals || []).map((w) => ({
        amount_cents: w.amount_cents,
        description: w.description,
    })),
    wastes: (c.wastes || []).map((w) => ({
        product_id: w.product_id,
        quantity: w.quantity,
    })),
});

function formatDate(dateStr) {
    const [year, month, day] = dateStr.split('-');
    return `${day}/${month}/${year}`;
}

function addWithdrawal() {
    form.withdrawals.push({ amount_cents: 0, description: '' });
}

function removeWithdrawal(index) {
    form.withdrawals.splice(index, 1);
}

function addWaste() {
    form.wastes.push({ product_id: '', quantity: 1 });
}

function removeWaste(index) {
    form.wastes.splice(index, 1);
}

function getProductCmv(productId) {
    const product = props.products?.find((p) => p.id === Number(productId));
    return product?.cmv_cents ?? 0;
}

const totalWithdrawals = computed(() => {
    return form.withdrawals.reduce((sum, w) => sum + (w.amount_cents || 0), 0);
});

const wasteLineCosts = computed(() => {
    return form.wastes.map((w) => {
        const unitCost = getProductCmv(w.product_id);
        return unitCost * (w.quantity || 0);
    });
});

const totalWasteCents = computed(() => {
    return wasteLineCosts.value.reduce((sum, cost) => sum + cost, 0);
});

const cashSalesCents = computed(() => {
    return form.cash_drawer_cents - c.opening_cash_cents + totalWithdrawals.value;
});

const totalRevenueCents = computed(() => {
    return form.card_sales_cents + Math.max(0, cashSalesCents.value);
});

function submit() {
    form.put(route('fechamentos.update', c.id));
}
</script>

<template>
    <AppLayout>
        <Head :title="`Editar Fechamento - ${formatDate(c.closing_date)}`" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('fechamentos.show', c.id)" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Fechamento
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">Editar Fechamento</h1>
            </div>
        </FadeIn>

        <form @submit.prevent="submit" class="grid gap-6 pb-24 lg:grid-cols-3 lg:pb-0">
            <!-- Left column: form fields -->
            <div class="lg:col-span-2 space-y-6">
                <FadeIn :delay="80">
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Dados do Fechamento</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Data</label>
                            <div class="flex h-[38px] items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600">
                                {{ formatDate(c.closing_date) }}
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Caixa de Abertura</label>
                            <div class="flex h-[38px] items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600">
                                <template v-if="c.opening_cash_cents > 0">
                                    {{ formatMoney(c.opening_cash_cents) }}
                                </template>
                                <template v-else>
                                    <span class="text-gray-400">Primeiro fechamento</span>
                                </template>
                            </div>
                        </div>
                        <MoneyInput
                            v-model="form.card_sales_cents"
                            label="Vendas no Cartao"
                            id="card_sales_cents"
                            :error="form.errors.card_sales_cents"
                        />
                        <MoneyInput
                            v-model="form.cash_drawer_cents"
                            label="Dinheiro em Caixa"
                            id="cash_drawer_cents"
                            :error="form.errors.cash_drawer_cents"
                        />
                    </div>
                </div>
                </FadeIn>

                <!-- Withdrawals section -->
                <FadeIn :delay="160">
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Saidas de Caixa</h2>
                        <button
                            type="button"
                            @click="addWithdrawal"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Adicionar Saida
                        </button>
                    </div>

                    <p v-if="form.errors.withdrawals" class="mb-3 text-xs text-red-600">{{ form.errors.withdrawals }}</p>

                    <div v-if="form.withdrawals.length === 0" class="rounded-lg border-2 border-dashed border-gray-200 p-6 text-center">
                        <p class="text-sm text-gray-400">Nenhuma saida de caixa registrada.</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="(withdrawal, index) in form.withdrawals"
                            :key="index"
                            class="flex flex-col gap-2 rounded-lg border border-gray-100 bg-gray-50/50 p-3 sm:flex-row sm:items-start sm:gap-3"
                        >
                            <div class="w-full sm:w-40 sm:shrink-0">
                                <MoneyInput
                                    v-model="withdrawal.amount_cents"
                                    :id="`withdrawal_amount_${index}`"
                                    :error="form.errors[`withdrawals.${index}.amount_cents`]"
                                />
                            </div>
                            <div class="flex-1">
                                <input
                                    v-model="withdrawal.description"
                                    type="text"
                                    placeholder="Descricao (ex: troco, fornecedor...)"
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                                    :class="{ 'border-red-400': form.errors[`withdrawals.${index}.description`] }"
                                />
                                <p v-if="form.errors[`withdrawals.${index}.description`]" class="mt-1 text-xs text-red-600">
                                    {{ form.errors[`withdrawals.${index}.description`] }}
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="removeWithdrawal(index)"
                                class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center self-end rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-600 sm:self-auto sm:mt-1"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                </FadeIn>

                <!-- Wastes section -->
                <FadeIn :delay="240">
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Perdas / Descartes</h2>
                        <button
                            type="button"
                            @click="addWaste"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Adicionar Perda
                        </button>
                    </div>

                    <p v-if="form.errors.wastes" class="mb-3 text-xs text-red-600">{{ form.errors.wastes }}</p>

                    <div v-if="form.wastes.length === 0" class="rounded-lg border-2 border-dashed border-gray-200 p-6 text-center">
                        <p class="text-sm text-gray-400">Nenhuma perda registrada.</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="(waste, index) in form.wastes"
                            :key="index"
                            class="flex flex-col gap-2 rounded-lg border border-gray-100 bg-gray-50/50 p-3 sm:flex-row sm:items-start sm:gap-3"
                        >
                            <div class="flex-1">
                                <select
                                    v-model="waste.product_id"
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                                    :class="{ 'border-red-400': form.errors[`wastes.${index}.product_id`] }"
                                >
                                    <option value="" disabled>Selecionar produto...</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors[`wastes.${index}.product_id`]" class="mt-1 text-xs text-red-600">
                                    {{ form.errors[`wastes.${index}.product_id`] }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3 sm:contents">
                                <div class="w-24 shrink-0">
                                    <input
                                        v-model.number="waste.quantity"
                                        type="number"
                                        min="1"
                                        max="9999"
                                        placeholder="Qtd"
                                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-center shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                                        :class="{ 'border-red-400': form.errors[`wastes.${index}.quantity`] }"
                                    />
                                    <p v-if="form.errors[`wastes.${index}.quantity`]" class="mt-1 text-xs text-red-600">
                                        {{ form.errors[`wastes.${index}.quantity`] }}
                                    </p>
                                </div>
                                <div class="flex-1 sm:w-28 sm:shrink-0 sm:flex-initial flex items-center justify-end">
                                    <span class="text-sm font-medium text-gray-600">
                                        {{ formatMoney(wasteLineCosts[index] || 0) }}
                                    </span>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="removeWaste(index)"
                                class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center self-end rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-600 sm:self-auto sm:mt-1"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                </FadeIn>

                <!-- Desktop actions -->
                <FadeIn :delay="320">
                <div class="hidden items-center justify-end gap-3 sm:flex">
                    <Link
                        :href="route('fechamentos.show', c.id)"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Salvando...' : 'Atualizar Fechamento' }}
                    </button>
                </div>
                </FadeIn>
            </div>

            <!-- Right column: live calculation sidebar -->
            <FadeIn :delay="160" from="right">
            <div class="space-y-6">
                <div class="sticky top-8">
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Resumo do Dia</h2>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Caixa de Abertura</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ formatMoney(c.opening_cash_cents) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Dinheiro em Caixa</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ formatMoney(form.cash_drawer_cents) }}</dd>
                            </div>
                            <div v-if="form.withdrawals.length > 0" class="flex justify-between">
                                <dt class="text-sm text-gray-500">Saidas de Caixa</dt>
                                <dd class="text-sm font-medium text-red-600">- {{ formatMoney(totalWithdrawals) }}</dd>
                            </div>
                            <div class="flex justify-between border-t border-gray-100 pt-3">
                                <dt class="text-sm font-semibold text-gray-700">Vendas em Dinheiro</dt>
                                <dd class="text-sm font-bold" :class="cashSalesCents >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                    {{ formatMoney(cashSalesCents) }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Vendas no Cartao</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ formatMoney(form.card_sales_cents) }}</dd>
                            </div>
                            <div class="flex justify-between border-t border-gray-100 pt-3">
                                <dt class="text-base font-bold text-gray-900">Receita Total</dt>
                                <dd class="text-base font-bold text-indigo-700">{{ formatMoney(totalRevenueCents) }}</dd>
                            </div>
                            <div v-if="form.wastes.length > 0" class="flex justify-between border-t border-gray-100 pt-3">
                                <dt class="text-sm font-semibold text-gray-700">Custo de Perdas</dt>
                                <dd class="text-sm font-bold text-red-600">- {{ formatMoney(totalWasteCents) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div v-if="cashSalesCents < 0" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <div class="flex gap-3">
                            <svg class="h-5 w-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            <p class="text-sm text-amber-800">
                                O dinheiro em caixa esta menor que o esperado. Verifique se todas as saidas foram registradas.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            </FadeIn>

            <!-- Mobile sticky summary bar -->
            <div class="fixed bottom-0 left-0 right-0 z-20 border-t border-gray-200 bg-white px-4 py-3 shadow-lg lg:hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Receita Total</p>
                        <p class="text-lg font-bold text-indigo-700">{{ formatMoney(totalRevenueCents) }}</p>
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Salvando...' : 'Atualizar' }}
                    </button>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
