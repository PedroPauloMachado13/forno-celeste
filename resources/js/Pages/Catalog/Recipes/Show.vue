<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FadeIn from '@/Components/FadeIn.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    recipe: Object,
    recipe_cost_cents: Number,
});

const { formatMoney } = useMoney();

const typeLabels = { base: 'Base', sub: 'Sub-receita' };

const costPerUnitCents = computed(() => {
    const yieldQty = props.recipe.data.yield_quantity;
    if (!yieldQty || yieldQty <= 0 || !props.recipe_cost_cents) return 0;
    return Math.round(props.recipe_cost_cents / yieldQty);
});
</script>

<template>
    <AppLayout>
        <Head :title="recipe.data.name" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('receitas.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Receitas
                </Link>
                <div class="mt-2 flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#1e293b]">{{ recipe.data.name }}</h1>
                    <span
                        v-if="recipe.data.is_experimental"
                        class="rounded-full bg-purple-100 px-2.5 py-1 text-xs font-medium text-purple-700"
                    >
                        Experimental
                    </span>
                </div>
            </div>
        </FadeIn>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Info card -->
            <FadeIn :delay="80">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Informacoes</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-500">Tipo</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ typeLabels[recipe.data.type] || recipe.data.type }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Rendimento por lote</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ recipe.data.yield_quantity }} unidades</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Custo total da receita</dt>
                        <dd class="text-sm font-semibold text-indigo-700">{{ formatMoney(recipe_cost_cents) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Custo por unidade</dt>
                        <dd class="text-sm font-semibold text-indigo-700">{{ formatMoney(costPerUnitCents) }}</dd>
                    </div>
                </dl>

                <div class="mt-4 flex gap-2">
                    <Link
                        :href="route('receitas.edit', recipe.data.id)"
                        class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800"
                    >
                        Editar
                    </Link>
                </div>
            </div>
            </FadeIn>

            <!-- Ingredients table -->
            <FadeIn :delay="160">
            <div class="rounded-xl bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Ingredientes</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Ingrediente</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-gray-500">Quantidade</th>
                                <th class="px-4 py-2 text-center text-xs font-semibold uppercase text-gray-500">Unidade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="line in recipe.data.ingredients"
                                :key="line.id"
                                class="hover:bg-indigo-50/40"
                            >
                                <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ line.ingredient?.name || '—' }}</td>
                                <td class="px-4 py-2 text-right text-sm text-gray-600">{{ line.quantity }}</td>
                                <td class="px-4 py-2 text-center text-sm text-gray-600">{{ line.unit }}</td>
                            </tr>
                            <tr v-if="!recipe.data.ingredients?.length">
                                <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-400">
                                    Nenhum ingrediente cadastrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            </FadeIn>
        </div>
    </AppLayout>
</template>
