<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router, useForm } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';

const logoutForm = useForm({});

const sidebarOpen = ref(false);
const page = usePage();
const transitioning = ref(false);

const currentUrl = computed(() => page.url);

const billsOverdue = computed(() => page.props.bills_overdue ?? 0);
const billsDueSoon = computed(() => page.props.bills_due_soon ?? 0);
const userName = computed(() => page.props.auth?.user?.name ?? '');

function logout() {
    logoutForm.post(route('logout'));
}

let removeStartListener;
let removeFinishListener;

onMounted(() => {
    removeStartListener = router.on('before', () => {
        transitioning.value = true;
    });
    removeFinishListener = router.on('finish', () => {
        setTimeout(() => { transitioning.value = false; }, 50);
    });
});

onUnmounted(() => {
    removeStartListener?.();
    removeFinishListener?.();
});

const navGroups = [
    {
        label: 'Catalogo',
        icon: 'catalog',
        items: [
            { name: 'Produtos', routeName: 'produtos.index', icon: 'product' },
            { name: 'Receitas', routeName: 'receitas.index', icon: 'recipe' },
            { name: 'Ingredientes', routeName: 'ingredientes.index', icon: 'ingredient' },
            { name: 'Embalagens', routeName: 'embalagens.index', icon: 'packaging' },
            { name: 'Categorias', routeName: 'categorias.index', icon: 'category' },
        ],
    },
    {
        label: 'Vendas',
        icon: 'sales',
        items: [
            { name: 'Dashboard Vendas', routeName: 'vendas.dashboard', icon: 'sales-dashboard' },
            { name: 'Fechamentos', routeName: 'fechamentos.index', icon: 'closing' },
        ],
    },
    {
        label: 'Financeiro',
        icon: 'finance',
        items: [
            { name: 'Dashboard', routeName: 'financeiro.dashboard', icon: 'finance-dashboard' },
            { name: 'Contas a Pagar', routeName: 'contas.index', icon: 'bill' },
            { name: 'Custos Fixos', routeName: 'custos-fixos.index', icon: 'fixed-cost' },
            { name: 'Feriados', routeName: 'feriados.index', icon: 'holiday' },
        ],
    },
];

const expandedGroups = ref(
    navGroups.reduce((acc, group) => {
        acc[group.label] = !group.soon;
        return acc;
    }, {}),
);

function toggleGroup(label) {
    expandedGroups.value[label] = !expandedGroups.value[label];
}

function isActive(routeName) {
    try {
        const routeUrl = route(routeName);
        const pathname = new URL(routeUrl).pathname;
        if (pathname === '/') return currentUrl.value === '/';
        return currentUrl.value.startsWith(pathname);
    } catch {
        return false;
    }
}

function isGroupActive(group) {
    return group.items.some((item) => isActive(item.routeName));
}
</script>

