<script setup>
// import ApplicationLogo from '@/Components/ApplicationLogo.vue'; // Ya no necesitamos este import
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900"
    >
        <div>
            <Link href="/">
                <!-- Reemplazado ApplicationLogo por la imagen directa -->
                <img src="/images/logo.jpg" alt="Logo de la Aplicación" class="h-20 w-20 object-contain" />
                <!-- Puedes ajustar las clases de Tailwind (h-20 w-20) según el tamaño deseado para tu logo -->
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800"
        >
            <slot />
        </div>
    </div>
</template>