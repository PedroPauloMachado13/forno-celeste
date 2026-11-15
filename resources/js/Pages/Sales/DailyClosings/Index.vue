<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import FadeIn from '@/Components/FadeIn.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    closings: Object,
});

const { formatMoney } = useMoney();

const deleteTarget = ref(null);
const deleting = ref(false);

function formatDate(dateStr) {
    const [year, month, day] = dateStr.split('-');
    return `${day}/${month}/${year}`;
}

function confirmDelete(closing) {
    deleteTarget.value = closing;
}

function executeDelete() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('fechamentos.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleting.value = false;
            deleteTarget.value = null;
        },
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Fechamentos Diarios" />

        <FadeIn>
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-[#1e293b]">Fechamentos Diarios</h1>
                <Link
                    :href="route('fechamentos.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Novo Fechamento
                </Link>
            </div>
        </FadeIn>

        <FadeIn :delay="80">
        <div class="overflow-hidden rounded-xl bg-white shadow-sm">
            <div v-if="closings.data.length === 0" class="p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-3 text-sm text-gray-400">Nenhum fechamento registrado.</p>
                <Link
                    :href="route('fechamentos.create')"
                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800"
                >
                    Registrar primeiro fechamento
                </Link>
            </div>
            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Data</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Cartao</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Dinheiro</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Receita Total</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Perdas</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr
                        v-for="closing in closings.data"
                        :key="closing.id"
                        class="cursor-pointer transition hover:bg-gray-50"
                        @click="router.visit(route('fechamentos.show', closing.id))"
                    >
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-900">{{ formatDate(closing.closing_date) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                            {{ formatMoney(closing.card_sales_cents) }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                            {{ formatMoney(closing.cash_sales_cents) }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                            {{ formatMoney(closing.total_revenue_cents) }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium" :class="closing.total_waste_cents > 0 ? 'text-red-600' : 'text-gray-400'">
                            {{ closing.total_waste_cents > 0 ? formatMoney(closing.total_waste_cents) : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right" @click.stop>
                            <div class="flex items-center justify-end gap-2">
                                <Link
                                    :href="route('fechamentos.edit', closing.id)"
                                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-700"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </Link>
                                <button
                                    @click="confirmDelete(closing)"
                                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
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
        </FadeIn>

        <!-- Pagination -->
        <FadeIn v-if="closings.data.length > 0 && closings.links" :delay="160">
            <div class="mt-4 flex justify-center">
                <nav class="flex items-center gap-1">
                    <template v-for="link in closings.links" :key="link.label">
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
            :show="deleteTarget !== null"
            :processing="deleting"
            title="Excluir fechamento"
            :message="`Tem certeza que deseja excluir o fechamento de ${deleteTarget ? formatDate(deleteTarget.closing_date) : ''}?`"
            @close="deleteTarget = null"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
