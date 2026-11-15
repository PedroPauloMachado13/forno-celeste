<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('success');
let timeout = null;

function show(msg, msgType) {
    message.value = msg;
    type.value = msgType;
    visible.value = true;
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        visible.value = false;
    }, 4000);
}

function dismiss() {
    visible.value = false;
}

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            show(flash.success, 'success');
        } else if (flash?.error) {
            show(flash.error, 'error');
        }
    },
    { immediate: true }
);
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-[-100%] opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-[-100%] opacity-0"
    >
        <div
            v-if="visible"
            class="fixed top-4 right-4 z-50 max-w-sm rounded-lg px-4 py-3 shadow-lg"
            :class="{
                'bg-green-50 border border-green-200 text-green-800': type === 'success',
                'bg-red-50 border border-red-200 text-red-800': type === 'error',
            }"
        >
            <div class="flex items-center gap-2">
                <svg v-if="type === 'success'" class="h-5 w-5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="h-5 w-5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium">{{ message }}</p>
                <button @click="dismiss" class="ml-auto text-current opacity-50 hover:opacity-100">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </Transition>
</template>
