<script setup>
import { ref, computed } from 'vue';
import { useMoney } from '@/Composables/useMoney.js';

const props = defineProps({
    cmvCents: {
        type: Number,
        default: 0,
    },
    cardMachineFeePercent: {
        type: Number,
        default: 3.15,
    },
    showSelectButton: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['select-price']);

const { formatMoney } = useMoney();

const customMargin = ref(50);

const scenarios = computed(() => {
    const fee = props.cardMachineFeePercent;
    const configs = [
        { name: 'Agressivo', margin: 45, recommended: false },
        { name: 'Padrao', margin: 55, recommended: true },
        { name: 'Premium', margin: 65, recommended: false },
    ];

    return configs.map((cfg) => calculate(cfg.name, cfg.margin, fee, cfg.recommended));
});

const customScenario = computed(() => {
    return calculate('Personalizado', customMargin.value, props.cardMachineFeePercent, false);
});

function calculate(name, margin, fee, recommended) {
    const cmv = props.cmvCents;
    if (cmv <= 0 || margin + fee >= 100) {
        return { name, margin, recommended, suggestedPriceCents: 0, marginPerUnitCents: 0, netMarginPercent: 0 };
    }
    const suggestedPriceCents = Math.round(cmv / (1 - margin / 100 - fee / 100));
    const marginPerUnitCents = suggestedPriceCents - cmv;
    const netMarginPercent = suggestedPriceCents > 0
        ? ((marginPerUnitCents / suggestedPriceCents) * 100)
        : 0;
    return { name, margin, recommended, suggestedPriceCents, marginPerUnitCents, netMarginPercent };
}

function selectPrice(priceCents) {
    emit('select-price', priceCents);
}
</script>

<template>
    <div>
        <h4 class="mb-3 text-sm font-semibold text-gray-700">Simulador de Precos</h4>

        <div class="grid grid-cols-1 gap-3">
            <div
                v-for="scenario in scenarios"
                :key="scenario.name"
                class="rounded-lg border p-4 transition-shadow hover:shadow-md"
                :class="scenario.recommended
                    ? 'border-indigo-300 bg-indigo-50 ring-2 ring-indigo-200'
                    : 'border-gray-200 bg-white'"
            >
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">{{ scenario.name }}</span>
                    <span
                        v-if="scenario.recommended"
                        class="rounded-full bg-indigo-600 px-2 py-0.5 text-xs font-medium text-white"
                    >
                        Recomendado
                    </span>
                </div>
                <div class="space-y-1 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <span>Preco sugerido:</span>
                        <span class="font-medium text-gray-900">{{ formatMoney(scenario.suggestedPriceCents) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Margem/un:</span>
                        <span class="font-medium text-gray-900">{{ formatMoney(scenario.marginPerUnitCents) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Margem liquida:</span>
                        <span class="font-medium text-gray-900">{{ scenario.netMarginPercent.toFixed(1) }}%</span>
                    </div>
                </div>
                <button
                    v-if="showSelectButton && scenario.suggestedPriceCents > 0"
                    type="button"
                    @click="selectPrice(scenario.suggestedPriceCents)"
                    class="mt-3 w-full rounded-lg border border-indigo-300 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-50"
                >
                    Usar este preco
                </button>
            </div>
        </div>

        <div class="mt-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4">
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Margem personalizada:</label>
                <div class="flex items-center gap-2">
                    <input
                        v-model.number="customMargin"
                        type="number"
                        min="1"
                        max="95"
                        step="1"
                        class="w-20 rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-center shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                    />
                    <span class="text-sm text-gray-500">%</span>
                </div>
            </div>
            <div v-if="customScenario.suggestedPriceCents > 0" class="mt-3 space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Preco:</span>
                    <span class="font-medium text-gray-900">{{ formatMoney(customScenario.suggestedPriceCents) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Margem/un:</span>
                    <span class="font-medium text-gray-900">{{ formatMoney(customScenario.marginPerUnitCents) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Margem liq.:</span>
                    <span class="font-medium text-gray-900">{{ customScenario.netMarginPercent.toFixed(1) }}%</span>
                </div>
                <button
                    v-if="showSelectButton"
                    type="button"
                    @click="selectPrice(customScenario.suggestedPriceCents)"
                    class="mt-2 w-full rounded-lg border border-indigo-300 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-50"
                >
                    Usar este preco
                </button>
            </div>
        </div>
    </div>
</template>
