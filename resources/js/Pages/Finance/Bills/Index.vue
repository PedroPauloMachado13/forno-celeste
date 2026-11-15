<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import FadeIn from '@/Components/FadeIn.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    bills: Object,
    upcoming: Object,
    currentMonth: String,
});

// Month navigation
const selectedMonth = ref(props.currentMonth);

const selectedMonthLabel = computed(() => {
    if (!selectedMonth.value) return '';
    const [year, month] = selectedMonth.value.split('-');
    const months = ['Janeiro', 'Fevereiro', 'Marco', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    return `${months[parseInt(month, 10) - 1]} ${year}`;
});

function changeMonth(offset) {
    const [year, month] = selectedMonth.value.split('-').map(Number);
    const d = new Date(year, month - 1 + offset, 1);
    const newMonth = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-01`;
    selectedMonth.value = newMonth;
    router.get(route('contas.index'), { month: newMonth }, { preserveState: true, replace: true });
}

const { formatMoney } = useMoney();

// Upcoming unpaid bills (global, not filtered by month)
const unpaidBills = computed(() =>
    (props.upcoming.data || [])
        .sort((a, b) => {
            if (!a.due_date && !b.due_date) return 0;
            if (!a.due_date) return 1;
            if (!b.due_date) return -1;
            return a.due_date.localeCompare(b.due_date);
        })
);

// Paid bills filtered by selected month
const paidBills = computed(() =>
    props.bills.data.filter(b => b.status === 'paid')
);

const paidColumns = [
    { key: 'description', label: 'Descricao' },
    { key: 'bill_type_label', label: 'Tipo', align: 'center' },
    { key: 'fixed_cost', label: 'Custo Fixo' },
    { key: 'amount_cents', label: 'Valor', align: 'right' },
    { key: 'reference_month', label: 'Mes Ref', align: 'center' },
    { key: 'due_date', label: 'Vencimento', align: 'center' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: '', align: 'right' },
];

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const [year, month, day] = dateStr.split('-');
    return `${day}/${month}/${year}`;
}

function formatRefMonth(dateStr) {
    if (!dateStr) return '—';
    const [year, month] = dateStr.split('-');
    const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    return `${months[parseInt(month, 10) - 1]}/${year}`;
}

function statusLabel(status) {
    const map = { paid: 'Pago', overdue: 'Vencida', pending: 'Pendente' };
    return map[status] || status;
}

function statusClass(status) {
    const map = {
        paid: 'bg-emerald-50 text-emerald-700',
        overdue: 'bg-red-50 text-red-700',
        pending: 'bg-amber-50 text-amber-700',
    };
    return map[status] || 'bg-gray-100 text-gray-500';
}

function billTypeBadgeClass(billType) {
    const map = {
        fixed: 'bg-indigo-50 text-indigo-700',
        supplier: 'bg-amber-50 text-amber-700',
        personal: 'bg-violet-50 text-violet-700',
        tax: 'bg-red-50 text-red-700',
        other: 'bg-gray-100 text-gray-500',
    };
    return map[billType] || 'bg-gray-100 text-gray-500';
}

function daysUntilDue(dueDateStr) {
    if (!dueDateStr) return null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const due = new Date(dueDateStr + 'T00:00:00');
    const diff = Math.ceil((due - today) / (1000 * 60 * 60 * 24));
    return diff;
}

function daysUntilDueLabel(dueDateStr) {
    const days = daysUntilDue(dueDateStr);
    if (days === null) return 'Sem vencimento';
    if (days < 0) return 'Vencida!';
    if (days === 0) return 'Vence hoje';
    if (days === 1) return 'Amanha';
    return `${days} dias`;
}

function payBill(bill) {
    router.patch(route('contas.pay', bill.id));
}

// Delete modal
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const deleting = ref(false);

function confirmDelete(bill) {
    deleteTarget.value = bill;
    showDeleteModal.value = true;
}

function executeDelete() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(route('contas.destroy', deleteTarget.value.id), {
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
        <Head title="Contas a Pagar" />

        <FadeIn>
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#1e293b]">Contas a Pagar</h1>
                    <p class="mt-1 text-sm text-gray-500">Gerencie as contas e pagamentos mensais</p>
                </div>
                <Link
                    :href="route('contas.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 active:bg-indigo-900"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nova Conta
                </Link>
            </div>
        </FadeIn>

        <!-- Month picker -->
        <FadeIn :delay="40">
            <div class="mb-6 flex items-center justify-center gap-4">
                <button
                    @click="changeMonth(-1)"
                    class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <span class="min-w-[180px] text-center text-lg font-semibold text-gray-700">
                    {{ selectedMonthLabel }}
                </span>
                <button
                    @click="changeMonth(1)"
                    class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </FadeIn>

        <!-- Proximos Vencimentos (unpaid bills as cards) -->
        <FadeIn :delay="80">
        <div class="mb-8">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Proximos Vencimentos</h2>

            <div v-if="unpaidBills.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="bill in unpaidBills"
                    :key="bill.id"
                    class="rounded-xl bg-white p-5 shadow-sm transition hover:shadow-md"
                    :class="{ 'ring-2 ring-red-200': daysUntilDue(bill.due_date) !== null && daysUntilDue(bill.due_date) < 0 }"
                >
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 flex-1">
                            <Link
                                :href="route('contas.show', bill.id)"
                                class="text-sm font-semibold text-gray-900 transition hover:text-indigo-700"
                            >
                                {{ bill.description }}
                            </Link>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="billTypeBadgeClass(bill.bill_type)"
                                >
                                    {{ bill.bill_type_label || 'Outro' }}
                                </span>
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="statusClass(bill.status)"
                                >
                                    {{ statusLabel(bill.status) }}
                                </span>
                            </div>
                        </div>
                        <p class="ml-4 text-lg font-bold text-gray-900">{{ formatMoney(bill.amount_cents) }}</p>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            <span>{{ formatDate(bill.due_date) }}</span>
                            <span
                                class="font-semibold"
                                :class="daysUntilDue(bill.due_date) === null ? 'text-gray-400' : daysUntilDue(bill.due_date) < 0 ? 'text-red-600' : daysUntilDue(bill.due_date) <= 3 ? 'text-amber-600' : 'text-gray-500'"
                            >
                                {{ daysUntilDueLabel(bill.due_date) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link
                                :href="route('contas.edit', bill.id)"
                                class="rounded-md px-2 py-1 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50"
                            >
                                Editar
                            </Link>
                            <button
                                @click="payBill(bill)"
                                class="rounded-md bg-emerald-600 px-3 py-1 text-xs font-semibold text-white transition hover:bg-emerald-700"
                            >
                                Pagar
                            </button>
                            <button
                                @click="confirmDelete(bill)"
                                class="rounded-md px-2 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50"
                            >
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-xl border-2 border-dashed border-gray-200 bg-white p-8 text-center">
                <svg class="mx-auto h-10 w-10 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-2 text-sm font-medium text-gray-500">Nenhuma conta pendente</p>
                <p class="text-xs text-gray-400">Todas as contas estao pagas. Otimo trabalho!</p>
            </div>
        </div>
        </FadeIn>

        <!-- Contas Pagas (paid bills in DataTable) -->
        <FadeIn :delay="160">
        <div>
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Contas Pagas</h2>

            <DataTable
                :columns="paidColumns"
                :rows="paidBills"
                :search-keys="['description']"
                search-placeholder="Buscar conta paga..."
            >
                <template #row="{ row }">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ row.description }}</td>
                    <td class="px-4 py-3 text-center">
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="billTypeBadgeClass(row.bill_type)"
                        >
                            {{ row.bill_type_label || 'Outro' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ row.fixed_cost?.name || '—' }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ formatMoney(row.amount_cents) }}</td>
                    <td class="px-4 py-3 text-center text-sm text-gray-600">{{ formatRefMonth(row.reference_month) }}</td>
                    <td class="px-4 py-3 text-center text-sm text-gray-600">{{ formatDate(row.due_date) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="statusClass(row.status)"
                        >
                            {{ statusLabel(row.status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <Link
                                :href="route('contas.show', row.id)"
                                class="rounded-md px-2 py-1 text-xs font-medium text-gray-600 transition hover:bg-gray-50"
                            >
                                Ver
                            </Link>
                            <Link
                                :href="route('contas.edit', row.id)"
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
        </div>
        </FadeIn>

        <DeleteConfirmModal
            :show="showDeleteModal"
            :processing="deleting"
            :message="`Tem certeza que deseja excluir a conta '${deleteTarget?.description}'?`"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
