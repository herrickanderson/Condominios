<template>

    <Head title="Distribución de Gastos" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-blue-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Distribución de Gastos</h3>
                <Link :href="route('distribucion_gasto.create')" class="text-white hover:underline font-semibold">
                Generar Distribución
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <!-- Mensaje de éxito -->
                    <div v-if="$page.props.flash?.success" class="p-2 bg-green-100 text-green-700 rounded mb-4">
                        {{ $page.props.flash.success }}
                    </div>

                    <!-- Acordeón de grupos -->
                    <div v-for="(group, key) in groupedDistribuciones" :key="key"
                        class="mb-8 border border-gray-200 rounded">
                        <!-- Encabezado del grupo: clic para expandir/contraer -->
                        <div class="bg-gray-200 px-4 py-2 flex justify-between items-center cursor-pointer"
                            @click="toggleGroup(key)">
                            <h4 class="font-bold text-gray-800">
                                {{ group[0].detalle_gasto_comun.gastos_comune?.descripcion || 'Sin gasto común' }}
                            </h4>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600">
                                    Vence: {{ formatDate(group[0].detalle_gasto_comun.gastos_comune?.fecha_vencimiento)
                                    }}
                                </span>
                                <svg v-if="expandedGroups[key]" class="h-5 w-5 transform rotate-180" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <!-- Contenido del grupo (tabla) -->
                        <transition name="fade">
                            <div v-if="expandedGroups[key]" class="overflow-x-auto">
                                <table class="min-w-full table-auto">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-2">ID</th>
                                            <th class="px-4 py-2">Detalle</th>
                                            <th class="px-4 py-2">Unidad</th>
                                            <th class="px-4 py-2">Monto Asignado</th>
                                            <th class="px-4 py-2">Fecha Vencimiento</th>
                                            <th class="px-4 py-2">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr v-for="dist in group" :key="dist.id_distribucion" class="hover:bg-gray-50">
                                            <td class="px-4 py-2">{{ dist.id_distribucion }}</td>
                                            <td class="px-4 py-2">
                                                {{ dist.detalle_gasto_comun.descripcion_detalle || 'Sin descripción' }}
                                            </td>
                                            <td class="px-4 py-2">
                                                {{ dist.unidad.nombre_unidad }}
                                            </td>
                                            <td class="px-4 py-2">
                                                {{ dist.monto_asignado }}
                                            </td>
                                            <td class="px-4 py-2">
                                                {{ formatDate(dist.detalle_gasto_comun.gastos_comune?.fecha_vencimiento)
                                                }}
                                            </td>
                                            <td class="px-4 py-2">
                                                <div class="flex space-x-2">
                                                    <Link :href="route('distribucion_gasto.edit', dist.id_distribucion)"
                                                        class="text-blue-600 hover:underline">
                                                    Editar
                                                    </Link>
                                                    <form
                                                        @submit.prevent="$inertia.delete(route('distribucion_gasto.destroy', dist.id_distribucion))">
                                                        <button type="submit" class="text-red-600 hover:underline">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </transition>
                    </div>

                    <div class="mt-4">
                        <Pagination :links="distribuciones.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    distribuciones: Object,
});

const distribuciones = props.distribuciones;

// Agrupar distribuciones por el id del gasto común
const groupedDistribuciones = computed(() => {
    const groups = {};
    if (!distribuciones.data) return groups;
    distribuciones.data.forEach(dist => {
        const key = dist.detalle_gasto_comun.gastos_comune?.id_gasto || 'sin_gasto';
        if (!groups[key]) groups[key] = [];
        groups[key].push(dist);
    });
    return groups;
});

// Control de grupos expandidos/contraídos
const expandedGroups = ref({});
function toggleGroup(key) {
    expandedGroups.value[key] = !expandedGroups.value[key];
}

// Función para formatear fecha
function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const dateObj = new Date(dateStr);
    return isNaN(dateObj) ? 'N/A' : dateObj.toLocaleDateString();
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
