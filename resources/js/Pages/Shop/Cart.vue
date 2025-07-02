<!-- resources/js/Pages/Shop/Cart.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

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

// Formulario para el checkout (ya no se usa para la petición principal, solo para el botón)
const checkoutForm = useForm({});

// Variables reactivas para el QR y el estado del pago
const qrImageBase64 = ref(null); // Renombrado para claridad
const nroTransaccion = ref(null);
const ventaId = ref(null); // Para almacenar el ID de la venta creada en el backend
const estadoPago = ref(null);
const showQrModal = ref(false); // Controla la visibilidad del modal del QR
let checkInterval = null; // Para el polling

/**
 * Actualiza la cantidad de un producto en el carrito.
 * @param {object} item - El ítem del carrito a actualizar.
 * @param {number} newQuantity - La nueva cantidad deseada.
 */
const updateQuantity = (item, newQuantity) => {
    if (newQuantity < 0) newQuantity = 0;
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
 * Esta función ahora llama a la función `handleCheckout` que usa Axios.
 */
const proceedToCheckout = () => {
    if (props.cartItems.length === 0) {
        alert('Tu carrito está vacío. Añade productos antes de proceder al pago.');
        return;
    }
    if (!props.auth.user) {
        alert('Debes iniciar sesión para completar tu compra.');
        window.location.href = route('login');
        return;
    }
    // Llama a la función que maneja la petición Axios y el QR
    handleCheckout();
};

/**
 * Función auxiliar para asegurar que el precio sea un número antes de formatear.
 * @param {any} price - El valor del precio.
 * @returns {number} El precio como un número flotante.
 */
const ensureFloat = (price) => {
    return parseFloat(price);
};

// --- Lógica de PagoFácil y Polling ---

/**
 * Maneja el proceso de checkout, llama al backend para obtener el QR.
 */
const handleCheckout = async () => {
    try {
        // Deshabilita el botón mientras se procesa
        checkoutForm.processing = true;

        // La ruta de checkout ahora devuelve JSON
        const response = await axios.post(route('checkout'));

        if (response.data.success) {
            qrImageBase64.value = response.data.qrImageBase64;
            nroTransaccion.value = response.data.nroTransaccion;
            ventaId.value = response.data.ventaId; // Guarda el ID de la venta

            showQrModal.value = true; // Muestra el modal del QR
            iniciarConsultaEstado(); // Inicia el polling
        } else {
            alert(response.data.error || 'Error al obtener el QR de pago.');
        }
    } catch (error) {
        console.error('Error en handleCheckout:', error);
        // Manejo de errores de la petición (ej. 400, 500)
        if (error.response && error.response.data && error.response.data.error) {
            alert('Error: ' + error.response.data.error);
        } else {
            alert('Ocurrió un error inesperado al procesar el pago.');
        }
    } finally {
        // Habilita el botón de nuevo
        checkoutForm.processing = false;
    }
};

/**
 * Actualiza el estado del pedido en la base de datos de tu Laravel.
 * Se llama cuando el polling detecta que el pago fue exitoso (estadoPago.value === 2).
 */
const actualizarEstadoPedido = async () => {
    if (!ventaId.value) {
        console.error('No hay ID de venta para actualizar el estado.');
        return;
    }
    try {
        // Llama al nuevo endpoint API para actualizar el estado de la venta en el backend
        const updateResponse = await axios.post(route('actualizar.estado.venta'), {
            venta_id: ventaId.value,
            // Puedes enviar el nroTransaccion si quieres guardarlo en este punto
            // nroTransaccion: nroTransaccion.value,
            // O el estado específico si tu backend lo espera
            // estado: 'completado',
        });

        if (updateResponse.data.success) {
            console.log('Estado de venta actualizado en DB por polling:', updateResponse.data.message);
            // Solo redirigimos si la actualización en la DB fue exitosa
            window.location.href = route('venta.pagoExitoso'); // Redirige a la página de éxito
        } else {
            console.error('Fallo al actualizar estado de venta por polling:', updateResponse.data.message);
            alert('El pago fue exitoso, pero hubo un problema al actualizar el estado de la venta. Por favor, revisa tus compras.');
            // Aún redirigimos para no dejar al usuario atascado, pero con una advertencia
            window.location.href = route('venta.pagoExitoso');
        }
    } catch (error) {
        console.error('Error al actualizar el estado del pedido:', error.message);
        alert('Error al actualizar el estado del pedido. Por favor, revisa tus compras.');
    }
};

/**
 * Consulta el estado de la transacción de PagoFácil.
 * Se ejecuta repetidamente con setInterval.
 */
const consultarEstadoTransaccion = async () => {
    if (!nroTransaccion.value) {
        detenerConsultaEstado(); // No hay transacción para consultar
        return;
    }

    try {
        // Llama al endpoint de tu Laravel para consultar el estado en PagoFácil
        const response = await axios.post( '/consultartransaccion', {
            tnTransaccion: nroTransaccion.value,
        });

        estadoPago.value = response.data.estadoTransaccion; // Obtiene el código de estado

        if (estadoPago.value === 2) { // 2 significa "Pagado" en PagoFácil
            detenerConsultaEstado();
            alert('¡Pago exitoso! Redirigiendo...');
            await actualizarEstadoPedido(); // Redirige a la página de éxito
        } else if (estadoPago.value === 4) { // 4 significa "Expirado" en PagoFácil
            detenerConsultaEstado();
            alert('La transacción ha vencido. Intenta nuevamente.');
            closeQrModal(); // Cierra el modal
        }
        // Puedes añadir más lógica para otros estados si es necesario
        console.log('Estado de transacción:', estadoPago.value, response.data.message);
    } catch (error) {
        console.error('Error al consultar el estado de la transacción:', error.message);
        detenerConsultaEstado(); // Detener el polling en caso de error
        alert('Error al consultar el estado del pago. Por favor, revisa tus compras.');
        closeQrModal(); // Cierra el modal
    }
};

/**
 * Inicia el polling para consultar el estado de la transacción.
 */
const iniciarConsultaEstado = () => {
    detenerConsultaEstado(); // Asegura que no haya intervalos duplicados
    checkInterval = setInterval(consultarEstadoTransaccion, 10000); // Consulta cada 10 segundos
    console.log('Polling iniciado para la transacción:', nroTransaccion.value);
};

/**
 * Detiene el polling.
 */
const detenerConsultaEstado = () => {
    if (checkInterval) {
        clearInterval(checkInterval);
        checkInterval = null;
        console.log('Polling detenido.');
    }
};

/**
 * Cierra el modal del QR y limpia los datos.
 */
const closeQrModal = () => {
    detenerConsultaEstado(); // Detener el polling al cerrar el modal
    qrImageBase64.value = null;
    nroTransaccion.value = null;
    ventaId.value = null;
    estadoPago.value = null;
    showQrModal.value = false;
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

        <!-- Modal de Pago QR -->
        <div v-if="showQrModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm w-full text-center relative">
                <button @click="closeQrModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</button>
                <h3 class="text-2xl font-bold mb-4 text-gray-800">Escanea para Pagar</h3>
                <p class="text-gray-700 mb-4">Número de Transacción: <strong>{{ nroTransaccion }}</strong></p>
                <p class="text-gray-700 mb-4">Estado: <strong>{{ estadoPago || 'Esperando pago...' }}</strong></p>
                <div v-if="qrImageBase64" class="flex justify-center mb-6">
                    <img :src="qrImageBase64" alt="Código QR de Pago" class="w-64 h-64 object-contain border p-2 rounded-md bg-gray-50"/>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Por favor, escanea este código QR con tu aplicación de banca móvil para completar el pago.
                    La página se actualizará automáticamente cuando el pago sea detectado.
                </p>
                <button @click="closeQrModal" class="inline-block bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition duration-200">
                    Cerrar
                </button>
                <Link :href="route('venta.mySales')" class="ml-2 inline-block bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition duration-200">
                    Ir a Mis Compras
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>