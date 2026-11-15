<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FadeIn from '@/Components/FadeIn.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import CmvBadge from '@/Components/CmvBadge.vue';
import PricingSimulator from '@/Components/PricingSimulator.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    categories: Object,
    base_recipes: Object,
    sub_recipes: Object,
    packagings: Object,
    card_machine_fee_percent: Number,
});

const { formatMoney } = useMoney();

const form = useForm({
    name: '',
    category_id: '',
    recipe_id: '',
    sub_recipes: [],
    packaging_id: '',
    sale_price_cents: 0,
});

// Client-side CMV calculation using recipe data passed from backend
const liveCmv = computed(() => {
    let ingredientCostCents = 0;

    // Base recipe cost
    const baseRecipe = props.base_recipes.data.find((r) => r.id === Number(form.recipe_id));
    if (baseRecipe) {
        ingredientCostCents += recipeCostPerUnit(baseRecipe);
    }

    // Sub-recipe costs (multiplied by quantity)
    for (const entry of form.sub_recipes) {
        const sub = props.sub_recipes.data.find((r) => r.id === Number(entry.recipe_id));
        if (sub) {
            ingredientCostCents += recipeCostPerUnit(sub) * (entry.quantity || 1);
        }
    }

    const selectedPackaging = props.packagings.data.find((p) => p.id === Number(form.packaging_id));
    const packagingCostCents = selectedPackaging ? selectedPackaging.cost_cents : 0;
    const cmvCents = ingredientCostCents + packagingCostCents;

    let cmvPercent = null;
    if (form.sale_price_cents > 0) {
        cmvPercent = Math.round((cmvCents / form.sale_price_cents) * 1000) / 10;
    }

    return { ingredientCostCents, packagingCostCents, cmvCents, cmvPercent };
});

function recipeCostPerUnit(recipe) {
    if (!recipe.ingredients || recipe.yield_quantity <= 0) return 0;
    let totalCost = 0;
    for (const line of recipe.ingredients) {
        if (line.ingredient && line.ingredient.package_quantity > 0) {
            totalCost += (parseFloat(line.quantity) / parseFloat(line.ingredient.package_quantity)) * line.ingredient.package_cost_cents;
        }
    }
    return Math.round(totalCost / recipe.yield_quantity);
}

function isSubRecipeSelected(id) {
    return form.sub_recipes.some((e) => e.recipe_id === id);
}

function toggleSubRecipe(id) {
    const idx = form.sub_recipes.findIndex((e) => e.recipe_id === id);
    if (idx === -1) {
        form.sub_recipes.push({ recipe_id: id, quantity: 1 });
    } else {
        form.sub_recipes.splice(idx, 1);
    }
}

function getSubRecipeEntry(id) {
    return form.sub_recipes.find((e) => e.recipe_id === id);
}

function onSelectPrice(priceCents) {
    form.sale_price_cents = priceCents;
}

function submit() {
    form.post(route('produtos.store'));
}
</script>

