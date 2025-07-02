<script setup>
import { Head, Link } from '@inertiajs/vue3';
const props = defineProps({ qr_url: String, monto: String });
</script>

<template>
    <Head title="Escanea el QR para pagar" />
    <div class="flex flex-col items-center justify-center min-h-screen">
        <h1 class="text-2xl font-bold mb-4">Escanea el código QR para pagar</h1>
        <p class="mb-2 text-lg">Monto a pagar: <strong>${{ monto }}</strong></p>
        <img :src="qr_url" alt="QR de Pago" class="w-72 h-72 rounded shadow-lg mb-4" />
        <Link href="/" class="text-blue-600 hover:underline mt-4">Volver a la tienda</Link>
    </div>
</template>
