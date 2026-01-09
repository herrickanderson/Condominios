<template>

    <Head title="Mantenimiento de Tipo de Gasto Común" />
    <AuthenticatedLayout>
        <!-- Encabezado -->
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Estas en: Tipo de gasto comun / Lista</h3>
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
                        {{ isEditing ? "Editar Tipo de Gasto Común" : "Agregar Nuevo Tipo de Gasto Común" }}
                    </h4>
                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Campo Nombre -->
                        <div>
                            <InputLabel for="nombre" value="Nombre" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <DocumentTextIcon class="h-5 w-5 text-gray-400" />
                                </div>
                                <TextInput id="nombre" type="text" v-model="form.nombre" required
                                    class="w-full pl-10" />
                            </div>
                            <InputError :message="form.errors.nombre" />
                        </div>

                        <!-- Condominio (para SuperAdmin) -->
                        <div v-if="condominios.length > 0">
                            <InputLabel for="id_condominio" value="Condominio" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <BuildingOffice2Icon class="h-5 w-5 text-gray-400" />
                                </div>
                                <select id="id_condominio" v-model="form.id_condominio" required
                                    class="w-full border-gray-300 rounded-md shadow-sm pl-10">
                                    <option disabled value="">Seleccione un condominio</option>
                                    <option v-for="cond in condominios" :key="cond.id" :value="cond.id">
                                        {{ cond.nombre }}
                                    </option>
                                </select>
                            </div>
                            <InputError :message="form.errors.id_condominio" />
                        </div>

                        <!-- Vista Prorrateo de Condominio -->
                        <div v-if="form.id_condominio">
                            <p class="text-sm text-gray-700">
                                Prorrateo de Condominio:
                                <span class="font-semibold">
                                    {{ prorrateoCondominio[form.id_condominio]?.descripcion || "Sin prorrateo" }}
                                </span>
                            </p>
                        </div>

                        <!-- Categoría -->
                        <div v-if="categorias.length > 0">
                            <InputLabel for="id_categoria" value="Categoría" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <FolderIcon class="h-5 w-5 text-gray-400" />
                                </div>
                                <select id="id_categoria" v-model="form.id_categoria" required
                                    class="w-full border-gray-300 rounded-md shadow-sm pl-10">
                                    <option disabled value="">Seleccione una categoría</option>
                                    <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">
                                        {{ cat.nombre }}
                                    </option>
                                </select>
                            </div>
                            <InputError :message="form.errors.id_categoria" />
                        </div>
                        <!-- Link para agregar categorías en caso de que no existan -->
                        <div v-else class="mb-4">
                            <p class="text-sm text-gray-600">
                                No hay categorías agregadas.
                                <a :href="route('categoria_gasto_comun.create')"
                                    class="ml-2 text-blue-600 font-bold hover:underline">
                                    Haga clic para agregar.
                                </a>
                            </p>
                        </div>

                        <!-- Checkbox de Consumo -->
                        <div class="flex items-center">
                            <input id="consumo" type="checkbox" v-model="form.consumo" class="mr-2">
                            <label for="consumo" class="text-gray-700">Capturar consumo</label>
                        </div>
                        <!-- Selector para Medida (solo si se marca Capturar consumo) -->
                        <div v-if="form.consumo" class="mt-2">
                            <InputLabel for="medida" value="Medida" />
                            <select id="medida" v-model="form.medida"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option disabled value="">Seleccione una unidad de medida</option>
                                <option value="litros">Litros</option>
                                <option value="kwh">KWh</option>
                                <option value="m3">m³</option>
                            </select>
                            <InputError :message="form.errors.medida" />
                        </div>

                        <!-- Selector para gasto individual (si aplica) -->
                        <div v-if="parseInt(form.aplica_prorrateo_condominio) === 1">
                            <InputLabel for="tipo_prorrateo_id" value="Seleccione el tipo de gasto individual" />
                            <select id="tipo_prorrateo_id" v-model="form.tipo_prorrateo_id"
                                class="w-full border rounded p-2">
                                <option disabled value="">Seleccione un tipo</option>
                                <option v-for="opt in tipoProrrateo" :key="opt.id" :value="opt.id">
                                    {{ opt.descripcion }}
                                </option>
                            </select>
                            <InputError :message="form.errors.tipo_prorrateo_id" />
                        </div>

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
                                <XMarkIcon class="h-4 w-4 text-gray-600" />
                                <span>Cancelar</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabla de Registros (texto reducido) -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2">ID</th>
                                    <th class="px-4 py-2">Nombre</th>
                                    <th class="px-4 py-2">Categoría</th>
                                    <th class="px-4 py-2">Medida</th>
                                    <th class="px-4 py-2">Consumo</th>
                                    <th class="px-4 py-2">Prorrateo</th>
                                    <th class="px-4 py-2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="tipo in tipos.data" :key="tipo.id_tipo_gasto">
                                    <td class="px-4 py-2">{{ tipo.id_tipo_gasto }}</td>
                                    <td class="px-4 py-2">{{ tipo.nombre }}</td>
                                    <td class="px-4 py-2">
                                        <span v-if="tipo.categoria">{{ tipo.categoria.nombre }}</span>
                                        <span v-else>Sin Categoría</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span v-if="tipo.consumo">{{ tipo.medida || '-' }}</span>
                                        <span v-else>-</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span v-if="tipo.consumo">Sí</span>
                                        <span v-else>No</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span v-if="tipo.aplica_prorrateo_condominio == 0">
                                            {{ prorrateoCondominio[tipo.id_condominio]?.descripcion || "Sin prorrateo"
                                            }}
                                        </span>
                                        <span v-else>
                                            {{tipoProrrateo.find(item => item.id ===
                                                tipo.tipo_prorrateo_id)?.descripcion ||
                                            "Sin gasto individual" }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <button @click="editTipo(tipo)"
                                            class="text-blue-600 hover:underline mr-2 inline-flex items-center space-x-1">
                                            <PencilSquareIcon class="h-4 w-4" />
                                            <span>Editar</span>
                                        </button>
                                        <button @click="confirmDelete(tipo)"
                                            class="text-red-600 hover:underline inline-flex items-center space-x-1">
                                            <TrashIcon class="h-4 w-4" />
                                            <span>Eliminar</span>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="tipos.data.length === 0">
                                    <td colspan="7" class="text-center py-4">No hay registros.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <Pagination :links="tipos.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmación para actualización -->
        <div v-if="showUpdateModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded p-6 w-96">
                <h2 class="text-xl font-bold mb-4">Confirmar Actualización</h2>
                <p class="mb-4">
                    Estás por actualizar el servicio "<strong>{{ currentTipo.nombre }}</strong>".
                    <span v-if="currentTipo.unidades_servicio_extras_count">
                        (Relacionado con {{ currentTipo.unidades_servicio_extras_count }} unidad(es))
                    </span>
                </p>
                <div class="flex justify-end">
                    <button @click="cancelUpdate" class="px-4 py-2 mr-2 border rounded">Cancelar</button>
                    <button @click="confirmUpdate" class="px-4 py-2 bg-green-600 text-white rounded">Actualizar</button>
                </div>
            </div>
        </div>

        <!-- Modal de confirmación para eliminación -->
        <div v-if="showDeleteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded p-6 w-96">
                <h2 class="text-xl font-bold mb-4">Confirmar Eliminación</h2>
                <p class="mb-4">¿Estás seguro de eliminar el servicio "<strong>{{ tipoToDelete.nombre }}</strong>"?</p>
                <div class="flex justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 mr-2 border rounded">Cancelar</button>
                    <button @click="deleteTipo" class="px-4 py-2 bg-red-600 text-white rounded">Eliminar</button>
                </div>
            </div>
        </div>

        <!-- Modal de error al intentar eliminar -->
        <div v-if="showDeleteErrorModal"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded p-6 w-96">
                <h2 class="text-xl font-bold mb-4">Acción Bloqueada</h2>
                <p class="mb-4">{{ deleteErrorMessage }}</p>
                <div class="flex justify-end">
                    <button @click="closeDeleteErrorModal" class="px-4 py-2 border rounded">Aceptar</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

import {
    DocumentTextIcon,
    BuildingOffice2Icon,
    FolderIcon,
    PencilSquareIcon,
    TrashIcon,
    PlusIcon,
    XMarkIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
    tipos: Object,
    condominios: { type: Array, default: () => [] },
    categorias: { type: Array, default: () => [] },
    prorrateoCondominio: { type: Object, default: () => ({}) },
    tipoProrrateo: { type: Array, default: () => [] }
});

