<template>

    <Head title="Usuarios" />
    <AuthenticatedLayout>
        <!-- Encabezado -->
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-3 rounded-md">
                <h3 class="text-lg font-semibold text-white">Estas en: Usuarios / Lista</h3>
                <Link :href="route('users.create')"
                    class="text-white hover:underline font-semibold flex items-center space-x-1">
                <span>Crear Usuario</span>
                </Link>
            </div>
        </template>

        <!-- Mensajes Flash -->
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 mt-3">
            <transition name="fade">
                <div v-if="showSuccess && successMessage" class="p-2 bg-green-100 text-green-700 rounded mb-3 text-sm">
                    {{ successMessage }}
                </div>
            </transition>
            <transition name="fade">
                <div v-if="showError && errorMessage" class="p-2 bg-red-100 text-red-700 rounded mb-3 text-sm">
                    {{ errorMessage }}
                </div>
            </transition>
        </div>

        <div class="py-3">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <!-- Campo de búsqueda en vivo -->
                    <div class="mb-3 max-w-md relative">
                        <label for="search" class="block font-semibold text-gray-700 mb-1 text-sm">
                            Buscar usuarios
                        </label>
                        <input id="search" v-model="searchQuery" type="text" placeholder="Ej: Juan, email..."
                            class="w-full rounded-md border border-gray-300 p-1 text-sm focus:border-green-500 focus:ring-green-500" />
                        <button v-if="searchQuery" @click="clearSearch"
                            class="absolute right-2 top-8 text-gray-500 hover:text-gray-700">
                            <XMarkIcon class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Tabla de usuarios -->
                    <h2 class="text-lg font-semibold mb-2">Listado de Usuarios</h2>
                    <div class="relative overflow-x-auto overflow-y-hidden">
                        <table class="w-full text-sm text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 text-center">
                                <tr>
                                    <th class="px-4 py-2">Nombre</th>
                                    <th class="px-4 py-2">Email</th>
                                    <th class="px-4 py-2">Rol</th>
                                    <th class="px-4 py-2">Estado</th>
                                    <th class="px-4 py-2">Edificio / Unidad</th>
                                    <!-- Columna Extenciones -->
                                    <th class="px-4 py-2">Extenciones</th>
                                    <th class="px-4 py-2">Documento</th>
                                    <th class="px-4 py-2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Iteramos usuarios -->
                                <template v-for="user in filteredUsers" :key="user.id">
                                    <!-- Fila principal -->
                                    <tr class="bg-white border-b text-center">
                                        <td class="px-4 py-2 text-gray-900">
                                            {{ user.name }} {{ user.apellidos }}
                                        </td>
                                        <td class="px-4 py-2">{{ user.email }}</td>
                                        <!-- Rol -->
                                        <td class="px-4 py-2">
                                            <span v-if="user.roles && user.roles.length > 0">
                                                {{ user.roles[0].nombre }}
                                            </span>
                                            <span v-else>N/A</span>
                                        </td>
                                        <!-- Estado -->
                                        <td class="px-4 py-2">{{ user.estado ? 'Activo' : 'Inactivo' }}</td>
                                        <!-- Edificio / Unidad -->
                                        <td class="px-4 py-2 text-left">
                                            <span v-if="user.unidad">
                                                <!-- Si no quieres relacionar con edificios, puedes mostrar solo la unidad -->
                                                {{ user.unidad.nombre_unidad }}
                                            </span>
                                            <span v-else>N/A</span>
                                        </td>
                                        <!-- Botón Extenciones -->
                                        <td class="px-4 py-2">
                                            <button @click="toggleExtenciones(user.id)"
                                                class="px-2 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 text-xs">
                                                <span v-if="expanded[user.id]">
                                                    – Extenciones
                                                </span>
                                                <span v-else>
                                                    + Extenciones ({{ Array.isArray(user.extenciones) ?
                                                    user.extenciones.length : 0 }})
                                                </span>
                                            </button>
                                        </td>
                                        <!-- Documento -->
                                        <td class="px-4 py-2">
                                            <span v-if="user.tipo_documento && user.numero_documento">
                                                {{ user.tipo_documento }} - {{ user.numero_documento }}
                                            </span>
                                            <span v-else>-</span>
                                        </td>
                                        <!-- Acciones -->
                                        <td class="px-4 py-2">
                                            <div class="flex items-center justify-center space-x-2">
                                                <Link :href="route('users.edit', user.id)"
                                                    class="inline-flex items-center text-blue-600 hover:underline text-sm">
                                                <PencilSquareIcon class="h-4 w-4 mr-1" />
                                                Editar
                                                </Link>
                                                <button @click="openDeleteModal(user)"
                                                    class="inline-flex items-center text-red-600 hover:underline text-sm">
                                                    <TrashIcon class="h-4 w-4 mr-1" />
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Fila de detalles: Extenciones asociadas -->
                                    <tr v-if="expanded[user.id]">
                                        <td colspan="8" class="bg-gray-50 text-sm text-gray-700 p-3">
                                            <div v-if="Array.isArray(user.extenciones) && user.extenciones.length > 0">
                                                <h4 class="font-semibold mb-1">Extenciones del Usuario:</h4>
                                                <ul class="list-disc pl-5">
                                                    <li v-for="(ext, i) in user.extenciones"
                                                        :key="ext.id_extencion || i" class="mb-1">
                                                        <strong>{{ ext.nombre }}</strong>
                                                        <span>- {{ ext.tipo_extencion }} - {{ ext.area }}{{
                                                            ext.unidad_medida }}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div v-else>
                                                <p class="text-gray-600">Este usuario no tiene extenciones asociadas.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Si no hay usuarios filtrados -->
                                <tr v-if="filteredUsers.length === 0">
                                    <td colspan="8" class="px-4 py-2 text-center">
                                        No hay usuarios registrados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Paginación -->
                <div class="px-4 mt-3">
                    <Pagination :links="users.links" />
                </div>
            </div>
        </div>

        <!-- Modal de confirmación para eliminar -->
        <ConfirmationModal :visible="showConfirmModal"
            :message="`¿Desea eliminar al usuario ${userToDelete?.name || ''}?`" @cancel="showConfirmModal = false"
            @confirm="confirmDelete" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, reactive, watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

