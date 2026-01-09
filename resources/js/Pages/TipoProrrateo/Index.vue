<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from "@/Components/Pagination.vue";
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

// Props del controlador
const props = defineProps({
    tipos: Object,
    filters: Object,
});

// Computamos los datos
const tiposData = computed(() => props.tipos.data || []);

// Extraemos la información del usuario autenticado para conocer su rol
const { props: pageProps } = usePage();
const userRole = computed(() => pageProps.auth.user?.roles?.[0] ?? null);
const userRoleId = computed(() => userRole.value?.id_rol ?? null);

// Estado para el modal de eliminación
const showModal = ref(false);
const tipoIdToDelete = ref(null);

// Abrir modal
function openModal(tipoId) {
    tipoIdToDelete.value = tipoId;
    showModal.value = true;
}

// Confirmar eliminación
function confirmDelete() {
    router.delete(route('tipos.destroy', tipoIdToDelete.value));
    showModal.value = false;
    tipoIdToDelete.value = null;
}

// Cancelar
function cancelDelete() {
    showModal.value = false;
    tipoIdToDelete.value = null;
}
</script>

<template>
    <Head title="Listado de Tipos Prorrateo" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Listado de Tipos Prorrateo</h3>
                <!-- Mostrar el enlace Crear Tipo solo si el rol es 1 o está vacío -->
                <template v-if="userRoleId === 1 || !userRoleId || userRoleId === 2">
                    <Link :href="route('tipos.create')" class="text-white hover:underline font-semibold">
                        Crear Tipo
                    </Link>
                </template>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">ID</th>
                                <th class="px-4 py-2">Descripción</th>
                                <th class="px-4 py-2">Acciones</th>
                                <!-- Acciones solo para superadmin
                                <th class="px-4 py-2" v-if="userRoleId === 1 || !userRoleId">Acciones</th>-->
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="tipo in tiposData" :key="tipo.id">
                                <td class="px-4 py-2">{{ tipo.id }}</td>
                                <td class="px-4 py-2">{{ tipo.descripcion }}</td>
                                <td class="px-4 py-2" >
                               <!-- <td class="px-4 py-2" v-if="userRoleId === 1 || !userRoleId">-->
                                    <div class="flex space-x-2">
                                        <Link :href="route('tipos.edit', tipo.id)" class="text-blue-600 hover:underline">
                                            Editar
                                        </Link>
                                        <button @click="openModal(tipo.id)" class="text-red-600 hover:underline">
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="tiposData.length === 0">
                                <td colspan="3" class="px-4 py-4 text-center">
                                    No hay tipos de prorrateo registrados.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Paginación -->
                    <div class="mt-4">
                        <Pagination :links="props.tipos.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para confirmación de eliminación -->
        <transition name="fade">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <transition name="scale">
                    <div v-if="showModal" class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm">
                        <h2 class="text-lg font-bold mb-4">Confirmar Eliminación</h2>
                        <p class="mb-4">
                            ¿Estás seguro de que deseas eliminar este tipo de prorrateo?
                        </p>
                        <div class="flex justify-end space-x-4">
                            <button @click="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                Sí, eliminar
                            </button>
                            <button @click="cancelDelete" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.scale-enter-active,
.scale-leave-active {
    transition: all 0.3s ease-out;
}
.scale-enter-from {
    transform: scale(0.95);
    opacity: 0;
}
.scale-enter-to {
    transform: scale(1);
    opacity: 1;
}
.scale-leave-from {
    transform: scale(1);
    opacity: 1;
}
.scale-leave-to {
    transform: scale(0.95);
    opacity: 0;
}
</style>
