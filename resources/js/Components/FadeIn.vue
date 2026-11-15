<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    delay: { type: Number, default: 0 },
    duration: { type: Number, default: 400 },
    from: { type: String, default: 'bottom' },
});

const visible = ref(false);

onMounted(() => {
    setTimeout(() => { visible.value = true; }, props.delay);
});

const translateClass = {
    bottom: 'translate-y-4',
    top: '-translate-y-4',
    left: '-translate-x-4',
    right: 'translate-x-4',
    none: '',
};
</script>

<template>
    <div
        class="transition-all ease-out"
        :style="{ transitionDuration: duration + 'ms' }"
        :class="visible
            ? 'opacity-100 translate-y-0 translate-x-0'
            : `opacity-0 ${translateClass[from]}`"
    >
        <slot />
    </div>
</template>
