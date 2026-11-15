<script setup>
import { computed } from 'vue';

const props = defineProps({
    percent: {
        type: Number,
        default: null,
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md'].includes(v),
    },
});

const color = computed(() => {
    if (props.percent === null || props.percent === undefined) return 'bg-gray-100 text-gray-600';
    if (props.percent < 45) return 'bg-green-100 text-green-700';
    if (props.percent <= 60) return 'bg-yellow-100 text-yellow-700';
    return 'bg-red-100 text-red-700';
});

const sizeClass = computed(() => {
    return props.size === 'sm' ? 'px-1.5 py-0.5 text-xs' : 'px-2.5 py-1 text-sm';
});

const display = computed(() => {
    if (props.percent === null || props.percent === undefined) return '--';
    return `${props.percent.toFixed(1)}%`;
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-full font-semibold transition-colors duration-300"
        :class="[color, sizeClass]"
    >
        {{ display }}
    </span>
</template>
