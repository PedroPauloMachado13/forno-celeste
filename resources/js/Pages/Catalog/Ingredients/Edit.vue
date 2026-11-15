<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FadeIn from '@/Components/FadeIn.vue';
import MoneyInput from '@/Components/MoneyInput.vue';

const props = defineProps({
    ingredient: Object,
});

const form = useForm({
    name: props.ingredient.data.name,
    package_quantity: props.ingredient.data.package_quantity,
    unit: props.ingredient.data.unit,
    package_cost_cents: props.ingredient.data.package_cost_cents,
});

const costPerUnit = computed(() => {
    const qty = parseFloat(form.package_quantity);
    if (!qty || qty <= 0 || !form.package_cost_cents) return null;
    return form.package_cost_cents / qty;
});

function formatCostPerUnit(cents) {
    if (!cents) return 'R$ 0,0000';
    const value = (cents / 100).toFixed(4).replace('.', ',');
    return `R$ ${value}`;
}

function submit() {
    form.put(route('ingredientes.update', props.ingredient.data.id));
}
</script>

<template>
    <AppLayout>
        <Head title="Editar Ingrediente" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('ingredientes.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Ingredientes
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">Editar Ingrediente</h1>
            </div>
        </FadeIn>

        <FadeIn :delay="80">
        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6 rounded-xl bg-white p-6 pb-20 shadow-sm sm:pb-6">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Nome</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                        :class="{ 'border-red-400': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="package_quantity" class="mb-1 block text-sm font-medium text-gray-700">Quantidade da Embalagem</label>
                    <input
                        id="package_quantity"
                        v-model="form.package_quantity"
                        type="number"
                        step="any"
                        min="0.01"
                        required
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                        :class="{ 'border-red-400': form.errors.package_quantity }"
                    />
                    <p v-if="form.errors.package_quantity" class="mt-1 text-xs text-red-600">{{ form.errors.package_quantity }}</p>
                </div>

                <div>
                    <label for="unit" class="mb-1 block text-sm font-medium text-gray-700">Unidade</label>
                    <select
                        id="unit"
                        v-model="form.unit"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                        :class="{ 'border-red-400': form.errors.unit }"
                    >
                        <option value="g">Gramas (g)</option>
                        <option value="un">Unidades (un)</option>
                    </select>
                    <p v-if="form.errors.unit" class="mt-1 text-xs text-red-600">{{ form.errors.unit }}</p>
                </div>

                <MoneyInput
                    v-model="form.package_cost_cents"
                    label="Custo da Embalagem"
                    id="package_cost_cents"
                    :error="form.errors.package_cost_cents"
                />

                <div v-if="costPerUnit !== null" class="rounded-lg bg-indigo-50 p-4">
                    <p class="text-sm text-gray-600">
                        Custo por unidade:
                        <span class="font-semibold text-indigo-700">{{ formatCostPerUnit(costPerUnit) }}</span>
                    </p>
                </div>

                <div class="hidden items-center justify-end gap-3 border-t border-gray-100 pt-4 sm:flex">
                    <Link
                        :href="route('ingredientes.index')"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Salvando...' : 'Atualizar Ingrediente' }}
                    </button>
                </div>

                <!-- Mobile sticky submit bar -->
                <div class="fixed bottom-0 left-0 right-0 z-20 border-t border-gray-200 bg-white p-4 shadow-lg sm:hidden">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-lg bg-indigo-700 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Salvando...' : 'Atualizar Ingrediente' }}
                    </button>
                </div>
            </form>
        </div>
        </FadeIn>
    </AppLayout>
</template>
