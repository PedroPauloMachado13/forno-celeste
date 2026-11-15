<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import FadeIn from '@/Components/FadeIn.vue';

const form = useForm({
    name: '',
    cost_cents: 0,
    description: '',
});

function submit() {
    form.post(route('embalagens.store'));
}
</script>

<template>
    <AppLayout>
        <Head title="Nova Embalagem" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('embalagens.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Embalagens
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">Nova Embalagem</h1>
            </div>
        </FadeIn>

        <FadeIn :delay="80">
        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6 rounded-xl bg-white p-6 shadow-sm">
                <!-- Nome -->
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

                <!-- Custo -->
                <MoneyInput
                    v-model="form.cost_cents"
                    label="Custo"
                    id="cost_cents"
                    :error="form.errors.cost_cents"
                />

                <!-- Descricao -->
                <div>
                    <label for="description" class="mb-1 block text-sm font-medium text-gray-700">Descricao (opcional)</label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                        :class="{ 'border-red-400': form.errors.description }"
                    />
                    <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
                    <Link
                        :href="route('embalagens.index')"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Salvando...' : 'Salvar Embalagem' }}
                    </button>
                </div>
            </form>
        </div>
        </FadeIn>
    </AppLayout>
</template>
