<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import FadeIn from '@/Components/FadeIn.vue';

const form = useForm({
    name: '',
    type: 'fixed_amount',
    default_amount_cents: 0,
    percentage_value: '',
    description: '',
    is_active: true,
});

function submit() {
    form.post(route('custos-fixos.store'));
}
</script>

<template>
    <AppLayout>
        <Head title="Novo Custo Fixo" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('custos-fixos.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Custos Fixos
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">Novo Custo Fixo</h1>
            </div>
        </FadeIn>

        <FadeIn :delay="80">
        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6 rounded-xl bg-white p-6 pb-20 shadow-sm sm:pb-6">
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

                <!-- Tipo -->
                <div>
                    <label for="type" class="mb-1 block text-sm font-medium text-gray-700">Tipo</label>
                    <select
                        id="type"
                        v-model="form.type"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                        :class="{ 'border-red-400': form.errors.type }"
                    >
                        <option value="fixed_amount">Valor Fixo</option>
                        <option value="percentage">Porcentagem</option>
                    </select>
                    <p v-if="form.errors.type" class="mt-1 text-xs text-red-600">{{ form.errors.type }}</p>
                </div>

                <!-- Valor Padrao (fixed_amount) -->
                <MoneyInput
                    v-if="form.type === 'fixed_amount'"
                    v-model="form.default_amount_cents"
                    label="Valor Padrao"
                    id="default_amount_cents"
                    :error="form.errors.default_amount_cents"
                />

                <!-- Porcentagem (percentage) -->
                <div v-if="form.type === 'percentage'">
                    <label for="percentage_value" class="mb-1 block text-sm font-medium text-gray-700">Porcentagem</label>
                    <div class="relative">
                        <input
                            id="percentage_value"
                            v-model="form.percentage_value"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            placeholder="0,00"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 pr-8 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                            :class="{ 'border-red-400': form.errors.percentage_value }"
                        />
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-500">%</span>
                    </div>
                    <p v-if="form.errors.percentage_value" class="mt-1 text-xs text-red-600">{{ form.errors.percentage_value }}</p>
                </div>

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

                <!-- Ativo -->
                <div class="flex items-center gap-3">
                    <input
                        id="is_active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <label for="is_active" class="text-sm font-medium text-gray-700">Ativo</label>
                </div>

                <!-- Actions -->
                <div class="hidden items-center justify-end gap-3 border-t border-gray-100 pt-4 sm:flex">
                    <Link
                        :href="route('custos-fixos.index')"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Salvando...' : 'Salvar Custo Fixo' }}
                    </button>
                </div>

                <!-- Mobile sticky submit bar -->
                <div class="fixed bottom-0 left-0 right-0 z-20 border-t border-gray-200 bg-white p-4 shadow-lg sm:hidden">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-lg bg-indigo-700 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Salvando...' : 'Salvar Custo Fixo' }}
                    </button>
                </div>
            </form>
        </div>
        </FadeIn>
    </AppLayout>
</template>
