<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FadeIn from '@/Components/FadeIn.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    ingredient: Object,
});

const { formatMoney } = useMoney();
const i = props.ingredient.data;

const unitLabels = { g: 'gramas', ml: 'mililitros', un: 'unidades' };
</script>

<template>
    <AppLayout>
        <Head :title="i.name" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('ingredientes.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Ingredientes
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">{{ i.name }}</h1>
            </div>
        </FadeIn>

        <FadeIn :delay="80">
        <div class="mx-auto max-w-2xl">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <dl class="space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Nome</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ i.name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Unidade</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ unitLabels[i.unit] || i.unit }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Qtde por Embalagem</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ i.package_quantity }} {{ i.unit }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Custo da Embalagem</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ formatMoney(i.package_cost_cents) }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-4">
                        <dt class="text-sm text-gray-500">Custo por {{ i.unit }}</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ formatMoney(i.cost_per_unit_cents, 4) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-6 flex gap-3">
                <Link
                    :href="route('ingredientes.edit', i.id)"
                    class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800"
                >
                    Editar
                </Link>
                <Link
                    :href="route('ingredientes.index')"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    Voltar
                </Link>
            </div>
        </div>
        </FadeIn>
    </AppLayout>
</template>
