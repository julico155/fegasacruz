// resources/js/Pages/Search/Results.vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Definición de las propiedades (props) que este componente Vue espera recibir.
const props = defineProps({
    query: String, // La palabra clave buscada
    results: Object, // Los resultados de la búsqueda, agrupados por tipo
});

// Funcion para verificar si hay resultados en alguna categoria
const hasResults = Object.values(props.results).some(arr => arr.length > 0);
</script>

<template>
    <Head :title="`Resultados para '${query}'`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Resultados de Búsqueda para: "{{ query }}"
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 rounded-lg shadow-lg">
                    <div v-if="!query" class="text-center text-gray-600 py-10">
                        <p class="text-lg">Por favor, introduce una palabra clave para buscar.</p>
                    </div>
                    <div v-else-if="!hasResults" class="text-center text-gray-600 py-10">
                        <p class="text-lg">No se encontraron resultados para "{{ query }}".</p>
                    </div>
                    <div v-else>
                        <!-- Resultados de Productos -->
                        <div v-if="results.productos && results.productos.length > 0" class="mb-8">
                            <h3 class="text-2xl font-bold mb-4 text-gray-800">Productos</h3>
                            <ul class="space-y-3">
                                <li v-for="item in results.productos" :key="item.id" class="p-4 bg-gray-50 rounded-md shadow-sm border border-gray-200">
                                    <Link :href="item.url" class="text-indigo-600 hover:text-indigo-800 font-medium text-lg">
                                        {{ item.nombre }}
                                    </Link>
                                    <p class="text-gray-600 text-sm">{{ item.descripcion }}</p>
                                </li>
                            </ul>
                        </div>

                        <!-- Resultados de Categorías -->
                        <div v-if="results.categorias && results.categorias.length > 0" class="mb-8">
                            <h3 class="text-2xl font-bold mb-4 text-gray-800">Categorías</h3>
                            <ul class="space-y-3">
                                <li v-for="item in results.categorias" :key="item.id" class="p-4 bg-gray-50 rounded-md shadow-sm border border-gray-200">
                                    <Link :href="item.url" class="text-indigo-600 hover:text-indigo-800 font-medium text-lg">
                                        {{ item.nombre }}
                                    </Link>
                                    <p class="text-gray-600 text-sm">{{ item.descripcion }}</p>
                                </li>
                            </ul>
                        </div>

                        <!-- Resultados de Movimientos de Inventario -->
                        <div v-if="results.inventario && results.inventario.length > 0" class="mb-8">
                            <h3 class="text-2xl font-bold mb-4 text-gray-800">Movimientos de Inventario</h3>
                            <ul class="space-y-3">
                                <li v-for="item in results.inventario" :key="item.id" class="p-4 bg-gray-50 rounded-md shadow-sm border border-gray-200">
                                    <Link :href="item.url" class="text-indigo-600 hover:text-indigo-800 font-medium text-lg">
                                        {{ item.tipo }} (Producto: {{ item.producto_nombre }})
                                    </Link>
                                    <p class="text-gray-600 text-sm">Cantidad: {{ item.cantidad }} - Observación: {{ item.observacion || 'N/A' }}</p>
                                </li>
                            </ul>
                        </div>

                        <!-- Resultados de Usuarios -->
                        <div v-if="results.usuarios && results.usuarios.length > 0" class="mb-8">
                            <h3 class="text-2xl font-bold mb-4 text-gray-800">Usuarios</h3>
                            <ul class="space-y-3">
                                <li v-for="item in results.usuarios" :key="item.id" class="p-4 bg-gray-50 rounded-md shadow-sm border border-gray-200">
                                    <Link :href="item.url" class="text-indigo-600 hover:text-indigo-800 font-medium text-lg">
                                        {{ item.name }} ({{ item.email }})
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

