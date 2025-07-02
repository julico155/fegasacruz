<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Asegúrate de tener este layout
import { Head } from '@inertiajs/vue3';

// Define las props que esperas de tu controlador
const props = defineProps({
    qrImageBase64: String,
    nroTransaccion: String,
    ventaId: Number,
});
</script>

<template>
    <Head title="Confirmación de Pago" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Confirmación de Pago</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-2xl font-bold mb-4">¡Tu pago ha sido iniciado!</h3>
                        <p class="mb-4">Por favor, escanea el código QR a continuación para completar la transacción.</p>

                        <div v-if="qrImageBase64" class="text-center my-6">
                            <img :src="qrImageBase64" alt="Código QR de Pago" class="mx-auto border p-2 bg-gray-50" style="max-width: 300px;">
                            <p class="mt-2 text-sm text-gray-600">Número de Transacción: <strong>{{ nroTransaccion }}</strong></p>
                            <p class="mt-1 text-sm text-gray-600">ID de Venta: <strong>{{ ventaId }}</strong></p>
                        </div>
                        <div v-else class="text-red-500">
                            No se pudo generar el código QR. Por favor, contacta a soporte.
                        </div>

                        <p class="mt-6 text-gray-700">Puedes verificar el estado de tu compra en la sección "Mis Compras".</p>
                        <a :href="route('venta.mySales')" class="mt-4 inline-block px-6 py-2 bg-indigo-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-indigo-700 hover:shadow-lg focus:bg-indigo-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-indigo-800 active:shadow-lg transition duration-150 ease-in-out">
                            Ir a Mis Compras
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>