<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FadeIn from '@/Components/FadeIn.vue';

const props = defineProps({
    category: Object,
});

const form = useForm({
    name: props.category.data.name,
    sort_order: props.category.data.sort_order,
});

function submit() {
    form.put(route('categorias.update', props.category.data.id));
}
</script>

<template>
    <AppLayout>
        <Head title="Editar Categoria" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('categorias.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Categorias
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">Editar Categoria</h1>
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
                    <label for="sort_order" class="mb-1 block text-sm font-medium text-gray-700">Ordem de exibicao</label>
                    <input
                        id="sort_order"
                        v-model.number="form.sort_order"
                        type="number"
                        min="0"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                        :class="{ 'border-red-400': form.errors.sort_order }"
                    />
                    <p v-if="form.errors.sort_order" class="mt-1 text-xs text-red-600">{{ form.errors.sort_order }}</p>
                </div>

                <div class="hidden items-center justify-end gap-3 border-t border-gray-100 pt-4 sm:flex">
                    <Link
                        :href="route('categorias.index')"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Salvando...' : 'Atualizar Categoria' }}
                    </button>
                </div>

                <!-- Mobile sticky submit bar -->
                <div class="fixed bottom-0 left-0 right-0 z-20 border-t border-gray-200 bg-white p-4 shadow-lg sm:hidden">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-lg bg-indigo-700 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Salvando...' : 'Atualizar Categoria' }}
                    </button>
                </div>
            </form>
        </div>
        </FadeIn>
    </AppLayout>
</template>