// Componentes
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Íconos Heroicons
import { PencilSquareIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/solid';

// Extraemos props de la página
const page = usePage();
const users = page.props.users;

// Flash messages
const showSuccess = ref(false);
const showError = ref(false);
const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

watch(successMessage, (val) => {
    if (val) {
        showSuccess.value = true;
        setTimeout(() => {
            showSuccess.value = false;
        }, 4000);
    }
});
watch(errorMessage, (val) => {
    if (val) {
        showError.value = true;
        setTimeout(() => {
            showError.value = false;
        }, 4000);
    }
});

// Búsqueda local
const searchQuery = ref('');
const filteredUsers = computed(() => {
    if (!searchQuery.value) return users.data;
    const q = searchQuery.value.toLowerCase();

    return users.data.filter((user) => {
        const name = (user.name || '') + ' ' + (user.apellidos || '');
        const email = user.email || '';
        const role = (user.roles && user.roles.length > 0) ? user.roles[0].nombre : '';
        const unidad = user.unidad ? (user.unidad.nombre_unidad || '') : '';
        const documento = user.tipo_documento && user.numero_documento ? `${user.tipo_documento} ${user.numero_documento}` : '';
        let extencionesString = '';
        if (Array.isArray(user.extenciones)) {
            extencionesString = user.extenciones.map((ext) => ((ext.nombre || '') + ' ' + (ext.tipo_extencion || ''))).join(' ');
        }
        return (
            name.toLowerCase().includes(q) ||
            email.toLowerCase().includes(q) ||
            role.toLowerCase().includes(q) ||
            unidad.toLowerCase().includes(q) ||
            documento.toLowerCase().includes(q) ||
            extencionesString.toLowerCase().includes(q)
        );
    });
});

function clearSearch() {
    searchQuery.value = '';
}

// Modal para eliminar
const showConfirmModal = ref(false);
const userToDelete = ref(null);
function openDeleteModal(user) {
    userToDelete.value = user;
    showConfirmModal.value = true;
}
function confirmDelete() {
    if (!userToDelete.value) return;
    Inertia.delete(route('users.destroy', userToDelete.value.id), {
        onFinish: () => {
            showConfirmModal.value = false;
            userToDelete.value = null;
        },
    });
}

// Objeto reactivo para controlar la expansión de extenciones
const expanded = reactive({});

// Toggle de la visualización de extenciones para un usuario
function toggleExtenciones(userId) {
    expanded[userId] = !expanded[userId];
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
