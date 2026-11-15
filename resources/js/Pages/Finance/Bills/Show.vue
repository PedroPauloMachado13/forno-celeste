<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FadeIn from '@/Components/FadeIn.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    bill: Object,
});

const { formatMoney } = useMoney();

const b = props.bill.data;

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

function formatDateTime(isoStr) {
    if (!isoStr) return '—';
    const d = new Date(isoStr);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const hours = String(d.getHours()).padStart(2, '0');
    const mins = String(d.getMinutes()).padStart(2, '0');
    return `${day}/${month}/${year} as ${hours}:${mins}`;
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

const paying = ref(false);

function payBill() {
    paying.value = true;
    router.patch(route('contas.pay', b.id), {}, {
        onFinish: () => { paying.value = false; },
    });
}

function unpayBill() {
    paying.value = true;
    router.patch(route('contas.unpay', b.id), {}, {
        onFinish: () => { paying.value = false; },
    });
}

// Delete modal
const showDeleteModal = ref(false);
const deleting = ref(false);

function executeDelete() {
    deleting.value = true;
    router.delete(route('contas.destroy', b.id), {
        onFinish: () => {
            deleting.value = false;
            showDeleteModal.value = false;
        },
    });
}
</script>

<template>
    <AppLayout>
        <Head :title="`Conta - ${b.description}`" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('contas.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Contas a Pagar
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">{{ b.description }}</h1>
            </div>
        </FadeIn>

        <FadeIn :delay="80">
        <div class="mx-auto max-w-2xl">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <dl class="space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Descricao</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ b.description }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Custo Fixo</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ b.fixed_cost?.name || '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Tipo de Despesa</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ b.bill_type_label || '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Valor</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ formatMoney(b.amount_cents) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Mes de Referencia</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ formatRefMonth(b.reference_month) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Vencimento</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ formatDate(b.due_date) }}</dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                        <dt class="text-sm text-gray-500">Status</dt>
                        <dd>
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="statusClass(b.status)"
                            >
                                {{ statusLabel(b.status) }}
                            </span>
                        </dd>
                    </div>
                    <div v-if="b.paid_at" class="flex justify-between">
                        <dt class="text-sm text-gray-500">Pago em</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ formatDateTime(b.paid_at) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex flex-wrap gap-3">
                <button
                    v-if="b.status !== 'paid'"
                    @click="payBill"
                    :disabled="paying"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-50"
                >
                    {{ paying ? 'Processando...' : 'Marcar como Pago' }}
                </button>
                <button
                    v-if="b.status === 'paid'"
                    @click="unpayBill"
                    :disabled="paying"
                    class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-100 disabled:opacity-50"
                >
                    {{ paying ? 'Processando...' : 'Desfazer Pagamento' }}
                </button>
                <Link
                    :href="route('contas.edit', b.id)"
                    class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800"
                >
                    Editar
                </Link>
                <button
                    @click="showDeleteModal = true"
                    class="rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-50"
                >
                    Excluir
                </button>
                <Link
                    :href="route('contas.index')"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    Voltar
                </Link>
            </div>
        </div>
        </FadeIn>

        <DeleteConfirmModal
            :show="showDeleteModal"
            :processing="deleting"
            :message="`Tem certeza que deseja excluir a conta '${b.description}'?`"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
