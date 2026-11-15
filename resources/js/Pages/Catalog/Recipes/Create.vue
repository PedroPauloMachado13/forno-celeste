<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FadeIn from '@/Components/FadeIn.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    ingredients: Object,
});

const { formatMoney } = useMoney();

const form = useForm({
    name: '',
    type: 'base',
    yield_quantity: 1,
    is_experimental: false,
    ingredients: [],
});

function addIngredientLine() {
    form.ingredients.push({
        ingredient_id: '',
        quantity: '',
        unit: 'g',
    });
}

function removeIngredientLine(index) {
    form.ingredients.splice(index, 1);
}

function onIngredientSelect(lineIndex) {
    const line = form.ingredients[lineIndex];
    const ingredient = props.ingredients.data.find((i) => i.id === Number(line.ingredient_id));
    if (ingredient) {
        line.unit = ingredient.unit;
    }
}

const ingredientSearch = ref('');

const filteredIngredients = computed(() => {
    if (!ingredientSearch.value.trim()) return props.ingredients.data;
    const term = ingredientSearch.value.toLowerCase();
    return props.ingredients.data.filter((i) => i.name.toLowerCase().includes(term));
});

const runningCostCents = computed(() => {
    let total = 0;
    for (const line of form.ingredients) {
        const ingredient = props.ingredients.data.find((i) => i.id === Number(line.ingredient_id));
        if (ingredient && line.quantity) {
            total += ingredient.cost_per_unit_cents * parseFloat(line.quantity);
        }
    }
    return Math.round(total);
});

function submit() {
    form.post(route('receitas.store'));
}
</script>

<template>
    <AppLayout>
        <Head title="Nova Receita" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('receitas.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Receitas
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">Nova Receita</h1>
            </div>
        </FadeIn>

        <form @submit.prevent="submit" class="space-y-6 pb-20 sm:pb-0">
            <FadeIn :delay="80">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Dados da Receita</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Nome</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                            :class="{ 'border-red-400': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label for="type" class="mb-1 block text-sm font-medium text-gray-700">Tipo</label>
                        <select
                            id="type"
                            v-model="form.type"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                        >
                            <option value="base">Base</option>
                            <option value="sub">Sub-receita (Recheio/Topping)</option>
                        </select>
                        <p v-if="form.errors.type" class="mt-1 text-xs text-red-600">{{ form.errors.type }}</p>
                    </div>
                    <div>
                        <label for="yield_quantity" class="mb-1 block text-sm font-medium text-gray-700">Rendimento por lote</label>
                        <input
                            id="yield_quantity"
                            v-model.number="form.yield_quantity"
                            type="number"
                            min="1"
                            required
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                            :class="{ 'border-red-400': form.errors.yield_quantity }"
                        />
                        <p v-if="form.errors.yield_quantity" class="mt-1 text-xs text-red-600">{{ form.errors.yield_quantity }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="flex items-center gap-2">
                        <input
                            v-model="form.is_experimental"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-200"
                        />
                        <span class="text-sm font-medium text-gray-700">Receita experimental</span>
                    </label>
                </div>
            </div>
            </FadeIn>

            <!-- Ingredients section -->
            <FadeIn :delay="160">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Ingredientes</h2>
                    <button
                        type="button"
                        @click="addIngredientLine"
                        class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Adicionar ingrediente
                    </button>
                </div>

                <p v-if="form.errors.ingredients" class="mb-3 text-xs text-red-600">{{ form.errors.ingredients }}</p>

                <div v-if="form.ingredients.length === 0" class="rounded-lg border-2 border-dashed border-gray-200 p-8 text-center">
                    <p class="text-sm text-gray-400">Nenhum ingrediente adicionado. Clique em "Adicionar ingrediente" para comecar.</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="(line, index) in form.ingredients"
                        :key="index"
                        class="flex items-start gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3"
                    >
                        <div class="flex-1 grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">Ingrediente</label>
                                <select
                                    v-model="line.ingredient_id"
                                    @change="onIngredientSelect(index)"
                                    required
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                                >
                                    <option value="" disabled>Selecione...</option>
                                    <option v-for="ing in ingredients.data" :key="ing.id" :value="ing.id">
                                        {{ ing.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">Quantidade</label>
                                <input
                                    v-model="line.quantity"
                                    type="number"
                                    step="any"
                                    min="0.01"
                                    required
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">Unidade</label>
                                <select
                                    v-model="line.unit"
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                                >
                                    <option value="g">g</option>
                                    <option value="un">un</option>
                                </select>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="removeIngredientLine(index)"
                            class="mt-5 shrink-0 rounded-lg p-1.5 text-red-400 transition hover:bg-red-50 hover:text-red-600"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Running cost total -->
                <div v-if="form.ingredients.length > 0" class="mt-4 flex items-center justify-end rounded-lg bg-indigo-50 p-3">
                    <span class="text-sm text-gray-600">Custo total dos ingredientes:</span>
                    <span class="ml-2 text-sm font-semibold text-indigo-700">{{ formatMoney(runningCostCents) }}</span>
                </div>
            </div>
            </FadeIn>

            <!-- Desktop actions -->
            <FadeIn :delay="240">
            <div class="hidden items-center justify-end gap-3 sm:flex">
                <Link
                    :href="route('receitas.index')"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    Cancelar
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                >
                    {{ form.processing ? 'Salvando...' : 'Salvar Receita' }}
                </button>
            </div>
            </FadeIn>

            <!-- Mobile sticky submit bar -->
            <div class="fixed bottom-0 left-0 right-0 z-20 border-t border-gray-200 bg-white p-4 shadow-lg sm:hidden">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-indigo-700 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                >
                    {{ form.processing ? 'Salvando...' : 'Salvar Receita' }}
                </button>
            </div>
        </form>
    </AppLayout>
</template>
