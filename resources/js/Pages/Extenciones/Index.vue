<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, usePage, router as Inertia } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    extenciones: Object,
});

// Extraemos los flash messages de la página
const page = usePage();
const flashSuccess = ref(page.props.flash.success || '');
const flashError = ref(page.props.flash.error || '');

// Limpiamos los mensajes flash después de 3 segundos
onMounted(() => {
    if (flashSuccess.value || flashError.value) {
        setTimeout(() => {
            flashSuccess.value = '';
            flashError.value = '';
        }, 3000);
    }
});

const extencionesData = computed(() => props.extenciones.data || []);
const searchQuery = ref('');

const filteredExtenciones = computed(() => {
    if (!searchQuery.value) return extencionesData.value;
    const q = searchQuery.value.toLowerCase();
    return extencionesData.value.filter(extencion => {
        const nombre = extencion.nombre || '';
        const tipo = extencion.tipo_extencion || '';
        const edificio = extencion.edificio ? extencion.edificio.nombre : '';
        return (
            nombre.toLowerCase().includes(q) ||
            tipo.toLowerCase().includes(q) ||
            edificio.toLowerCase().includes(q)
        );
    });
});

function clearSearch() {
    searchQuery.value = '';
}

const showConfirmModal = ref(false);
const extencionToDelete = ref(null);
function openDeleteModal(extencion) {
    extencionToDelete.value = extencion;
    showConfirmModal.value = true;
}
function confirmDelete() {
    if (!extencionToDelete.value) return;
    Inertia.delete(route('extenciones.destroy', extencionToDelete.value.id_extencion), {
        onFinish: () => {
            showConfirmModal.value = false;
            extencionToDelete.value = null;
        }
    });
}

// Objeto reactivo para controlar la expansión de detalles de cada extención
const expanded = ref({});

// Para forzar la reactividad, actualizamos expanded usando el spread operator
function toggleDetails(id) {
    expanded.value = { ...expanded.value, [id]: !expanded.value[id] };
}
</script>

<template>

    <Head title="Listado de Extenciones" />
    <AuthenticatedLayout>
        <!-- Encabezado -->
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Estas en: Extenciones / Lista</h3>
                <Link :href="route('extenciones.create')" class="text-white hover:underline font-semibold">
                Crear Extención
                </Link>
            </div>
        </template>

        <!-- Mensajes flash que desaparecen después de 3 segundos -->
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 mt-3">
            <div v-if="flashSuccess" class="p-2 bg-green-100 text-green-700 rounded mb-3 text-sm">
                {{ flashSuccess }}
            </div>
            <div v-if="flashError" class="p-2 bg-red-100 text-red-700 rounded mb-3 text-sm">
                {{ flashError }}
            </div>
        </div>

        <!-- Buscador y tabla -->
        <div class="py-3">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="mb-3 max-w-md relative">
                        <label for="search" class="block font-semibold text-gray-700 mb-1 text-sm">
                            Buscar extenciones
                        </label>
                        <input id="search" v-model="searchQuery" type="text" placeholder="Ej: Nombre, tipo, edificio..."
                            class="w-full rounded-md border border-gray-300 p-1 text-sm focus:border-green-500 focus:ring-green-500" />
                        <button v-if="searchQuery" @click="clearSearch"
                            class="absolute right-2 top-8 text-gray-500 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <h2 class="text-lg font-semibold mb-2">Listado de Extenciones</h2>
                    <div class="relative overflow-x-auto overflow-y-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Nombre</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Tipo</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Edificio</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Área</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Cobro Único</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Servicios
                                        Asociados</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template v-for="extencion in filteredExtenciones" :key="extencion.id_extencion">
                                    <tr class="hover:bg-gray-100">
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ extencion.nombre }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ extencion.tipo_extencion }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-700">
                                            {{ extencion.edificio ? extencion.edificio.nombre : '' }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-700">
                                            {{ extencion.area }} {{ extencion.unidad_medida }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-700">
                                            {{ extencion.cobro_unico == 1 ? 'Sí' : 'No' }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-700">
                                            <button @click="toggleDetails(extencion.id_extencion)"
                                                class="px-2 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200">
                                                {{ expanded[extencion.id_extencion] ? '- Servicios' : '+ Servicios(' +
                                                    (Array.isArray(extencion.servicios_extras) ?
                                                extencion.servicios_extras.length : 0) + ')' }}
                                            </button>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-700">
                                            <div class="flex items-center space-x-3">
                                                <Link :href="route('extenciones.edit', extencion.id_extencion)"
                                                    class="text-blue-600 hover:underline">
                                                Editar
                                                </Link>
                                                <button @click="openDeleteModal(extencion)"
                                                    class="text-red-600 hover:underline">
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Fila de detalles: Servicios asociados -->
                                    <tr v-if="expanded[extencion.id_extencion]">
                                        <td colspan="7" class="px-4 py-2">
                                            <div
                                                v-if="Array.isArray(extencion.servicios_extras) && extencion.servicios_extras.length">
                                                <h4 class="font-semibold mb-1">Servicios Asociados:</h4>
                                                <ul class="list-disc pl-5">
                                                    <li v-for="(serv, i) in extencion.servicios_extras"
                                                        :key="serv.id || i">
                                                        Servicio ID: {{ serv.id_tipo_gasto }} – Porcentaje: {{
                                                        serv.porcentaje_extra }}%
                                                    </li>
                                                </ul>
                                            </div>
                                            <div v-else>
                                                No hay servicios asociados.
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <Pagination :links="props.extenciones.links" />
                    </div>
                </div>
            </div>
        </div>
        <ConfirmationModal :visible="showConfirmModal"
            :message="`¿Desea eliminar la extención ${extencionToDelete?.nombre || ''}?`"
            @cancel="showConfirmModal = false" @confirm="confirmDelete" />
    </AuthenticatedLayout>
</template>
