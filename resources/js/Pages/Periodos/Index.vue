<template>
    <Head title="Mantenimiento de Periodos" />

    <AuthenticatedLayout>
        <!-- Encabezado -->
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">
                    Estas en: Periodos
                </h3>
            </div>
        </template>

        <!-- Mensajes Flash -->
        <div class="py-4">
            <transition name="fade">
                <div v-if="flashError" class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-4">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <strong class="font-bold">Acción Bloqueada: </strong>
                        <span class="block sm:inline">{{ flashError }}</span>
                    </div>
                </div>
            </transition>
            <transition name="fade">
                <div v-if="flashSuccess" class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-4">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        <strong class="font-bold">Éxito: </strong>
                        <span class="block sm:inline">{{ flashSuccess }}</span>
                    </div>
                </div>
            </transition>
        </div>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                <!-- Formulario para Agregar / Editar -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h4 class="text-lg font-bold mb-4">
                        {{ isEditing ? "Editar Periodo" : "Agregar Nuevo Periodo" }}
                    </h4>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <!-- Si es superadmin, mostrar <select> de condominios -->
                        <div v-if="condominios.length > 0">
                            <label for="idcondominio" class="block text-sm font-semibold text-gray-700 mb-1">
                                Condominio
                            </label>
                            <select id="idcondominio" v-model="form.idcondominio"
                                class="w-full border-gray-300 rounded-md shadow-sm" :disabled="isEditing" required>
                                <option disabled value="">Seleccione un condominio</option>
                                <option v-for="condo in condominios" :key="condo.id" :value="condo.id.toString()">
                                    {{ condo.nombre }}
                                </option>
                            </select>
                            <InputError :message="form.errors.idcondominio" />
                        </div>

                        <!-- DÍA INICIO -->
                        <div>
                            <label for="dia_inicio" class="block text-sm font-semibold text-gray-700 mb-1">
                                Día Inicio
                            </label>
                            <input type="number" id="dia_inicio" min="1" max="31" v-model="form.dia_inicio"
                                class="w-full border-gray-300 rounded-md shadow-sm" required />
                            <InputError :message="form.errors.dia_inicio" />
                            <p v-if="form.dia_inicio" class="text-xs text-gray-500 mt-1">
                                Ej: Fecha de inicio: {{ exampleInicio }}
                            </p>
                        </div>

                        <!-- DÍA FIN (inhabilitado, se asigna automáticamente) -->
                        <div>
                            <label for="dia_fin" class="block text-sm font-semibold text-gray-700 mb-1">
                                Día Fin
                            </label>
                            <input type="number" id="dia_fin" min="1" max="31" v-model="form.dia_fin"
                                class="w-full border-gray-300 rounded-md shadow-sm" required disabled />
                            <InputError :message="form.errors.dia_fin" />
                            <p v-if="form.dia_inicio" class="text-xs text-gray-500 mt-1">
                                Ej: Fecha de fin: {{ exampleFin }}
                            </p>
                        </div>

                        <!-- DÍA VENCIMIENTO -->
                        <div>
                            <label for="dia_vencimiento" class="block text-sm font-semibold text-gray-700 mb-1">
                                Día Vencimiento
                            </label>
                            <input type="number" id="dia_vencimiento" min="1" max="31" v-model="form.dia_vencimiento"
                                class="w-full border-gray-300 rounded-md shadow-sm" required />
                            <InputError :message="form.errors.dia_vencimiento" />
                            <p v-if="form.dia_vencimiento" class="text-xs text-gray-500 mt-1">
                                Ej: Fecha de vencimiento: {{ exampleVencimiento }}
                            </p>
                        </div>

                        <!-- ESTADO -->
                        <div>
                            <label for="estado" class="block text-sm font-semibold text-gray-700 mb-1">
                                Estado
                            </label>
                            <select id="estado" v-model="form.estado"
                                class="w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            <InputError :message="form.errors.estado" />
                        </div>

                        <!-- BOTONES -->
                        <div class="flex justify-center">
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">
                                <template v-if="isEditing">Actualizar</template>
                                <template v-else>Agregar</template>
                            </button>
                            <button v-if="isEditing" type="button" @click="cancelEdit"
                                class="px-4 py-2 border rounded ml-4">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabla de Registros -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">ID</th>
                                    <th class="px-4 py-2 text-left">Día Inicio</th>
                                    <th class="px-4 py-2 text-left">Día Fin</th>
                                    <th class="px-4 py-2 text-left">Día Venc.</th>
                                    <th class="px-4 py-2 text-left">Estado</th>
                                    <th class="px-4 py-2 text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="p in periodos.data" :key="p.id">
                                    <td class="px-4 py-2 text-left">{{ p.id }}</td>
                                    <td class="px-4 py-2 text-left">{{ p.dia_inicio }}</td>
                                    <td class="px-4 py-2 text-left">{{ p.dia_fin }}</td>
                                    <td class="px-4 py-2 text-left">{{ p.dia_vencimiento }}</td>
                                    <td class="px-4 py-2 text-left">
                                        <span :class="p.estado === 1 ? 'text-green-600' : 'text-gray-600'">
                                            {{ p.estado === 1 ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-left">
                                        <button @click="editPeriodo(p)" class="text-blue-600 hover:underline mr-2">
                                            Editar
                                        </button>
                                        <button @click="confirmDelete(p)" class="text-red-600 hover:underline">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="periodos.data.length === 0">
                                    <td colspan="6" class="text-center py-4">No hay registros.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <Pagination :links="periodos.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmación para actualización -->
        <div v-if="showUpdateModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded p-6 w-96">
                <h2 class="text-xl font-bold mb-4">Confirmar Actualización</h2>
                <p class="mb-4">
                    Estás por actualizar el periodo
                    "<strong>{{ currentPeriodo.nombre_periodo }}</strong>".
                    <span v-if="currentPeriodo.estado === 1">
                        Al activarlo, los demás periodos de este condominio se inactivarán.
                    </span>
                </p>
                <div class="flex justify-end">
                    <button @click="cancelUpdate" class="px-4 py-2 mr-2 border rounded">
                        Cancelar
                    </button>
                    <button @click="confirmUpdate" class="px-4 py-2 bg-green-600 text-white rounded">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de confirmación para eliminación -->
        <div v-if="showDeleteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded p-6 w-96">
                <h2 class="text-xl font-bold mb-4">Confirmar Eliminación</h2>
                <p class="mb-4">
                    ¿Estás seguro de eliminar el periodo
                    "<strong>{{ periodoToDelete.nombre_periodo }}</strong>"?
                </p>
                <div class="flex justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 mr-2 border rounded">
                        Cancelar
                    </button>
                    <button @click="deletePeriodo" class="px-4 py-2 bg-red-600 text-white rounded">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import InputError from "@/Components/InputError.vue";

// Desestructuramos las props para tener disponibles periodos, condominios y filters
const { periodos, condominios, filters } = defineProps({
    periodos: Object,
    condominios: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ search: "" }),
    },
});

