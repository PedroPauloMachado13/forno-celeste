<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import FadeIn from '@/Components/FadeIn.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    fixedCosts: Object,
});

const { formatMoney } = useMoney();

const columns = [
    { key: 'name', label: 'Nome' },
    { key: 'type', label: 'Tipo' },
    { key: 'value', label: 'Valor/Porcentagem', align: 'right' },
    { key: 'description', label: 'Descricao' },
    { key: 'is_active', label: 'Status', align: 'center' },
    { key: 'actions', label: '', align: 'right' },
];

function formatType(type) {
    return type === 'fixed_amount' ? 'Valor Fixo' : 'Porcentagem';
}

function formatValue(row) {
    if (row.type === 'fixed_amount') {
        return formatMoney(row.default_amount_cents);
    }
    return row.percentage_value + '%';
}

// Delete modal
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const deleting = ref(false);

function confirmDelete(fixedCost) {
    deleteTarget.value = fixedCost;
    showDeleteModal.value = true;
}

function executeDelete() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('custos-fixos.destroy', deleteTarget.value.id), {
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
        <Head title="Custos Fixos" />

        <FadeIn>
            <div class="mb-6 flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-[#1e293b]">Custos Fixos</h1>
                    <p class="mt-1 text-sm text-gray-500">Gerencie os custos fixos mensais da padaria</p>
                </div>
                <Link
                    :href="route('custos-fixos.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 active:bg-indigo-900"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">Novo Custo Fixo</span>
                </Link>
            </div>
        </FadeIn>

        <FadeIn :delay="80">
        <DataTable
            :columns="columns"
            :rows="fixedCosts.data"
            :search-keys="['name']"
            search-placeholder="Buscar custo fixo..."
        >
            <template #row="{ row }">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ row.name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ formatType(row.type) }}</td>
                <td class="px-4 py-3 text-right text-sm text-gray-600">{{ formatValue(row) }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ row.description || '—' }}</td>
                <td class="px-4 py-3 text-center">
                    <span
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                        :class="row.is_active
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-gray-100 text-gray-500'"
                    >
                        {{ row.is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <Link
                            :href="route('custos-fixos.edit', row.id)"
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

        <DeleteConfirmModal
            :show="showDeleteModal"
            :processing="deleting"
            :message="`Tem certeza que deseja excluir o custo fixo '${deleteTarget?.name}'?`"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
