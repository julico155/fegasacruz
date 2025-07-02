<!-- resources/js/Pages/Shop/PaymentSuccess.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Pago Exitoso" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pago Completado</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center shadow-lg rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-green-500 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-3xl font-bold text-gray-800 mb-4">¡Tu pago ha sido procesado con éxito!</h3>
                    <p class="text-gray-600 mb-6 text-lg">Gracias por tu compra. Recibirás una confirmación por correo electrónico en breve y el estado de tu venta se actualizará.</p>
                    <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4 mt-6">
                        <Link :href="route('shop.index')" class="bg-indigo-600 text-white py-3 px-8 rounded-full hover:bg-indigo-700 transition duration-200 font-semibold shadow-md">
                            Seguir Comprando
                        </Link>
                        <Link :href="route('venta.mySales')" class="border-2 border-indigo-600 text-indigo-600 py-3 px-8 rounded-full hover:bg-indigo-50 transition duration-200 font-semibold shadow-md">
                            Ver Mis Compras
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Puedes añadir estilos adicionales aquí si lo deseas */
</style>
