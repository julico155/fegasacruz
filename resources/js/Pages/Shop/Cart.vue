// resources/js/Pages/Shop/Cart.vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Definición de las propiedades (props) que este componente Vue espera recibir.
const props = defineProps({
    cartItems: Array,
    cartTotal: Number,
    success: String,
    error: String,
    auth: Object
});

// Formulario para actualizar la cantidad de un producto en el carrito
const updateForm = useForm({
    producto_id: null,
    cantidad: null
});

// Formulario para eliminar un producto del carrito
const removeForm = useForm({
    producto_id: null
});

// Formulario para el checkout
const checkoutForm = useForm({});

/**
 * Actualiza la cantidad de un producto en el carrito.
 * @param {object} item - El ítem del carrito a actualizar.
 * @param {number} newQuantity - La nueva cantidad deseada.
 */
const updateQuantity = (item, newQuantity) => {
    if (newQuantity < 0) newQuantity = 0; // Evitar cantidades negativas
    updateForm.producto_id = item.id;
    updateForm.cantidad = newQuantity;
    updateForm.post(route('cart.update'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Cantidad actualizada para:', item.nombre);
        },
        onError: (errors) => {
            console.error('Error al actualizar cantidad:', errors);
            alert('Error al actualizar cantidad: ' + (errors.cantidad || errors.producto_id || ''));
        }
    });
};

/**
 * Elimina un producto del carrito.
 * @param {number} productoId - El ID del producto a eliminar.
 */
const removeItem = (productoId) => {
    if (confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
        removeForm.producto_id = productoId;
        removeForm.post(route('cart.remove'), {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Producto eliminado del carrito.');
            },
            onError: (errors) => {
                console.error('Error al eliminar producto:', errors);
                alert('Error al eliminar producto: ' + (errors.producto_id || ''));
            }
        });
    }
};

/**
 * Procede al checkout.
 */
const proceedToCheckout = () => {
    if (props.cartItems.length === 0) {
        alert('Tu carrito está vacío. Añade productos antes de proceder al pago.');
        return;
    }
    if (!props.auth.user) {
        alert('Debes iniciar sesión para completar tu compra.');
        // Redirigir al login si no está autenticado
        window.location.href = route('login');
        return;
    }
    checkoutForm.post(route('checkout'), {
        onSuccess: () => {
            console.log('Checkout exitoso.');
            // Inertia redirigirá a la página de éxito de la venta
        },
        onError: (errors) => {
            console.error('Error durante el checkout:', errors);
            alert('Error durante el checkout: ' + (errors.cart || 'Verifica el stock de tus productos.'));
        }
    });
};

/**
 * Función auxiliar para asegurar que el precio sea un número antes de formatear.
 * @param {any} price - El valor del precio.
 * @returns {number} El precio como un número flotante.
 */
const ensureFloat = (price) => {
    return parseFloat(price);
};
</script>

<template>
    <Head title="Carrito de Compras" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tu Carrito de Compras</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 rounded-lg shadow-lg">
                    <div v-if="props.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ props.success }}</span>
                    </div>
                    <div v-if="props.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ props.error }}</span>
                    </div>

                    <div v-if="cartItems.length === 0" class="text-center py-10 text-gray-600">
                        <p class="text-lg mb-4">Tu carrito está vacío.</p>
                        <Link :href="route('shop.index')" class="text-indigo-600 hover:text-indigo-800 font-medium">
                            Volver a la tienda para añadir productos
                        </Link>
                    </div>

                    <div v-else>
                        <div class="grid grid-cols-1 gap-6">
                            <div v-for="item in cartItems" :key="item.id" class="flex items-center border rounded-lg p-4 shadow-sm">
                                <img :src="item.imagen" :alt="item.nombre" class="w-20 h-20 object-cover rounded-md mr-4">
                                <div class="flex-grow">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ item.nombre }}</h3>
                                    <p class="text-gray-600">Precio Unitario: ${{ ensureFloat(item.precio_unitario).toFixed(2) }}</p>
                                    <div class="flex items-center mt-2">
                                        <button @click="updateQuantity(item, item.cantidad - 1)" class="bg-gray-200 text-gray-700 px-3 py-1 rounded-l-md hover:bg-gray-300">-</button>
                                        <input
                                            type="number"
                                            v-model.number="item.cantidad"
                                            @change="updateQuantity(item, item.cantidad)"
                                            class="w-16 text-center border-t border-b border-gray-300 py-1 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                            min="0"
                                        />
                                        <button @click="updateQuantity(item, item.cantidad + 1)" class="bg-gray-200 text-gray-700 px-3 py-1 rounded-r-md hover:bg-gray-300">+</button>
                                        <span class="ml-4 text-gray-800 font-medium">Subtotal: ${{ (ensureFloat(item.cantidad) * ensureFloat(item.precio_unitario)).toFixed(2) }}</span>
                                    </div>
                                </div>
                                <button @click="removeItem(item.id)" class="ml-4 text-red-600 hover:text-red-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-200 flex justify-between items-center">
                            <h3 class="text-2xl font-bold text-gray-900">Total del Carrito: ${{ ensureFloat(cartTotal).toFixed(2) }}</h3>
                            <button
                                @click="proceedToCheckout"
                                :class="['py-3 px-6 rounded-md font-semibold transition-colors duration-200',
                                    cartItems.length > 0 ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                ]"
                                :disabled="cartItems.length === 0 || checkoutForm.processing"
                            >
                                Proceder al Pago
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

