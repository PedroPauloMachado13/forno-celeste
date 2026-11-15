<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import FadeIn from '@/Components/FadeIn.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    recipes: Object,
});

const { formatMoney } = useMoney();

const activeTab = ref('base');

const columns = [
    { key: 'name', label: 'Nome' },
    { key: 'type', label: 'Tipo', align: 'center' },
    { key: 'yield_quantity', label: 'Rendimento', align: 'right' },
    { key: 'ingredients_count', label: 'Ingredientes', align: 'center' },
    { key: 'actions', label: '', align: 'right' },
];

const typeLabels = { base: 'Base', sub: 'Sub-receita' };

const filteredRecipes = computed(() => {
    if (!props.recipes?.data) return [];
    return props.recipes.data.filter((r) => r.type === activeTab.value);
});

const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const deleting = ref(false);

function confirmDelete(recipe) {
    deleteTarget.value = recipe;
    showDeleteModal.value = true;
}

function executeDelete() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('receitas.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            showDeleteModal.value = false;
            deleteTarget.value = null;
        },
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Receitas" />

        <FadeIn>
            <div class="mb-6 flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-[#1e293b]">Receitas</h1>
                    <p class="mt-1 text-sm text-gray-500">Gerencie as receitas da padaria</p>
                </div>
                <Link
                    :href="route('receitas.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 active:bg-indigo-900"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">Nova Receita</span>
                </Link>
            </div>
        </FadeIn>

        <!-- Tabs -->
        <FadeIn :delay="80">
        <div class="mb-4 flex gap-1 rounded-lg bg-gray-100 p-1">
            <button
                @click="activeTab = 'base'"
                class="flex-1 rounded-md px-4 py-2 text-sm font-medium transition"
                :class="activeTab === 'base'
                    ? 'bg-white text-[#1e293b] shadow-sm'
                    : 'text-gray-500 hover:text-gray-700'"
            >
                Receitas Base
            </button>
            <button
                @click="activeTab = 'sub'"
                class="flex-1 rounded-md px-4 py-2 text-sm font-medium transition"
                :class="activeTab === 'sub'
                    ? 'bg-white text-[#1e293b] shadow-sm'
                    : 'text-gray-500 hover:text-gray-700'"
            >
                Sub-receitas (Recheios/Toppings)
            </button>
        </div>
        </FadeIn>

        <FadeIn :delay="160">
        <DataTable
            :columns="columns"
            :rows="filteredRecipes"
            :search-keys="['name']"
            search-placeholder="Buscar receita..."
        >
            <template #mobile-row="{ row }">
                <Link
                    :href="route('receitas.show', row.id)"
                    class="block rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition active:bg-gray-50"
                >
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="truncate font-medium text-gray-900">{{ row.name }}</span>
                            <span
                                v-if="row.is_experimental"
                                class="shrink-0 rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700"
                            >
                                Experimental
                            </span>
                        </div>
                        <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                            {{ typeLabels[row.type] || row.type }}
                        </span>
                    </div>
                    <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                        <span>Rende: {{ row.yield_quantity }}</span>
                        <span>{{ row.ingredients?.length || 0 }} ingredientes</span>
                    </div>
                </Link>
            </template>
            <template #row="{ row }">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                    <div class="flex items-center gap-2">
                        {{ row.name }}
                        <span
                            v-if="row.is_experimental"
                            class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700"
                        >
                            Experimental
                        </span>
                    </div>
                </td>
                <td class="px-4 py-3 text-center text-sm text-gray-600">{{ typeLabels[row.type] || row.type }}</td>
                <td class="px-4 py-3 text-right text-sm text-gray-600">{{ row.yield_quantity }}</td>
                <td class="px-4 py-3 text-center text-sm text-gray-600">{{ row.ingredients?.length || 0 }}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <Link
                            :href="route('receitas.show', row.id)"
                            class="inline-flex min-h-[44px] items-center rounded-md px-2 py-1 text-xs font-medium text-blue-600 transition hover:bg-blue-50"
                        >
                            Ver
                        </Link>
                        <Link
                            :href="route('receitas.edit', row.id)"
                            class="inline-flex min-h-[44px] items-center rounded-md px-2 py-1 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50"
                        >
                            Editar
                        </Link>
                        <button
                            @click="confirmDelete(row)"
                            class="inline-flex min-h-[44px] items-center rounded-md px-2 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50"
                        >
                            Excluir
                        </button>
                    </div>
                </td>
            </template>
        </DataTable>
        </FadeIn>

        <DeleteConfirmModal
            :show="showDeleteModal"
            :processing="deleting"
            :message="`Tem certeza que deseja excluir a receita '${deleteTarget?.name}'?`"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
