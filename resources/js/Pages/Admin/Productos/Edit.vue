// resources/js/Pages/Admin/Producto/Edit.vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    product: Object, // El producto a editar
    categories: Array, // Propiedad para las categorías (ahora se pasan desde el controlador)
    pageVisits: Number,
});

// Inicializa el formulario con los datos del producto existente
const form = useForm({
    _method: 'put', // Importante para que Laravel reconozca la petición PUT
    nombre: props.product.nombre,
    descripcion: props.product.descripcion,
    precio_unitario: props.product.precio_unitario,
    stock_actual: props.product.stock_actual,
    id_categoria: props.product.id_categoria,
});

/**
 * Envía el formulario para actualizar el producto.
 */
const submit = () => {
    form.post(route('admin.productos.update', props.product.id), {
        onFinish: () => {
            // Lógica después de actualizar, por ejemplo, redirigir o mostrar un mensaje
            // Inertia automáticamente recargará los props si el controlador redirige
        },
    });
};
</script>

<template>
    <Head :title="`Editar Producto: ${props.product.nombre}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Producto: {{ props.product.nombre }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
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
                                class="mt-1 block w-full"
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
                                class="mt-1 block w-full"
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
                                class="mt-1 block w-full"
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
                                Actualizar Producto
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

