<template>

    <Head title="Mantenimiento de Tipo Prorrateo Condominio" />

    <AuthenticatedLayout>
        <!-- Encabezado -->
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">
                    Estas en: Tipo Prorrateo Condominio
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
                <!-- Formulario para Agregar/Editar -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h4 class="text-lg font-bold mb-4">
                        {{ isEditing ? "Editar Registro" : "Agregar Nuevo Registro" }}
                    </h4>
                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Select para Tipo Prorrateo -->
                        <div>
                            <InputLabel for="id_tipo_prorrateo" value="Tipo Prorrateo" />
                            <select id="id_tipo_prorrateo" v-model="form.id_tipo_prorrateo" required
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option disabled value="">Seleccione un tipo de prorrateo</option>
                                <option v-for="tipo in tiposProrrateo" :key="tipo.id" :value="tipo.id">
                                    {{ tipo.descripcion }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">💡 FACT-MT2 = Por metros cuadrados, FACT-EQUITATIVO = Igual para todos</p>
                            <InputError :message="form.errors.id_tipo_prorrateo" />
                        </div>

                        <!-- Select para Condominio (solo si es superadmin) -->
                        <div v-if="condominios.length > 0">
                            <InputLabel for="id_condominio" value="Condominio" />
                            <select id="id_condominio" v-model="form.id_condominio" required
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option disabled value="">Seleccione un condominio</option>
                                <option v-for="cond in condominios" :key="cond.id" :value="cond.id">
                                    {{ cond.nombre }}
                                </option>
                            </select>
                            <InputError :message="form.errors.id_condominio" />
                        </div>

                        <!-- Campo para Estado -->
                        <div>
                            <InputLabel for="estado" value="Estado" />
                            <select id="estado" v-model="form.estado" required
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">⚠️ Solo 1 tipo puede estar activo por condominio. El nuevo desactiva los anteriores.</p>
                            <InputError :message="form.errors.estado" />
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-center">
                            <PrimaryButton type="submit" class="flex items-center space-x-1">
                                <template v-if="isEditing">
                                    <PencilSquareIcon class="h-5 w-5" />
                                    <span>Actualizar</span>
                                </template>
                                <template v-else>
                                    <PlusIcon class="h-5 w-5" />
                                    <span>Agregar</span>
                                </template>
                            </PrimaryButton>
                            <button v-if="isEditing" type="button" @click="resetForm"
                                class="px-4 py-2 border rounded ml-4 flex items-center space-x-1">
                                <XMarkIcon class="h-4 w-4" />
                                <span>Cancelar</span>
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
                                    <th class="px-4 py-2 text-left">Tipo Prorrateo</th>
                                    <th class="px-4 py-2 text-left">Condominio</th>
                                    <th class="px-4 py-2 text-left">Estado</th>
                                    <th class="px-4 py-2 text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="registro in registros.data" :key="registro.id">
                                    <td class="px-4 py-2 text-left">{{ registro.id }}</td>
                                    <td class="px-4 py-2 text-left">
                                        <span v-if="registro.tipo_prorrateo">
                                            {{ registro.tipo_prorrateo.descripcion }}
                                        </span>
                                        <span v-else>Sin tipo</span>
                                    </td>
                                    <td class="px-4 py-2 text-left">
                                        <span v-if="registro.condominio">
                                            {{ registro.condominio.nombre }}
                                        </span>
                                        <span v-else>Sin condominio</span>
                                    </td>
                                    <td class="px-4 py-2 text-left">
                                        <button @click="toggleEstado(registro)" 
                                            :disabled="isLoading"
                                            class="inline-flex items-center space-x-2 px-3 py-1 rounded-full transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                                            :class="registro.estado == 1 
                                                ? 'bg-green-100 text-green-700 border-2 border-green-500' 
                                                : 'bg-gray-100 text-gray-500 border-2 border-gray-300 hover:bg-gray-200'"
                                        >
                                            <!-- Spinner cuando está cargando -->
                                            <svg v-if="isLoading && registro.estado == 0" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <!-- Círculo normal -->
                                            <div v-else class="h-4 w-4 rounded-full transition-colors" 
                                                :class="registro.estado == 1 ? 'bg-green-500' : 'bg-gray-400'"></div>
                                            <span class="font-medium text-sm">{{ registro.estado == 1 ? 'Activo' : 'Inactivo' }}</span>
                                        </button>
                                    </td>
                                    <td class="px-4 py-2 text-left">
                                        <button @click="confirmDelete(registro)"
                                            class="text-red-600 hover:underline inline-flex items-center space-x-1">
                                            <TrashIcon class="h-4 w-4" />
                                            <span>Eliminar</span>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="registros.data.length === 0">
                                    <td colspan="5" class="text-center py-4">No hay registros.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <Pagination :links="registros.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación para Actualización -->
        <div v-if="showUpdateModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded p-6 w-96">
                <h2 class="text-xl font-bold mb-4">Confirmar Actualización</h2>
                <p class="mb-4">
                    Estás por actualizar el registro con tipo prorrateo
                    "<strong>{{ currentRegistro.tipo_prorrateo ? currentRegistro.tipo_prorrateo.descripcion : ''
                        }}</strong>".
                </p>
                <div class="flex justify-end">
                    <button @click="cancelUpdate" class="px-4 py-2 mr-2 border rounded">
                        Cancelar
                    </button>
                    <button @click="confirmUpdate" class="px-4 py-2 bg-green-600 text-white rounded">
                        Actualizar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación para Eliminación -->
        <div v-if="showDeleteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded p-6 w-96">
                <h2 class="text-xl font-bold mb-4">Confirmar Eliminación</h2>
                <p class="mb-4">
                    ¿Estás seguro de eliminar el registro con tipo prorrateo
                    "<strong>{{ registroToDelete.tipo_prorrateo ? registroToDelete.tipo_prorrateo.descripcion : ''
                        }}</strong>"?
                </p>
                <div class="flex justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 mr-2 border rounded">
                        Cancelar
                    </button>
                    <button @click="deleteRegistro" class="px-4 py-2 bg-red-600 text-white rounded">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import { PencilSquareIcon, TrashIcon, PlusIcon, XMarkIcon } from '@heroicons/vue/24/solid';

