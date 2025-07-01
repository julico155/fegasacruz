// resources/js/Pages/Admin/Inventario/Show.vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Definición de las propiedades (props) que este componente Vue espera recibir.
const props = defineProps({
    movimiento: Object,
});
</script>

<template>
    <Head :title="`Detalles de Movimiento: ${props.movimiento.id_movimiento}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalles del Movimiento de Inventario #{{ props.movimiento.id_movimiento }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 rounded-lg shadow-lg">
                    <div class="mb-4">
                        <p><strong>ID Movimiento:</strong> {{ movimiento.id_movimiento }}</p>
                        <p><strong>Producto:</strong> {{ movimiento.producto ? movimiento.producto.nombre : 'N/A' }}</p>
                        <p><strong>Tipo:</strong> {{ movimiento.tipo }}</p>
                        <p><strong>Cantidad:</strong> {{ movimiento.cantidad }}</p>
                        <p><strong>Fecha:</strong> {{ new Date(movimiento.fecha).toLocaleString() }}</p>
                        <p><strong>Observación:</strong> {{ movimiento.observacion || 'N/A' }}</p>
                        <p><strong>Creado el:</strong> {{ new Date(movimiento.created_at).toLocaleString() }}</p>
                        <p><strong>Última actualización:</strong> {{ new Date(movimiento.updated_at).toLocaleString() }}</p>
                    </div>
                    <div class="flex justify-end">
                        <Link :href="route('admin.inventario.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg rounded-md shadow-sm">
                            Volver a la Lista
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

