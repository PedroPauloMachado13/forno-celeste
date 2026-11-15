<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import FadeIn from '@/Components/FadeIn.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    ingredients: Object,
});

const { formatMoney } = useMoney();

const columns = [
    { key: 'name', label: 'Nome' },
    { key: 'package_quantity', label: 'Qtd Embalagem', align: 'right' },
    { key: 'unit', label: 'Unidade', align: 'center' },
    { key: 'package_cost_cents', label: 'Custo Embalagem', align: 'right' },
    { key: 'cost_per_unit', label: 'Custo/Unidade', align: 'right' },
    { key: 'actions', label: '', align: 'right' },
];

const unitLabels = { g: 'Gramas (g)', un: 'Unidades (un)' };

function formatCostPerUnit(cents) {
    if (!cents) return 'R$ 0,0000';
    const value = (cents / 100).toFixed(4).replace('.', ',');
    return `R$ ${value}`;
}

// Delete modal
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const deleting = ref(false);

function confirmDelete(ingredient) {
    deleteTarget.value = ingredient;
    showDeleteModal.value = true;
}

function executeDelete() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('ingredientes.destroy', deleteTarget.value.id), {
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
        <Head title="Ingredientes" />

        <FadeIn>
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#1e293b]">Ingredientes</h1>
                    <p class="mt-1 text-sm text-gray-500">Gerencie os ingredientes da padaria</p>
                </div>
                <Link
                    :href="route('ingredientes.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 active:bg-indigo-900"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Novo Ingrediente
                </Link>
            </div>
        </FadeIn>

        <FadeIn :delay="80">
        <DataTable
            :columns="columns"
            :rows="ingredients.data"
            :search-keys="['name']"
            search-placeholder="Buscar ingrediente..."
        >
            <template #row="{ row }">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ row.name }}</td>
                <td class="px-4 py-3 text-right text-sm text-gray-600">{{ row.package_quantity }}</td>
                <td class="px-4 py-3 text-center text-sm text-gray-600">{{ row.unit }}</td>
                <td class="px-4 py-3 text-right text-sm text-gray-600">{{ formatMoney(row.package_cost_cents) }}</td>
                <td class="px-4 py-3 text-right text-sm text-gray-600">{{ formatCostPerUnit(row.cost_per_unit_cents) }}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <Link
                            :href="route('ingredientes.edit', row.id)"
                            class="rounded-md px-2 py-1 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50"
                        >
                            Editar
                        </Link>
                        <button
                            @click="confirmDelete(row)"
                            class="rounded-md px-2 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50"
                        >
                            Excluir
                        </button>
                    </div>
                </td>
            </template>
        </DataTable>
        </FadeIn>

        <!-- Pagination -->
        <FadeIn v-if="ingredients.data.length > 0 && ingredients.links" :delay="160">
            <div class="mt-4 flex justify-center">
                <nav class="flex items-center gap-1">
                    <template v-for="link in ingredients.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-lg px-3 py-2 text-sm transition"
                            :class="link.active
                                ? 'bg-indigo-700 text-white font-semibold'
                                : 'text-gray-500 hover:bg-gray-100'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="rounded-lg px-3 py-2 text-sm text-gray-300"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </FadeIn>

        <DeleteConfirmModal
            :show="showDeleteModal"
            :processing="deleting"
            :message="`Tem certeza que deseja excluir o ingrediente '${deleteTarget?.name}'?`"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