// Flash messages
const { props: pageProps } = usePage();
const flashError = ref(pageProps.flash.error || "");
const flashSuccess = ref(pageProps.flash.success || "");

// Ocultar mensajes después de 4s
onMounted(() => {
    if (flashError.value || flashSuccess.value) {
        setTimeout(() => {
            flashError.value = "";
            flashSuccess.value = "";
        }, 4000);
    }
});

// Estado de edición y objeto para el período actual
const isEditing = ref(false);
const currentPeriodo = reactive({});

// Formulario con useForm
const form = useForm({
    idcondominio: "",
    dia_inicio: "",
    dia_fin: "",
    dia_vencimiento: "",
    estado: "1", // por defecto activo
});

// Sincroniza automáticamente el día fin con el día inicio
watch(() => form.dia_inicio, (newVal) => {
    form.dia_fin = newVal;
});

// Estados para modales
const showUpdateModal = ref(false);
const showDeleteModal = ref(false);
const periodoToDelete = reactive({});

// Función para editar un período
function editPeriodo(p) {
    isEditing.value = true;
    Object.assign(currentPeriodo, p);
    form.idcondominio = p.idcondominio ? p.idcondominio.toString() : "";
    form.dia_inicio = p.dia_inicio;
    form.dia_fin = p.dia_fin;
    form.dia_vencimiento = p.dia_vencimiento;
    form.estado = p.estado.toString();
}

