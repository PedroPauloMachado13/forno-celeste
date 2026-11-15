<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CmvBadge from '@/Components/CmvBadge.vue';
import FadeIn from '@/Components/FadeIn.vue';
import PricingSimulator from '@/Components/PricingSimulator.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    product: Object,
    pricing_scenarios: Array,
});

const { formatMoney } = useMoney();

const p = props.product.data;
const cmv = p.cmv || props.product.cmv || {};
</script>

<template>
    <AppLayout>
        <Head :title="p.name" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('produtos.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Produtos
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">{{ p.name }}</h1>
            </div>
        </FadeIn>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Product info card -->
            <FadeIn :delay="80">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Informacoes do Produto</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Categoria</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ p.category?.name || '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Preco de Venda</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ formatMoney(p.sale_price_cents) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Embalagem</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ p.packaging?.name || '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Receita Base</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ p.base_recipe?.name || '—' }}</dd>
                    </div>
                    <div v-if="p.sub_recipes && p.sub_recipes.length > 0" class="flex justify-between">
                        <dt class="text-sm text-gray-500">Sub-receitas</dt>
                        <dd class="text-right text-sm font-medium text-gray-900">
                            <div v-for="sub in p.sub_recipes" :key="sub.id">
                                {{ sub.name }}
                                <span v-if="sub.pivot_quantity > 1" class="text-gray-500">(x{{ sub.pivot_quantity }})</span>
                            </div>
                        </dd>
                    </div>
                </dl>
            </div>
            </FadeIn>

            <!-- CMV breakdown card -->
            <FadeIn :delay="160">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Analise CMV</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Custo dos Ingredientes</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ formatMoney(cmv.ingredient_cost_cents) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Custo de Embalagem</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ formatMoney(cmv.packaging_cost_cents) }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-3">
                        <dt class="text-sm font-semibold text-gray-700">CMV Total</dt>
                        <dd class="text-sm font-bold text-gray-900">{{ formatMoney(cmv.cmv_cents) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-500">CMV %</dt>
                        <dd><CmvBadge :percent="cmv.cmv_percent" /></dd>
                    </div>
                </dl>
            </div>
            </FadeIn>
        </div>

        <!-- Pricing scenarios -->
        <FadeIn :delay="240">
        <div v-if="cmv.cmv_cents > 0" class="mt-6 rounded-xl bg-white p-6 shadow-sm">
            <PricingSimulator :cmv-cents="cmv.cmv_cents" :card-machine-fee-percent="3.15" />
        </div>
        </FadeIn>

        <!-- Ingredients detail -->
        <FadeIn :delay="320">
        <div v-if="p.base_recipe?.ingredients?.length" class="mt-6 rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Ingredientes da Receita Base ({{ p.base_recipe.name }})</h2>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Ingrediente</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-gray-500">Quantidade</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold uppercase text-gray-500">Unidade</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="line in p.base_recipe.ingredients" :key="line.id">
                        <td class="px-4 py-2 text-sm text-gray-900">{{ line.ingredient?.name || '—' }}</td>
                        <td class="px-4 py-2 text-right text-sm text-gray-700">{{ line.quantity }}</td>
                        <td class="px-4 py-2 text-center text-sm text-gray-500">{{ line.unit }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        </FadeIn>

        <FadeIn :delay="400">
        <div
            v-for="sub in (p.sub_recipes || [])"
            :key="sub.id"
            class="mt-4 rounded-xl bg-white p-6 shadow-sm"
        >
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">
                {{ sub.name }}
                <span v-if="sub.pivot_quantity > 1" class="text-gray-500">(x{{ sub.pivot_quantity }})</span>
            </h2>
            <table v-if="sub.ingredients?.length" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Ingrediente</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-gray-500">Quantidade</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold uppercase text-gray-500">Unidade</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="line in sub.ingredients" :key="line.id">
                        <td class="px-4 py-2 text-sm text-gray-900">{{ line.ingredient?.name || '—' }}</td>
                        <td class="px-4 py-2 text-right text-sm text-gray-700">{{ line.quantity }}</td>
                        <td class="px-4 py-2 text-center text-sm text-gray-500">{{ line.unit }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        </FadeIn>

        <!-- Actions -->
        <FadeIn :delay="480">
        <div class="mt-6 flex gap-3">
            <Link
                :href="route('produtos.edit', p.id)"
                class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800"
            >
                Editar Produto
            </Link>
        </div>
        </FadeIn>
    </AppLayout>
</template>
