// resources/js/Pages/Admin/Inventario/Index.vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Definición de las propiedades (props) que este componente Vue espera recibir.
const props = defineProps({
    movimientos: Object, // Objeto de paginación para los movimientos de inventario
    success: String,     // Mensaje de éxito
    error: String,   
    pageVisits: Number,    // Mensaje de error
});

// Inicialización del formulario con Inertia.js para manejar operaciones como la eliminación.
const form = useForm({});

/**
 * Elimina un movimiento de inventario.
 * Muestra un cuadro de diálogo de confirmación antes de proceder.
 * @param {number} id_movimiento - El ID del movimiento a eliminar.
 */
const deleteMovimiento = (id_movimiento) => {
    if (confirm('¿Estás seguro de que quieres eliminar este movimiento de inventario? Esto revertirá el cambio en el stock del producto.')) {
        form.delete(route('admin.inventario.destroy', id_movimiento), {
            onSuccess: () => {
                // Inertia recargará la página automáticamente después de una eliminación exitosa.
            },
            onError: (errors) => {
                // Manejo de errores si la eliminación falla.
                console.error('Error al eliminar el movimiento:', errors);
            }
        });
    }
};
</script>

<template>
    <Head title="Movimientos de Inventario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Inventario</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 rounded-lg shadow-lg">
                    <!-- INICIO DEL FRAGMENTO DEL CONTADOR -->
                    <div class="mb-4 text-gray-700 p-3 bg-gray-50 rounded-lg shadow-sm">
                        Visitas a esta página: <span class="font-bold text-indigo-600">{{ pageVisits }}</span>
                    </div>
                    <div v-if="props.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ props.success }}</span>
                    </div>
                    <div v-if="props.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ props.error }}</span>
                    </div>

                    <div class="flex justify-end mb-4">
                        <Link :href="route('admin.inventario.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg rounded-md shadow-sm">
                            Registrar Nuevo Movimiento
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ID Movimiento
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Producto
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tipo
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cantidad
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Observación
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="movimiento in movimientos.data" :key="movimiento.id_movimiento">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ movimiento.id_movimiento }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ movimiento.producto ? movimiento.producto.nombre : 'Producto Desconocido' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ movimiento.tipo }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ movimiento.cantidad }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(movimiento.fecha).toLocaleString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ movimiento.observacion || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('admin.inventario.edit', movimiento.id_movimiento)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Editar
                                        </Link>
                                        <button @click="deleteMovimiento(movimiento.id_movimiento)" class="text-red-600 hover:text-red-900">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-4 flex justify-center">
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <Link v-for="(link, index) in movimientos.links" :key="index"
                                :href="link.url || '#'" :disabled="!link.url"
                                :class="['relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                    link.active ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                    index === 0 ? 'rounded-l-md' : '',
                                    index === movimientos.links.length - 1 ? 'rounded-r-md' : '',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                v-html="link.label"
                            />
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