const { props: pageProps } = usePage();
const flashError = ref(pageProps.flash.error || "");
const flashSuccess = ref(pageProps.flash.success || "");

onMounted(() => {
    if (flashError.value || flashSuccess.value) {
        setTimeout(() => {
            flashError.value = "";
            flashSuccess.value = "";
        }, 4000);
    }
});

const isEditing = ref(false);
const currentTipo = reactive({});

const form = useForm({
    nombre: "",
    aplica_a_todos_edificios: true,
    id_condominio: props.condominios.length > 0 ? props.condominios[0].id : "",
    id_categoria: props.categorias.length > 0 ? props.categorias[0].id_categoria : "",
    aplica_prorrateo_condominio: 0,
    tipo_prorrateo_id: "",
    consumo: false,
    medida: ""
});

const showUpdateModal = ref(false);
const showDeleteModal = ref(false);
const showDeleteErrorModal = ref(false);
const tipoToDelete = reactive({});
const deleteErrorMessage = ref("");

function resetForm() {
    form.reset();
    isEditing.value = false;
    Object.keys(currentTipo).forEach(key => delete currentTipo[key]);
}

function editTipo(tipo) {
    isEditing.value = true;
    Object.assign(currentTipo, tipo);
    form.nombre = tipo.nombre;
    form.aplica_a_todos_edificios = tipo.aplica_a_todos_edificios;
    form.id_condominio = tipo.id_condominio;
    form.id_categoria = tipo.id_categoria;
    form.aplica_prorrateo_condominio = tipo.aplica_prorrateo_condominio;
    form.tipo_prorrateo_id = tipo.tipo_prorrateo_id;
    form.consumo = tipo.consumo;
    form.medida = tipo.medida;
}

function submit() {
    if (isEditing.value && currentTipo.id_tipo_gasto) {
        showUpdateModal.value = true;
    } else {
        form.post(route("tipo_gasto_comun.store"), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => { resetForm(); },
        });
    }
}

function confirmUpdate() {
    form.put(route("tipo_gasto_comun.update", currentTipo.id_tipo_gasto), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { resetForm(); showUpdateModal.value = false; },
    });
}

function cancelUpdate() {
    showUpdateModal.value = false;
}

function confirmDelete(tipo) {
    if (tipo.unidades_servicio_extras_count && tipo.unidades_servicio_extras_count > 0) {
        deleteErrorMessage.value = "No se puede eliminar este servicio porque está relacionado con alguna unidad.";
        showDeleteErrorModal.value = true;
        return;
    }
    Object.assign(tipoToDelete, tipo);
    showDeleteModal.value = true;
}

function deleteTipo() {
    Inertia.delete(route("tipo_gasto_comun.destroy", tipoToDelete.id_tipo_gasto), {
        preserveState: true,
        preserveScroll: true,
    });
    showDeleteModal.value = false;
}

function closeDeleteErrorModal() {
    showDeleteErrorModal.value = false;
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