// Cancelar edición
function cancelEdit() {
    form.reset();
    isEditing.value = false;
    Object.keys(currentPeriodo).forEach((k) => delete currentPeriodo[k]);
}

// Función para crear o actualizar
function submitForm() {
    // Validación básica para evitar campos vacíos
    if (!form.idcondominio && condominios.length > 0) return;
    if (!form.dia_inicio || !form.dia_vencimiento) return;

    if (isEditing.value && currentPeriodo.id) {
        showUpdateModal.value = true;
    } else {
        form.post(route("periodos.store"), {
            onSuccess: () => {
                cancelEdit();
            },
        });
    }
}

// Confirmar actualización
function confirmUpdate() {
    if (!currentPeriodo.id) return;
    form.put(route("periodos.update", currentPeriodo.id), {
        onSuccess: () => {
            cancelEdit();
            showUpdateModal.value = false;
        },
    });
}

// Cancelar actualización
function cancelUpdate() {
    showUpdateModal.value = false;
}

// Confirmar eliminación
function confirmDelete(p) {
    Object.assign(periodoToDelete, p);
    showDeleteModal.value = true;
}

// Eliminar período
function deletePeriodo() {
    Inertia.delete(route("periodos.destroy", periodoToDelete.id));
    showDeleteModal.value = false;
}

// Funciones para búsqueda
const search = ref(filters.search || "");
function searchData() {
    Inertia.get(route("periodos.index"), { search: search.value }, { replace: true });
}
function clearSearch() {
    search.value = "";
    searchData();
}

// Cálculo de ejemplos de fechas usando la fecha actual como base
const today = new Date();
const currentMonth = today.getMonth(); // 0-indexado
const currentYear = today.getFullYear();
const monthNames = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
const padDay = (day) => day.toString().padStart(2, '0');

// Ejemplo para fecha de inicio: se usa el mes actual
const exampleInicio = computed(() => {
    if (!form.dia_inicio) return "";
    const day = parseInt(form.dia_inicio);
    const date = new Date(currentYear, currentMonth, day);
    return `${padDay(day)} de ${monthNames[date.getMonth()]} del ${date.getFullYear()}`;
});

// Ejemplo para fecha de fin: se usa el mes siguiente
const exampleFin = computed(() => {
    if (!form.dia_inicio) return "";
    const day = parseInt(form.dia_inicio);
    let finishMonth = currentMonth + 1;
    let finishYear = currentYear;
    if (finishMonth > 11) {
        finishMonth = finishMonth % 12;
        finishYear++;
    }
    const date = new Date(finishYear, finishMonth, day);
    return `${padDay(day)} de ${monthNames[date.getMonth()]} del ${date.getFullYear()}`;
});

// Ejemplo para fecha de vencimiento: ahora se calcula con 1 mes de diferencia (igual que fecha fin)
const exampleVencimiento = computed(() => {
    if (!form.dia_vencimiento) return "";
    const day = parseInt(form.dia_vencimiento);
    let vencMonth = currentMonth + 1;
    let vencYear = currentYear;
    if (vencMonth > 11) {
        vencMonth = vencMonth % 12;
        vencYear++;
    }
    const date = new Date(vencYear, vencMonth, day);
    return `${padDay(day)} de ${monthNames[date.getMonth()]} del ${date.getFullYear()}`;
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 1s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
