<template>
    <Head title="Gestión de Categorías" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Categorías</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <!-- Mensajes de éxito/error -->
                    <div v-if="success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ success }}</span>
                    </div>
                    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ error }}</span>
                    </div>

                    <div class="flex justify-end mb-4">
                        <Link :href="route('admin.categorias.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Crear Nueva Categoría
                        </Link>
                    </div>

                    <!-- INICIO DEL FRAGMENTO DEL CONTADOR -->
                    <div class="mb-4 text-gray-700 p-3 bg-gray-50 rounded-lg shadow-sm">
                        Visitas a esta página: <span class="font-bold text-indigo-600">{{ pageVisits }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nombre
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Descripción
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="categoria in categorias.data" :key="categoria.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ categoria.id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ categoria.nombre }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ categoria.descripcion }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('admin.categorias.edit', categoria.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Editar
                                        </Link>
                                        <button @click="confirmDelete(categoria.id)" class="text-red-600 hover:text-red-900">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="categorias.data.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay categorías registradas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación: Añadimos v-if para asegurar que 'categorias' y 'links' existen -->
                    <div v-if="categorias && categorias.links" class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Mostrando {{ categorias.from }} a {{ categorias.to }} de {{ categorias.total }} resultados
                        </div>
                        <div class="flex space-x-1">
                            <template v-for="(link, key) in categorias.links" :key="key">
                                <Link v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-3 py-2 text-sm rounded-md"
                                    :class="{ 'bg-indigo-500 text-white': link.active, 'text-gray-700 hover:bg-gray-100': !link.active, 'opacity-50 cursor-not-allowed': !link.url }"
                                    :preserve-scroll="true"
                                ></Link>
                                <span v-else
                                    v-html="link.label"
                                    class="px-3 py-2 text-sm rounded-md opacity-50 cursor-not-allowed text-gray-700"
                                ></span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
    categorias: Object,
    success: String,
    error: String,
    pageVisits: Number,
});

// Función para confirmar la eliminación
const confirmDelete = (id) => {
    if (confirm('¿Estás seguro de que quieres eliminar esta categoría? Esta acción no se puede deshacer.')) {
        useForm({}).delete(route('admin.categorias.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                // El mensaje de éxito se maneja automáticamente por Inertia con `session('success')`
            },
            onError: (errors) => {
                console.error('Error al eliminar:', errors);
                alert('Hubo un error al eliminar la categoría. Consulta la consola para más detalles.');
            },
        });
    }
};
</script>
