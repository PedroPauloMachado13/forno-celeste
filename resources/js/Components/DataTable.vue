<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    columns: {
        type: Array,
        required: true,
        // Each column: { key: string, label: string, align?: 'left'|'center'|'right', class?: string }
    },
    rows: {
        type: Array,
        default: () => [],
    },
    searchable: {
        type: Boolean,
        default: true,
    },
    searchPlaceholder: {
        type: String,
        default: 'Buscar...',
    },
    searchKeys: {
        type: Array,
        default: () => ['name'],
    },
    emptyMessage: {
        type: String,
        default: 'Nenhum registro encontrado.',
    },
});

const search = ref('');

const filteredRows = computed(() => {
    if (!search.value.trim()) return props.rows;
    const term = search.value.toLowerCase().trim();
    return props.rows.filter((row) => {
        return props.searchKeys.some((key) => {
            const value = key.split('.').reduce((obj, k) => obj?.[k], row);
            return String(value || '').toLowerCase().includes(term);
        });
    });
});
</script>

<template>
    <div>
        <div v-if="searchable" class="mb-4">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    :placeholder="searchPlaceholder"
                    class="block w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                />
            </div>
        </div>

        <!-- Mobile cards (when mobile-row slot is provided) -->
        <div v-if="$slots['mobile-row']" class="sm:hidden">
            <div v-if="filteredRows.length === 0" class="overflow-hidden rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
                <p class="text-sm text-gray-400">{{ emptyMessage }}</p>
            </div>
            <div v-else class="space-y-3">
                <div v-for="(row, index) in filteredRows" :key="'mobile-' + (row.id || index)">
                    <slot name="mobile-row" :row="row" :index="index" />
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" :class="{ 'hidden sm:block': $slots['mobile-row'] }">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                v-for="col in columns"
                                :key="col.key"
                                class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
                                :class="[
                                    col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                                    col.class || '',
                                ]"
                            >
                                {{ col.label }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="(row, index) in filteredRows"
                            :key="row.id || index"
                            class="transition-colors hover:bg-indigo-50/40"
                        >
                            <slot name="row" :row="row" :index="index" />
                        </tr>
                        <tr v-if="filteredRows.length === 0">
                            <td :colspan="columns.length" class="px-4 py-8 text-center text-sm text-gray-400">
                                {{ emptyMessage }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