<template>
    <AppLayout>
        <Head title="Novo Produto" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('produtos.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Produtos
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">Novo Produto</h1>
            </div>
        </FadeIn>

        <form @submit.prevent="submit" class="grid gap-6 pb-20 lg:grid-cols-3 lg:pb-0">
            <!-- Left column: form fields -->
            <div class="lg:col-span-2 space-y-6">
                <FadeIn :delay="80">
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Dados do Produto</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
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
                            <label for="category_id" class="mb-1 block text-sm font-medium text-gray-700">Categoria</label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                required
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                                :class="{ 'border-red-400': form.errors.category_id }"
                            >
                                <option value="" disabled>Selecione...</option>
                                <option v-for="cat in categories.data" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <p v-if="form.errors.category_id" class="mt-1 text-xs text-red-600">{{ form.errors.category_id }}</p>
                        </div>
                        <div>
                            <label for="recipe_id" class="mb-1 block text-sm font-medium text-gray-700">Receita Base</label>
                            <select
                                id="recipe_id"
                                v-model="form.recipe_id"
                                required
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                                :class="{ 'border-red-400': form.errors.recipe_id }"
                            >
                                <option value="" disabled>Selecione...</option>
                                <option v-for="recipe in base_recipes.data" :key="recipe.id" :value="recipe.id">{{ recipe.name }}</option>
                            </select>
                            <p v-if="form.errors.recipe_id" class="mt-1 text-xs text-red-600">{{ form.errors.recipe_id }}</p>
                        </div>
                        <div>
                            <label for="packaging_id" class="mb-1 block text-sm font-medium text-gray-700">Embalagem</label>
                            <select
                                id="packaging_id"
                                v-model="form.packaging_id"
                                required
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                                :class="{ 'border-red-400': form.errors.packaging_id }"
                            >
                                <option value="" disabled>Selecione...</option>
                                <option v-for="pkg in packagings.data" :key="pkg.id" :value="pkg.id">{{ pkg.name }} ({{ formatMoney(pkg.cost_cents) }})</option>
                            </select>
                            <p v-if="form.errors.packaging_id" class="mt-1 text-xs text-red-600">{{ form.errors.packaging_id }}</p>
                        </div>
                        <MoneyInput
                            v-model="form.sale_price_cents"
                            label="Preco de Venda"
                            id="sale_price_cents"
                            :error="form.errors.sale_price_cents"
                        />
                    </div>
                </div>
                </FadeIn>

                <!-- Sub-recipes -->
                <FadeIn :delay="160">
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Sub-receitas (Recheios / Toppings)</h2>
                    <p v-if="form.errors.sub_recipes" class="mb-3 text-xs text-red-600">{{ form.errors.sub_recipes }}</p>

                    <div v-if="sub_recipes.data.length === 0" class="rounded-lg border-2 border-dashed border-gray-200 p-6 text-center">
                        <p class="text-sm text-gray-400">Nenhuma sub-receita cadastrada.</p>
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="sub in sub_recipes.data"
                            :key="sub.id"
                            class="rounded-lg border p-3 transition"
                            :class="isSubRecipeSelected(sub.id)
                                ? 'border-indigo-300 bg-indigo-50'
                                : 'border-gray-200 bg-white hover:border-gray-300'"
                        >
                            <div class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    :checked="isSubRecipeSelected(sub.id)"
                                    @change="toggleSubRecipe(sub.id)"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-200"
                                />
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-gray-900">{{ sub.name }}</span>
                                    <span v-if="sub.is_experimental" class="ml-2 rounded-full bg-purple-100 px-1.5 py-0.5 text-xs text-purple-700">Experimental</span>
                                </div>
                                <div v-if="isSubRecipeSelected(sub.id)" class="flex items-center gap-2">
                                    <label class="text-xs text-gray-500">Qtd:</label>
                                    <input
                                        v-model.number="getSubRecipeEntry(sub.id).quantity"
                                        type="number"
                                        min="1"
                                        max="9999"
                                        class="w-16 rounded-lg border border-gray-300 px-2 py-1 text-sm text-center shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </FadeIn>

                <!-- Actions -->
                <FadeIn :delay="240">
                <div class="hidden items-center justify-end gap-3 sm:flex">
                    <Link
                        :href="route('produtos.index')"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Salvando...' : 'Salvar Produto' }}
                    </button>
                </div>
                </FadeIn>
            </div>

            <!-- Right column: live CMV preview -->
            <FadeIn :delay="160" from="right">
            <div class="space-y-6">
                <div class="sticky top-8 space-y-6">
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Previa do CMV</h2>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Custo Ingredientes</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ formatMoney(liveCmv.ingredientCostCents) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Embalagem</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ formatMoney(liveCmv.packagingCostCents) }}</dd>
                            </div>
                            <div class="flex justify-between border-t border-gray-100 pt-3">
                                <dt class="text-sm font-semibold text-gray-700">CMV Total</dt>
                                <dd class="text-base font-bold text-gray-900">{{ formatMoney(liveCmv.cmvCents) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-500">CMV %</dt>
                                <dd>
                                    <CmvBadge :percent="liveCmv.cmvPercent" />
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div v-if="liveCmv.cmvCents > 0" class="rounded-xl bg-white p-6 shadow-sm">
                        <PricingSimulator
                            :cmv-cents="liveCmv.cmvCents"
                            :card-machine-fee-percent="card_machine_fee_percent || 3.15"
                            :show-select-button="true"
                            @select-price="onSelectPrice"
                        />
                    </div>
                </div>
            </div>
            </FadeIn>

            <!-- Mobile sticky submit bar -->
            <div class="fixed bottom-0 left-0 right-0 z-20 border-t border-gray-200 bg-white p-4 shadow-lg sm:hidden">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-indigo-700 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                >
                    {{ form.processing ? 'Salvando...' : 'Salvar Produto' }}
                </button>
            </div>
        </form>
    </AppLayout>
</template>
