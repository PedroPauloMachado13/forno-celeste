<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: Number,
        default: 0,
    },
    label: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const displayValue = ref('');

function centsToDisplay(cents) {
    if (cents === null || cents === undefined || cents === 0) return '';
    const value = (cents / 100).toFixed(2).replace('.', ',');
    return value;
}

function displayToCents(str) {
    if (!str) return 0;
    const cleaned = str.replace(/[^\d,]/g, '').replace(',', '.');
    const value = parseFloat(cleaned);
    if (isNaN(value)) return 0;
    return Math.round(value * 100);
}

function onInput(event) {
    let val = event.target.value;
    // Allow only digits and one comma
    val = val.replace(/[^\d,]/g, '');
    // Only allow one comma
    const parts = val.split(',');
    if (parts.length > 2) {
        val = parts[0] + ',' + parts.slice(1).join('');
    }
    // Limit decimal places to 2
    if (parts.length === 2 && parts[1].length > 2) {
        val = parts[0] + ',' + parts[1].substring(0, 2);
    }
    displayValue.value = val;
    // Force DOM input to match the cleaned value
    event.target.value = val;
    emit('update:modelValue', displayToCents(val));
}

onMounted(() => {
    displayValue.value = centsToDisplay(props.modelValue);
});

watch(() => props.modelValue, (newVal) => {
    const currentCents = displayToCents(displayValue.value);
    if (currentCents !== newVal) {
        displayValue.value = centsToDisplay(newVal);
    }
});
</script>

<template>
    <div>
        <label v-if="label" :for="id" class="mb-1 block text-sm font-medium text-gray-700">
            {{ label }}
        </label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 text-sm">
                R$
            </span>
            <input
                :id="id"
                type="text"
                inputmode="decimal"
                :value="displayValue"
                @input="onInput"
                placeholder="0,00"
                class="block w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-200': error }"
            />
        </div>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
    </div>
</template>
