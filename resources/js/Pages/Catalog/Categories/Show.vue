<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FadeIn from '@/Components/FadeIn.vue';

const props = defineProps({
    category: Object,
});

const c = props.category.data;
</script>

<template>
    <AppLayout>
        <Head :title="c.name" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('categorias.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Categorias
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">{{ c.name }}</h1>
            </div>
        </FadeIn>

        <FadeIn :delay="80">
        <div class="mx-auto max-w-2xl">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <dl class="space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Nome</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ c.name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Ordem</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ c.sort_order }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-6 flex gap-3">
                <Link
                    :href="route('categorias.edit', c.id)"
                    class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800"
                >
                    Editar
                </Link>
                <Link
                    :href="route('categorias.index')"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    Voltar
                </Link>
            </div>
        </div>
        </FadeIn>
    </AppLayout>
</template>
