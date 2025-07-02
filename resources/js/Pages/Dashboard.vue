<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue'; // Importa computed

// Definición de las propiedades (props) que este componente Vue espera recibir.
const props = defineProps({
    totalProducts: Number,
    totalCategories: Number,
    totalInventoryMovements: Number,
    totalUsers: Number,
    totalSales: Number,
    visits: Object, // Objeto que contiene los conteos de visitas
    productsByCategory: Array, // Datos para el gráfico de torta
});

// Colores para el gráfico de torta
const pieColors = [
    '#4CAF50', '#2196F3', '#FFC107', '#FF5722', '#9C27B0',
    '#00BCD4', '#FFEB3B', '#795548', '#607D8B', '#E91E63'
];

// Función para generar las propiedades de cada "rebanada" del gráfico de torta
const pieChartData = computed(() => {
    const total = props.productsByCategory.reduce((sum, item) => sum + item.value, 0);
    let startAngle = 0;

    return props.productsByCategory.map((item, index) => {
        const sliceAngle = (item.value / total) * 360;
        const endAngle = startAngle + sliceAngle;

        const largeArcFlag = sliceAngle > 180 ? 1 : 0;

        // Coordenadas para el arco SVG
        const x1 = 50 + 45 * Math.cos(Math.PI * (startAngle - 90) / 180);
        const y1 = 50 + 45 * Math.sin(Math.PI * (startAngle - 90) / 180);
        const x2 = 50 + 45 * Math.cos(Math.PI * (endAngle - 90) / 180);
        const y2 = 50 + 45 * Math.sin(Math.PI * (endAngle - 90) / 180);

        const d = `M50,50 L${x1},${y1} A45,45 0 ${largeArcFlag},1 ${x2},${y2} Z`;

        const color = pieColors[index % pieColors.length];

        startAngle = endAngle;

        return {
            name: item.name,
            value: item.value,
            d,
            color,
            percentage: ((item.value / total) * 100).toFixed(1),
        };
    });
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Fegasacruz</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-2xl font-bold mb-6 text-gray-800">Estadísticas Generales</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                        <!-- Tarjeta de Productos -->
                        <div class="bg-blue-500 text-white p-6 rounded-lg shadow-md flex items-center justify-between">
                            <div>
                                <div class="text-4xl font-bold">{{ totalProducts }}</div>
                                <div class="text-lg">Total Productos</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>

                        <!-- Tarjeta de Categorías -->
                        <div class="bg-green-500 text-white p-6 rounded-lg shadow-md flex items-center justify-between">
                            <div>
                                <div class="text-4xl font-bold">{{ totalCategories }}</div>
                                <div class="text-lg">Total Categorías</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>

                        <!-- Tarjeta de Movimientos de Inventario -->
                        <div class="bg-yellow-500 text-white p-6 rounded-lg shadow-md flex items-center justify-between">
                            <div>
                                <div class="text-4xl font-bold">{{ totalInventoryMovements }}</div>
                                <div class="text-lg">Movimientos Inventario</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                        </div>

                        <!-- Tarjeta de Usuarios -->
                        <div class="bg-red-500 text-white p-6 rounded-lg shadow-md flex items-center justify-between">
                            <div>
                                <div class="text-4xl font-bold">{{ totalUsers }}</div>
                                <div class="text-lg">Total Usuarios</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H2v-2a3 3 0 015.356-1.857M17 20v-2c0-.653-.146-1.28-.423-1.857M9.356 18H7v-2m3.476-6.173a4.993 4.993 0 00-7.38-2.607M9.356 18c-.322-.334-.64-.68-.96-1.036M12 6.5a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zm-2 14.036c-.174.004-.347.004-.521.004H2l.016.004.008.002.002.002A9.988 9.988 0 012 18V9.5C2 7.567 4.238 6 7 6s5 1.567 5 3.5v8.556zM19.882 18h-4.882" />
                            </svg>
                        </div>

                        <!-- Tarjeta de Ventas -->
                        <div class="bg-purple-500 text-white p-6 rounded-lg shadow-md flex items-center justify-between">
                            <div>
                                <div class="text-4xl font-bold">{{ totalSales }}</div>
                                <div class="text-lg">Total Ventas</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Sección del Gráfico de Torta -->
                    <h3 class="text-2xl font-bold mb-4 text-gray-800">Productos por Categoría</h3>
                    <div class="flex flex-col md:flex-row items-center justify-center bg-gray-50 p-6 rounded-lg shadow-sm mb-10">
                        <div v-if="productsByCategory.length === 0" class="text-gray-600">
                            No hay productos para mostrar en el gráfico de torta.
                        </div>
                        <div v-else class="flex flex-col md:flex-row items-center justify-center w-full">
                            <div class="w-full md:w-1/2 flex justify-center mb-6 md:mb-0">
                                <svg width="200" height="200" viewBox="0 0 100 100">
                                    <template v-for="(slice, index) in pieChartData" :key="index">
                                        <path :d="slice.d" :fill="slice.color" stroke="white" stroke-width="1" />
                                    </template>
                                </svg>
                            </div>
                            <div class="w-full md:w-1/2 md:ps-8">
                                <ul class="space-y-2">
                                    <li v-for="(slice, index) in pieChartData" :key="index" class="flex items-center">
                                        <span :style="{ backgroundColor: slice.color }" class="block w-4 h-4 rounded-full mr-3"></span>
                                        <span class="text-gray-700">{{ slice.name }}: {{ slice.value }} ({{ slice.percentage }}%)</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-2xl font-bold mb-4 text-gray-800">Visitas a Páginas de Administración</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Categorías -->
                        <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                            <div class="font-semibold text-gray-700">Categorías (Índice): <span class="font-bold text-indigo-600">{{ visits.categoriasIndex }}</span></div>
                            <div class="font-semibold text-gray-700">Categorías (Crear): <span class="font-bold text-indigo-600">{{ visits.categoriasCreate }}</span></div>
                            <div class="font-semibold text-gray-700">Categorías (Editar): <span class="font-bold text-indigo-600">{{ visits.categoriasEdit }}</span></div>
                        </div>
                        <!-- Productos -->
                        <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                            <div class="font-semibold text-gray-700">Productos (Índice): <span class="font-bold text-indigo-600">{{ visits.productosIndex }}</span></div>
                            <div class="font-semibold text-gray-700">Productos (Crear): <span class="font-bold text-indigo-600">{{ visits.productosCreate }}</span></div>
                            <div class="font-semibold text-gray-700">Productos (Editar): <span class="font-bold text-indigo-600">{{ visits.productosEdit }}</span></div>
                        </div>
                        <!-- Inventario -->
                        <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                            <div class="font-semibold text-gray-700">Inventario (Índice): <span class="font-bold text-indigo-600">{{ visits.inventarioIndex }}</span></div>
                            <div class="font-semibold text-gray-700">Inventario (Crear): <span class="font-bold text-indigo-600">{{ visits.inventarioCreate }}</span></div>
                            <div class="font-semibold text-gray-700">Inventario (Editar): <span class="font-bold text-indigo-600">{{ visits.inventarioEdit }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
