<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FadeIn from '@/Components/FadeIn.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';

const props = defineProps({
    holiday: Object,
});

const h = props.holiday.data;

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const [year, month, day] = dateStr.split('-');
    return `${day}/${month}/${year}`;
}

// Delete modal
const showDeleteModal = ref(false);
const deleting = ref(false);

function executeDelete() {
    deleting.value = true;
    router.delete(route('feriados.destroy', h.id), {
        onFinish: () => {
            deleting.value = false;
            showDeleteModal.value = false;
        },
    });
}
</script>

<template>
    <AppLayout>
        <Head :title="`Feriado - ${h.name}`" />

        <FadeIn>
            <div class="mb-6">
                <Link :href="route('feriados.index')" class="text-sm text-gray-500 transition hover:text-indigo-700">
                    &larr; Voltar para Feriados
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-[#1e293b]">{{ h.name }}</h1>
            </div>
        </FadeIn>

        <FadeIn :delay="80">
        <div class="mx-auto max-w-2xl">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <dl class="space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Data</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ formatDate(h.date) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Nome</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ h.name }}</dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                        <dt class="text-sm text-gray-500">Status</dt>
                        <dd>
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="h.was_open
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : 'bg-gray-100 text-gray-500'"
                            >
                                {{ h.was_open ? 'Aberto' : 'Fechado' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex gap-3">
                <Link
                    :href="route('feriados.edit', h.id)"
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
                    :href="route('feriados.index')"
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
            :message="`Tem certeza que deseja excluir o feriado '${h.name}'?`"
            @close="showDeleteModal = false"
            @confirm="executeDelete"
        />
    </AppLayout>
</template>
