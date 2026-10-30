<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Bem-vindo" />

    <div class="min-h-screen bg-amber-50 flex items-center justify-center">
        <div class="text-center">
            <h1 class="text-5xl font-bold text-amber-900 mb-4">🍞 Forno Celeste</h1>
            <p class="text-xl text-amber-700 mb-8">Sistema de Gestão da Padaria</p>
            <div class="text-sm text-amber-600">
                Laravel v{{ laravelVersion }} &middot; PHP v{{ phpVersion }}
            </div>
        </div>
    </div>
</template>
