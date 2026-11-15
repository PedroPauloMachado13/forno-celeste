<script setup>
import Modal from './Modal.vue';

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirmar exclusão',
    },
    message: {
        type: String,
        default: 'Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.',
    },
    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <Modal :show="show" max-width="sm" @close="emit('close')">
        <div class="text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <h3 class="mb-2 text-lg font-semibold text-gray-900">{{ title }}</h3>
            <p class="mb-6 text-sm text-gray-500">{{ message }}</p>
            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-center sm:gap-3">
                <button
                    @click="emit('close')"
                    :disabled="processing"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 sm:w-auto sm:py-2"
                >
                    Cancelar
                </button>
                <button
                    @click="emit('confirm')"
                    :disabled="processing"
                    class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 disabled:opacity-50 sm:w-auto sm:py-2"
                >
                    {{ processing ? 'Excluindo...' : 'Excluir' }}
                </button>
            </div>
        </div>
    </Modal>
</template>