<template>
    <div class="flex min-h-screen bg-[#f8fafc]">
        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-30 bg-black/50 lg:hidden"
                @click="sidebarOpen = false"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-[#1a1a2e] transition-transform duration-300 lg:translate-x-0 lg:static lg:z-auto"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo -->
            <div class="flex items-center justify-center border-b border-white/10 py-5">
                <Link :href="route('home')">
                    <img src="/images/logo.png" alt="Forno Celeste" class="h-16 w-16 rounded-full object-cover transition hover:scale-105" />
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <!-- Dashboard link -->
                <Link
                    :href="route('home')"
                    class="mb-3 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-150"
                    :class="isActive('home')
                        ? 'bg-indigo-700 text-white shadow-md'
                        : 'text-gray-300 hover:bg-white/10 hover:text-white'"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Painel
                </Link>

                <!-- Groups -->
                <div v-for="group in navGroups" :key="group.label" class="mb-1">
                    <!-- Group header -->
                    <button
                        @click="toggleGroup(group.label)"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wider transition-colors duration-150"
                        :class="[
                            group.soon ? 'text-gray-600 cursor-default' : 'text-gray-400 hover:text-gray-200',
                            isGroupActive(group) ? 'text-indigo-300' : '',
                        ]"
                    >
                        <div class="flex items-center gap-2">
                            <!-- Group icons -->
                            <svg v-if="group.icon === 'catalog'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <svg v-if="group.icon === 'sales'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                            </svg>
                            <svg v-if="group.icon === 'finance'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ group.label }}
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span v-if="group.soon" class="rounded bg-white/5 px-1.5 py-0.5 text-[10px] font-medium text-gray-500">Em breve</span>
                            <svg
                                v-else
                                class="h-3.5 w-3.5 transition-transform duration-200"
                                :class="expandedGroups[group.label] ? 'rotate-90' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </button>

                    <!-- Group items -->
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 max-h-0"
                        enter-to-class="opacity-100 max-h-48"
                        leave-active-class="transition-all duration-150 ease-in"
                        leave-from-class="opacity-100 max-h-48"
                        leave-to-class="opacity-0 max-h-0"
                    >
                        <div v-show="expandedGroups[group.label] && !group.soon" class="mt-1 space-y-0.5 overflow-hidden pl-3">
                            <Link
                                v-for="item in group.items"
                                :key="item.routeName"
                                :href="route(item.routeName)"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors duration-150"
                                :class="isActive(item.routeName)
                                    ? 'bg-indigo-700 text-white shadow-md'
                                    : 'text-gray-300 hover:bg-white/10 hover:text-white'"
                                @click="sidebarOpen = false"
                            >
                                <svg v-if="item.icon === 'ingredient'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <svg v-if="item.icon === 'packaging'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3L2 7l10 4 10-4-10-4zM2 17l10 4 10-4M2 12l10 4 10-4" />
                                </svg>
                                <svg v-if="item.icon === 'category'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <svg v-if="item.icon === 'recipe'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                <svg v-if="item.icon === 'product'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                                <svg v-if="item.icon === 'sales-dashboard'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <svg v-if="item.icon === 'closing'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <svg v-if="item.icon === 'finance-dashboard'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <svg v-if="item.icon === 'bill'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                </svg>
                                <svg v-if="item.icon === 'fixed-cost'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z" />
                                </svg>
                                <svg v-if="item.icon === 'holiday'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                                <span class="flex-1">{{ item.name }}</span>
                                <!-- Bill notification badge -->
                                <span
                                    v-if="item.icon === 'bill' && billsOverdue > 0"
                                    class="ml-auto inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1.5 text-[10px] font-bold text-white"
                                >
                                    {{ billsOverdue }}
                                </span>
                                <span
                                    v-else-if="item.icon === 'bill' && billsDueSoon > 0"
                                    class="ml-auto inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-amber-500 px-1.5 text-[10px] font-bold text-white"
                                >
                                    {{ billsDueSoon }}
                                </span>
                            </Link>
                        </div>
                    </Transition>
                </div>
            </nav>

            <!-- User / Logout -->
            <div class="border-t border-white/10 px-4 py-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">
                            {{ userName.charAt(0).toUpperCase() }}
                        </div>
                        <span class="truncate text-sm text-gray-300">{{ userName }}</span>
                    </div>
                    <button
                        @click="logout"
                        class="flex-shrink-0 rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-white/10 hover:text-white"
                        title="Sair"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex flex-1 flex-col">
            <!-- Top bar (mobile) -->
            <header class="flex h-16 items-center gap-4 border-b border-gray-200 bg-white px-4 shadow-sm lg:hidden">
                <button
                    @click="sidebarOpen = true"
                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <Link :href="route('home')">
                    <img src="/images/logo.png" alt="Forno Celeste" class="h-10 w-10 rounded-full object-cover" />
                </Link>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <div
                    class="transition-all duration-200 ease-out"
                    :class="transitioning ? 'opacity-0 translate-y-1' : 'opacity-100 translate-y-0'"
                >
                    <slot />
                </div>
            </main>
        </div>

        <FlashMessage />
    </div>
</template>
