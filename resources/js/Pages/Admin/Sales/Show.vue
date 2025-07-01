// resources/js/Pages/Admin/Sales/Show.vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Definición de las propiedades (props) que este componente Vue espera recibir.
const props = defineProps({
    venta: Object,
});

/**
 * Función auxiliar para asegurar que el valor sea un número flotante.
 * Si el valor es null o undefined, devuelve 0 para evitar errores con toFixed().
 * @param {any} value - El valor a convertir.
 * @returns {number} El valor como un número flotante, o 0 si es null/undefined.
 */
const ensureFloat = (value) => {
    if (value === null || typeof value === 'undefined') {
        return 0;
    }
    return parseFloat(value);
};
</script>

<template>
    <Head :title="`Detalles de Venta #${props.venta.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalles de Venta #{{ props.venta.id }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 rounded-lg shadow-lg">
                    <div class="mb-6 border-b pb-4">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">Información General de la Venta</h3>
                        <p class="mb-2"><strong>ID de Venta:</strong> {{ venta.id }}</p>
                        <p class="mb-2"><strong>Cliente:</strong> {{ venta.user ? venta.user.name : 'Usuario Desconocido' }} ({{ venta.user ? venta.user.email : 'N/A' }})</p>
                        <p class="mb-2"><strong>Fecha de Venta:</strong> {{ new Date(venta.fecha_venta).toLocaleString() }}</p>
                        <p class="mb-2"><strong>Total de la Venta:</strong> ${{ ensureFloat(venta.total).toFixed(2) }}</p>
                        <p class="mb-2"><strong>Estado:</strong>
                            <span :class="[
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                venta.estado === 'completado' ? 'bg-green-100 text-green-800' :
                                venta.estado === 'pendiente' ? 'bg-yellow-100 text-yellow-800' :
                                'bg-gray-100 text-gray-800'
                            ]">
                                {{ venta.estado }}
                            </span>
                        </p>
                    </div>

                    <h3 class="text-xl font-semibold mb-4 text-gray-800">Productos en esta Venta</h3>
                    <div v-if="venta.detalles && venta.detalles.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Producto
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cantidad
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Precio Unitario
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="detalle in venta.detalles" :key="detalle.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ detalle.producto ? detalle.producto.nombre : 'Producto Desconocido' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ detalle.cantidad }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        ${{ ensureFloat(detalle.precio_unitario).toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        ${{ ensureFloat(detalle.subtotal).toFixed(2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-gray-600">
                        No hay detalles de productos para esta venta.
                    </div>

                    <div class="mt-6 flex justify-end">
                        <Link :href="route('admin.sales.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg rounded-md shadow-sm">
                            Volver a la Lista de Ventas
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