// Props recibidas desde el controlador
const props = defineProps({
    registros: Object,
    condominios: {
        type: Array,
        default: () => [],
    },
    tiposProrrateo: {
        type: Array,
        default: () => [],
    },
});

// Accedemos a las props de Inertia de forma reactiva
const { props: pageProps } = usePage();
const flashError = computed(() => pageProps.value?.flash?.error || "");
const flashSuccess = computed(() => pageProps.value?.flash?.success || "");

// Verificamos en consola que llegan los mensajes flash
onMounted(() => {
    console.log('Flash:', pageProps.value?.flash);
    if (flashError.value || flashSuccess.value) {
        setTimeout(() => {
            // Inertia limpia estos mensajes en la siguiente navegación
        }, 4000);
    }
});

// Estado para saber si se está editando y para guardar el registro actual
const isEditing = ref(false);
const currentRegistro = reactive({});

// Formulario con useForm
const form = useForm({
    id_tipo_prorrateo: "",
    id_condominio: props.condominios.length > 0 ? props.condominios[0].id : "",
    estado: "1", // Por defecto activo
});

// Estados para modales
const showUpdateModal = ref(false);
const showDeleteModal = ref(false);
const registroToDelete = reactive({});
const isLoading = ref(false); // Loading state para el toggle

// Función para reiniciar el formulario
function resetForm() {
    form.reset();
    isEditing.value = false;
    Object.keys(currentRegistro).forEach(key => delete currentRegistro[key]);
}

// Función para cargar los datos en el formulario al editar
function editRegistro(registro) {
    isEditing.value = true;
    Object.assign(currentRegistro, registro);
    form.id_tipo_prorrateo = registro.id_tipo_prorrateo;
    form.id_condominio = registro.id_condominio;
    form.estado = registro.estado.toString();
}

// Función para enviar el formulario (crear o actualizar)
function submit() {
    if (isEditing.value && currentRegistro.id) {
        showUpdateModal.value = true;
    } else {
        // Eliminamos preserveState para que Inertia refresque las props y se muestren los flash
        form.post(route("tipo_prorrateo_condominio.store"), {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
            },
        });
    }
}

// Confirmar actualización (desde modal)
function confirmUpdate() {
    form.put(route("tipo_prorrateo_condominio.update", currentRegistro.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
            showUpdateModal.value = false;
        },
    });
}

// Cancelar actualización
function cancelUpdate() {
    showUpdateModal.value = false;
}

// Confirmar eliminación
function confirmDelete(registro) {
    Object.assign(registroToDelete, registro);
    showDeleteModal.value = true;
}

// Ejecutar eliminación
function deleteRegistro() {
    Inertia.delete(route("tipo_prorrateo_condominio.destroy", registroToDelete.id), {
        preserveState: true,
        preserveScroll: true,
    });
    showDeleteModal.value = false;
}

// Nueva función para toggle de estado (funciona como radio button)
function toggleEstado(registro) {
    // Si ya está activo, no hacer nada
    if (registro.estado == 1) {
        return;
    }
    
    // Mostrar loading
    isLoading.value = true;
    
    // Activar este registro (automáticamente desactiva los demás en el backend)
    form.id_tipo_prorrateo = registro.id_tipo_prorrateo;
    form.id_condominio = registro.id_condominio;
    form.estado = "1";
    
    form.put(route("tipo_prorrateo_condominio.update", registro.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
        }
    });
}
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
