// resources/js/Pages/Admin/Inventario/Edit.vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Definición de las propiedades (props) que este componente Vue espera recibir.
const props = defineProps({
    movimiento: Object,
    productos: Array,
    errors: Object,
    pageVisits: Number,
});

// Inicialización del formulario con Inertia.js para manejar los datos del movimiento de inventario.
// Se usa '_method: 'put'' para que Laravel reconozca la petición como una actualización (PUT).
const form = useForm({
    _method: 'put',
    id_producto: props.movimiento.id_producto,
    tipo: props.movimiento.tipo,
    cantidad: props.movimiento.cantidad,
    observacion: props.movimiento.observacion,
});

/**
 * Envía el formulario para actualizar el movimiento de inventario.
 * Utiliza el método POST de Inertia (con el _method 'put' simulando PUT)
 * para enviar los datos a la ruta de actualización del movimiento.
 */
const submit = () => {
    form.post(route('admin.inventario.update', props.movimiento.id_movimiento), {
        onFinish: () => {
            // Lógica a ejecutar después de que la operación de actualización finaliza.
            // Inertia.js recargará automáticamente los props si el controlador redirige.
        },
    });
};
</script>

<template>
    <Head :title="`Editar Movimiento: ${props.movimiento.id_movimiento}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Movimiento de Inventario: {{ props.movimiento.id_movimiento }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 rounded-lg shadow-lg">
                    <!-- INICIO DEL FRAGMENTO DEL CONTADOR -->
                    <div class="mb-4 text-gray-700 p-3 bg-gray-50 rounded-lg shadow-sm">
                        Visitas a esta página: <span class="font-bold text-indigo-600">{{ pageVisits }}</span>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <InputLabel for="id_producto" value="Producto" />
                            <select
                                id="id_producto"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.id_producto"
                                required
                            >
                                <option value="">Selecciona un producto</option>
                                <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                                    {{ producto.nombre }} (Stock: {{ producto.stock_actual }})
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.id_producto" />
                        </div>

                        <div class="mb-4">
                            <InputLabel for="tipo" value="Tipo de Movimiento" />
                            <select
                                id="tipo"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.tipo"
                                required
                            >
                                <option value="ingreso">Ingreso</option>
                                <option value="salida">Salida</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.tipo" />
                        </div>

                        <div class="mb-4">
                            <InputLabel for="cantidad" value="Cantidad" />
                            <TextInput
                                id="cantidad"
                                type="number"
                                class="mt-1 block w-full rounded-md"
                                v-model="form.cantidad"
                                required
                                min="1"
                            />
                            <InputError class="mt-2" :message="form.errors.cantidad" />
                        </div>

                        <div class="mb-4">
                            <InputLabel for="observacion" value="Observación" />
                            <textarea
                                id="observacion"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.observacion"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.observacion" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Actualizar Movimiento
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

