// resources/js/Pages/Admin/Producto/Create.vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Definición de las propiedades (props) que este componente Vue espera recibir.
// 'categories' es un array que contendrá la lista de categorías disponibles.
const props = defineProps({
    categories: Array,
    pageVisits: Number,
});

// Inicialización del formulario con Inertia.js para manejar los datos del producto.
// Los campos se inicializan vacíos.
const form = useForm({
    nombre: '',
    descripcion: '',
    precio_unitario: '',
    stock_actual: '',
    id_categoria: '',
});

/**
 * Envía el formulario para crear un nuevo producto.
 * Utiliza el método POST de Inertia para enviar los datos a la ruta 'admin.productos.store'.
 * Después de un envío exitoso, el formulario se reinicia.
 */
const submit = () => {
    form.post(route('admin.productos.store'), {
        onFinish: () => form.reset(), // Reinicia el formulario después de completar la operación
    });
};
</script>

<template>
    <Head title="Crear Producto" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Nuevo Producto</h2>
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
                            <InputLabel for="nombre" value="Nombre" />
                            <TextInput
                                id="nombre"
                                type="text"
                                class="mt-1 block w-full rounded-md"
                                v-model="form.nombre"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.nombre" />
                        </div>

                        <div class="mb-4">
                            <InputLabel for="descripcion" value="Descripción" />
                            <textarea
                                id="descripcion"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.descripcion"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.descripcion" />
                        </div>

                        <div class="mb-4">
                            <InputLabel for="precio_unitario" value="Precio Unitario" />
                            <TextInput
                                id="precio_unitario"
                                type="number"
                                step="0.01"
                                class="mt-1 block w-full rounded-md"
                                v-model="form.precio_unitario"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.precio_unitario" />
                        </div>

                        <div class="mb-4">
                            <InputLabel for="stock_actual" value="Stock Actual" />
                            <TextInput
                                id="stock_actual"
                                type="number"
                                class="mt-1 block w-full rounded-md"
                                v-model="form.stock_actual"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.stock_actual" />
                        </div>

                        <div class="mb-4">
                            <InputLabel for="id_categoria" value="Categoría" />
                            <select
                                id="id_categoria"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.id_categoria"
                                required
                            >
                                <option value="">Selecciona una categoría</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.nombre }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.id_categoria" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Guardar Producto
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

