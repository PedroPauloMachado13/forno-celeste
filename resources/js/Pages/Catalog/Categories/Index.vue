<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import FadeIn from '@/Components/FadeIn.vue';

const props = defineProps({
    categories: Object,
});

const columns = [
    { key: 'name', label: 'Nome' },
    { key: 'sort_order', label: 'Ordem', align: 'center' },
    { key: 'actions', label: '', align: 'right' },
];

const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const deleting = ref(false);

function confirmDelete(category) {
    deleteTarget.value = category;
    showDeleteModal.value = true;
}

function executeDelete() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('categorias.destroy', deleteTarget.value.id), {
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
        <Head title="Categorias" />

        <FadeIn>
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#1e293b]">Categorias</h1>
                    <p class="mt-1 text-sm text-gray-500">Gerencie as categorias de produtos</p>
                </div>
                <Link
                    :href="route('categorias.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 active:bg-indigo-900"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nova Categoria
                </Link>
            </div>
        </FadeIn>

        <FadeIn :delay="80">
        <DataTable
            :columns="columns"
            :rows="categories.data"
            :search-keys="['name']"
            search-placeholder="Buscar categoria..."
        >
            <template #row="{ row }">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ row.name }}</td>
                <td class="px-4 py-3 text-center text-sm text-gray-600">{{ row.sort_order }}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <Link
                            :href="route('categorias.edit', row.id)"
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
            :message="`Tem certeza que deseja excluir a categoria '${deleteTarget?.name}'?`"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
