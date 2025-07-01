// resources/js/Pages/Admin/Inventario/Create.vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Definición de las propiedades (props) que este componente Vue espera recibir.
const props = defineProps({
    productos: Array,
    errors: Object,
    pageVisits: Number,
});

// Inicialización del formulario con Inertia.js para manejar los datos del producto.
// Los campos se inicializan vacíos.
const form = useForm({
    id_producto: '',
    tipo: 'ingreso', // Valor por defecto
    cantidad: '',
    observacion: '',
});

/**
 * Envía el formulario para crear un nuevo movimiento de inventario.
 * Utiliza el método POST de Inertia para enviar los datos a la ruta 'admin.inventario.store'.
 * Después de un envío exitoso, el formulario se reinicia.
 */
const submit = () => {
    form.post(route('admin.inventario.store'), {
        onFinish: () => form.reset('cantidad', 'observacion'), // Reinicia solo cantidad y observación
    });
};
</script>

<template>
    <Head title="Registrar Movimiento de Inventario" />

    <AuthenticatedLayout>
        
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Registrar Nuevo Movimiento de Inventario</h2>
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
                                Registrar Movimiento
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

