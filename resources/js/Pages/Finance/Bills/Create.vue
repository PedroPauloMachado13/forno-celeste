<script setup>
import { watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import FadeIn from '@/Components/FadeIn.vue';

const props = defineProps({
    fixedCosts: Object,
});

const form = useForm({
    fixed_cost_id: '',
    bill_type: 'fixed',
    description: '',
    amount_cents: 0,
    reference_month: '',
    due_date: '',
    mark_as_paid: false,
});

const billTypes = [
    { value: 'fixed', label: 'Fixo' },
    { value: 'supplier', label: 'Fornecedor' },
    { value: 'personal', label: 'Pessoal' },
    { value: 'tax', label: 'Imposto' },
    { value: 'other', label: 'Outro' },
];

// Auto-fill when a fixed cost of type fixed_amount is selected
watch(() => form.fixed_cost_id, (newVal) => {
    if (!newVal) return;
    const fc = props.fixedCosts.data.find((c) => c.id === Number(newVal));
    if (fc) {
        form.description = fc.name;
        form.bill_type = 'fixed';
        if (fc.type === 'fixed_amount' && fc.default_amount_cents) {
            form.amount_cents = fc.default_amount_cents;
        }
    }
});

function submit() {
    form.transform((data) => ({
        ...data,
        fixed_cost_id: data.fixed_cost_id || null,
        reference_month: data.reference_month ? data.reference_month + '-01' : null,
        due_date: data.due_date || null,
    })).post(route('contas.store'));
}
</script>

<template>
    <AppLayout>
        <Head title="Nova Conta" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('contas.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Contas a Pagar
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">Nova Conta</h1>
            </div>
        </FadeIn>

        <FadeIn :delay="80">
        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6 rounded-xl bg-white p-6 pb-20 shadow-sm sm:pb-6">
                <!-- Custo Fixo -->
                <div>
                    <label for="fixed_cost_id" class="mb-1 block text-sm font-medium text-gray-700">Custo Fixo (opcional)</label>
                    <select
                        id="fixed_cost_id"
                        v-model="form.fixed_cost_id"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                        :class="{ 'border-red-400': form.errors.fixed_cost_id }"
                    >
                        <option value="">Nenhum (avulsa)</option>
                        <option v-for="fc in fixedCosts.data" :key="fc.id" :value="fc.id">
                            {{ fc.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.fixed_cost_id" class="mt-1 text-xs text-red-600">{{ form.errors.fixed_cost_id }}</p>
                </div>

                <!-- Tipo de Despesa -->
                <div>
                    <label for="bill_type" class="mb-1 block text-sm font-medium text-gray-700">Tipo de Despesa</label>
                    <select
                        id="bill_type"
                        v-model="form.bill_type"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                        :class="{ 'border-red-400': form.errors.bill_type }"
                    >
                        <option v-for="bt in billTypes" :key="bt.value" :value="bt.value">
                            {{ bt.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.bill_type" class="mt-1 text-xs text-red-600">{{ form.errors.bill_type }}</p>
                </div>

                <!-- Descricao -->
                <div>
                    <label for="description" class="mb-1 block text-sm font-medium text-gray-700">Descricao</label>
                    <input
                        id="description"
                        v-model="form.description"
                        type="text"
                        required
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                        :class="{ 'border-red-400': form.errors.description }"
                    />
                    <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
                </div>

                <!-- Valor -->
                <MoneyInput
                    v-model="form.amount_cents"
                    label="Valor"
                    id="amount_cents"
                    :error="form.errors.amount_cents"
                />

                <!-- Mes de Referencia -->
                <div>
                    <label for="reference_month" class="mb-1 block text-sm font-medium text-gray-700">Mes de Referencia (opcional)</label>
                    <input
                        id="reference_month"
                        v-model="form.reference_month"
                        type="month"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                        :class="{ 'border-red-400': form.errors.reference_month }"
                    />
                    <p v-if="form.errors.reference_month" class="mt-1 text-xs text-red-600">{{ form.errors.reference_month }}</p>
                </div>

                <!-- Data de Vencimento -->
                <div>
                    <label for="due_date" class="mb-1 block text-sm font-medium text-gray-700">Data de Vencimento (opcional)</label>
                    <input
                        id="due_date"
                        v-model="form.due_date"
                        type="date"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                        :class="{ 'border-red-400': form.errors.due_date }"
                    />
                    <p v-if="form.errors.due_date" class="mt-1 text-xs text-red-600">{{ form.errors.due_date }}</p>
                </div>

                <!-- Ja foi pago? -->
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input
                            v-model="form.mark_as_paid"
                            type="checkbox"
                            class="peer sr-only"
                        />
                        <div class="peer h-6 w-11 rounded-full bg-gray-200 transition after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-all after:content-[''] peer-checked:bg-emerald-500 peer-checked:after:translate-x-full peer-focus:ring-2 peer-focus:ring-emerald-200"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700">Ja foi pago</span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="hidden items-center justify-end gap-3 border-t border-gray-100 pt-4 sm:flex">
                    <Link
                        :href="route('contas.index')"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Salvando...' : 'Salvar Conta' }}
                    </button>
                </div>

                <!-- Mobile sticky submit bar -->
                <div class="fixed bottom-0 left-0 right-0 z-20 border-t border-gray-200 bg-white p-4 shadow-lg sm:hidden">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-lg bg-indigo-700 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Salvando...' : 'Salvar Conta' }}
                    </button>
                </div>
            </form>
        </div>
        </FadeIn>
    </AppLayout>
</template>
