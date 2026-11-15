<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CmvBadge from '@/Components/CmvBadge.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import FadeIn from '@/Components/FadeIn.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    products: Object,
});

const { formatMoney } = useMoney();

const deleteTarget = ref(null);
const deleting = ref(false);

function confirmDelete(product) {
    deleteTarget.value = product;
}

function executeDelete() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('produtos.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteTarget.value = null;
        },
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Produtos" />

        <FadeIn>
            <div class="mb-6 flex items-center justify-between gap-3">
                <h1 class="text-2xl font-bold text-[#1e293b]">Produtos</h1>
                <Link
                    :href="route('produtos.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">Novo Produto</span>
                </Link>
            </div>
        </FadeIn>

        <FadeIn :delay="80">
        <div class="overflow-hidden rounded-xl bg-white shadow-sm">
            <div v-if="products.data.length === 0" class="p-8 text-center">
                <p class="text-sm text-gray-400">Nenhum produto cadastrado.</p>
            </div>

            <template v-else>
            <!-- Mobile cards -->
            <div class="space-y-3 p-4 sm:hidden">
                <Link
                    v-for="product in products.data"
                    :key="'mobile-' + product.id"
                    :href="route('produtos.show', product.id)"
                    class="block rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition active:bg-gray-50"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-900">{{ product.name }}</span>
                        <CmvBadge v-if="product.cmv" :percent="product.cmv.cmv_percent" size="sm" />
                    </div>
                    <div class="mt-2 flex items-center justify-between text-sm text-gray-500">
                        <span>{{ product.category?.name || '—' }}</span>
                        <span class="font-semibold text-gray-900">{{ formatMoney(product.sale_price_cents) }}</span>
                    </div>
                </Link>
            </div>

            <!-- Desktop table -->
            <div class="hidden sm:block">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Categoria</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Preco de Venda</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">CMV</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">CMV %</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr
                        v-for="product in products.data"
                        :key="product.id"
                        class="transition hover:bg-gray-50"
                    >
                        <td class="px-6 py-4">
                            <Link :href="route('produtos.show', product.id)" class="font-medium text-gray-900 hover:text-indigo-700">
                                {{ product.name }}
                            </Link>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ product.category?.name || '—' }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                            {{ formatMoney(product.sale_price_cents) }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-gray-700">
                            {{ product.cmv ? formatMoney(product.cmv.cmv_cents) : '—' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <CmvBadge
                                v-if="product.cmv"
                                :percent="product.cmv.cmv_percent"
                                size="sm"
                            />
                            <span v-else class="text-sm text-gray-400">—</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Link
                                    :href="route('produtos.edit', product.id)"
                                    class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-lg p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-700"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </Link>
                                <button
                                    @click="confirmDelete(product)"
                                    class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
            </template>
        </div>
        </FadeIn>

        <DeleteConfirmModal
            :show="deleteTarget !== null"
            :processing="deleting"
            title="Excluir produto"
            :message="`Tem certeza que deseja excluir o produto '${deleteTarget?.name}'?`"
            @close="deleteTarget = null"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
