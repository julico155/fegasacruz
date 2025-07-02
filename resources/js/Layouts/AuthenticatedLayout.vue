<script setup>
import { ref, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3'; // Importa 'router'

const showingNavigationDropdown = ref(false);

const page = usePage();
const user = page.props.auth.user;

const isAdmin = user && user.tipo === 'administrativo';

// --- Lógica de Temas / Modos de Visualización ---
const themes = {
    light: {
        name: 'Claro',
        containerClass: 'bg-gray-100 text-gray-900',
        navClass: 'bg-white border-gray-100',
        headerClass: 'bg-white shadow',
        dropdownClass: 'bg-white text-gray-500 hover:text-gray-700',
    },
    dark: {
        name: 'Oscuro',
        containerClass: 'bg-gray-900 text-gray-100',
        navClass: 'bg-gray-800 border-gray-700',
        headerClass: 'bg-gray-800 shadow',
        dropdownClass: 'bg-gray-800 text-gray-400 hover:text-gray-300',
    },
    child: {
        name: 'Niño',
        containerClass: 'bg-blue-100 text-blue-800',
        navClass: 'bg-blue-200 border-blue-300',
        headerClass: 'bg-blue-200 shadow',
        dropdownClass: 'bg-blue-200 text-blue-700 hover:text-blue-900',
    },
    teen: {
        name: 'Adolescente',
        containerClass: 'bg-purple-100 text-purple-800',
        navClass: 'bg-purple-200 border-purple-300',
        headerClass: 'bg-purple-200 shadow',
        dropdownClass: 'bg-purple-200 text-purple-700 hover:text-purple-900',
    },
};

const currentThemeKey = ref('light'); // Tema por defecto

// Función para aplicar el tema y guardarlo en localStorage
const applyTheme = (themeKey) => {
    currentThemeKey.value = themeKey;
    localStorage.setItem('appTheme', themeKey);
    // Remover todas las clases de tema existentes del body para evitar conflictos
    document.body.className = '';
    // Añadir la clase del tema al body
    document.body.classList.add(themes[themeKey].containerClass.split(' ')[0]); // Solo bg-color
    document.body.classList.add(themes[themeKey].containerClass.split(' ')[1]); // Solo text-color
};

// Cargar el tema desde localStorage al montar el componente
onMounted(() => {
    const savedTheme = localStorage.getItem('appTheme');
    if (savedTheme && themes[savedTheme]) {
        applyTheme(savedTheme);
    } else {
        applyTheme('light'); // Aplicar tema claro por defecto si no hay uno guardado
    }
});

// --- Lógica de Búsqueda Global ---
const searchTerm = ref('');

const performSearch = () => {
    if (searchTerm.value.trim()) {
        router.get(route('global.search', { query: searchTerm.value }));
    }
};
</script>

<template>
    <div :class="themes[currentThemeKey].containerClass">
        <div class="min-h-screen">
            <nav
                :class="[
                    'border-b',
                    themes[currentThemeKey].navClass,
                    currentThemeKey === 'dark' ? 'dark:border-gray-700 dark:bg-gray-800' : ''
                ]"
            >
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <img src="/inf513/grupo01sa/proyecto2/fegasacruz/public/images/logo.jpg" alt="Logo de la Aplicación" class="h-20 w-20 object-contain" />
                                </Link>
                            </div>

                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    v-if="isAdmin"
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    v-if="isAdmin"
                                    :href="route('admin.categorias.index')"
                                    :active="route().current('admin.categorias.index')"
                                >
                                    Categorias
                                </NavLink>
                                <NavLink
                                    v-if="isAdmin"
                                    :href="route('admin.productos.index')"
                                    :active="route().current('admin.productos.index')"
                                >
                                    Productos
                                </NavLink>
                                <NavLink
                                    v-if="isAdmin"
                                    :href="route('admin.inventario.index')"
                                    :active="route().current('admin.inventario.index')"
                                >
                                    Inventario
                                </NavLink>

                                <NavLink
                                    :href="route('shop.index')"
                                    :active="route().current('shop.index')"
                                >
                                    Tienda
                                </NavLink>
                                <NavLink
                                    :href="route('cart.show')"
                                    :active="route().current('cart.show')"
                                >
                                    Carrito
                                </NavLink>
                                <NavLink
                                    :href="route('my.sales.index')"
                                    :active="route().current('my.sales.index')"
                                >
                                    Mis Compras
                                </NavLink>
                                <NavLink
                                    v-if="isAdmin"
                                    :href="route('admin.sales.index')"
                                    :active="route().current('admin.sales.index')"
                                >
                                    Ventas (Admin)
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Campo de Búsqueda Global -->
                            <div class="relative me-4">
                                <input
                                    type="search"
                                    v-model="searchTerm"
                                    @keyup.enter="performSearch"
                                    placeholder="Buscar..."
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm"
                                    :class="[currentThemeKey === 'dark' ? 'bg-gray-700 text-gray-200 border-gray-600 placeholder-gray-400' : 'bg-white text-gray-900 border-gray-300 placeholder-gray-500']"
                                >
                                <button @click="performSearch" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Dropdown de Selección de Tema -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                :class="['inline-flex items-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none', themes[currentThemeKey].dropdownClass]"
                                            >
                                                Modo: {{ themes[currentThemeKey].name }}
                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink v-for="(theme, key) in themes" :key="key" @click="applyTheme(key)" as="button">
                                            {{ theme.name }}
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Settings Dropdown (existente) -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                :class="['inline-flex items-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none', themes[currentThemeKey].dropdownClass]"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                :class="['inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none', currentThemeKey === 'dark' ? 'dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400' : '']"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            v-if="isAdmin"
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="isAdmin"
                            :href="route('admin.categorias.index')"
                            :active="route().current('admin.categorias.index')"
                        >
                            Categorias
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="isAdmin"
                            :href="route('admin.productos.index')"
                            :active="route().current('admin.productos.index')"
                        >
                            Productos
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="isAdmin"
                            :href="route('admin.inventario.index')"
                            :active="route().current('admin.inventario.index')"
                        >
                            Inventario
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            :href="route('shop.index')"
                            :active="route().current('shop.index')"
                        >
                            Tienda
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('cart.show')"
                            :active="route().current('cart.show')"
                        >
                            Carrito
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('my.sales.index')"
                            :active="route().current('my.sales.index')"
                        >
                            Mis Compras
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="isAdmin"
                            :href="route('admin.sales.index')"
                            :active="route().current('admin.sales.index')"
                        >
                            Ventas (Admin)
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        :class="['border-t', currentThemeKey === 'dark' ? 'border-gray-600' : 'border-gray-200', 'pb-1 pt-4']"
                    >
                        <div class="px-4">
                            <div
                                :class="['text-base font-medium', currentThemeKey === 'dark' ? 'text-gray-200' : 'text-gray-800']"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <!-- Campo de Búsqueda Global (Responsive) -->
                            <ResponsiveNavLink v-if="isAdmin" as="div">
                                <div class="relative w-full">
                                    <input
                                        type="search"
                                        v-model="searchTerm"
                                        @keyup.enter="performSearch"
                                        placeholder="Buscar..."
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm"
                                        :class="[currentThemeKey === 'dark' ? 'bg-gray-700 text-gray-200 border-gray-600 placeholder-gray-400' : 'bg-white text-gray-900 border-gray-300 placeholder-gray-500']"
                                    >
                                    <button @click="performSearch" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </ResponsiveNavLink>

                            <!-- Responsive Theme Selector -->
                            <ResponsiveNavLink as="button">
                                <Dropdown align="left" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md w-full">
                                            <button
                                                type="button"
                                                :class="['inline-flex items-center justify-between w-full rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none', themes[currentThemeKey].dropdownClass]"
                                            >
                                                Modo: {{ themes[currentThemeKey].name }}
                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <DropdownLink v-for="(theme, key) in themes" :key="key" @click="applyTheme(key)" as="button">
                                            {{ theme.name }}
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </ResponsiveNavLink>

                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                :class="[themes[currentThemeKey].headerClass, currentThemeKey === 'dark' ? 'dark:bg-gray-800' : '']"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
