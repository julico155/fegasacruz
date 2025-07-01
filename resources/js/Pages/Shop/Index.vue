// resources/js/Pages/Shop/Index.vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Definición de las propiedades (props) que este componente Vue espera recibir.
const props = defineProps({
    productos: Object,
    success: String,
    pageVisits: Number,
});

const form = useForm({
    producto_id: null,
    cantidad: 1,
});

/**
 * Añade un producto al carrito.
 * @param {object} producto - El objeto producto a añadir.
 */
const addToCart = (producto) => {
    form.producto_id = producto.id;
    form.post(route('cart.add'), {
        preserveScroll: true, // Mantener la posición de scroll después de añadir
        onSuccess: () => {
            // Opcional: mostrar una notificación o mensaje en el frontend
            console.log('Producto añadido:', producto.nombre);
        },
        onError: (errors) => {
            console.error('Error al añadir al carrito:', errors);
            alert('Error al añadir al carrito: ' + (errors.cantidad || errors.producto_id || 'Verifica el stock.'));
        }
    });
};
</script>

<template>
    <Head title="Tienda" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuestros Productos</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 rounded-lg shadow-lg">
                    <div class="mb-4 text-gray-700 p-3 bg-gray-50 rounded-lg shadow-sm">
                        Visitas a esta página: <span class="font-bold text-indigo-600">{{ pageVisits }}</span>
                    </div>
                    <div v-if="props.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ props.success }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <div v-for="producto in productos.data" :key="producto.id" class="border rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                            <img :src="`https://placehold.co/400x300/E0E0E0/000000?text=${producto.nombre}`" alt="Imagen del producto" class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ producto.nombre }}</h3>
                                <p class="text-sm text-gray-600 mb-2">{{ producto.categoria ? producto.categoria.nombre : 'Sin Categoría' }}</p>
                                <p class="text-gray-700 text-sm mb-3">{{ producto.descripcion.substring(0, 70) + '...' }}</p>
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-xl font-bold text-gray-900">${{ producto.precio_unitario }}</span>
                                    <span :class="['text-sm font-medium', producto.stock_actual > 0 ? 'text-green-600' : 'text-red-600']">
                                        Stock: {{ producto.stock_actual }}
                                    </span>
                                </div>
                                <button
                                    @click="addToCart(producto)"
                                    :disabled="producto.stock_actual <= 0 || form.processing"
                                    :class="['w-full py-2 px-4 rounded-md font-semibold transition-colors duration-200',
                                        producto.stock_actual > 0 ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                    ]"
                                >
                                    {{ producto.stock_actual > 0 ? 'Añadir al Carrito' : 'Sin Stock' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-8 flex justify-center">
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <Link v-for="(link, index) in productos.links" :key="index"
                                :href="link.url || '#'" :disabled="!link.url"
                                :class="['relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                    link.active ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                    index === 0 ? 'rounded-l-md' : '',
                                    index === productos.links.length - 1 ? 'rounded-r-md' : '',
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

