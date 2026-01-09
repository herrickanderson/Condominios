<template>

    <Head title="Configuración de Montos de Servicios" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h2 class="text-xl font-semibold text-white">Configuración de Montos de Servicios</h2>
            </div>
        </template>

        <!-- Alertas Flash -->
        <div class="py-4 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <transition name="fade">
                <div v-if="flashSuccess"
                    class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">¡Éxito!</strong>
                    <span class="block sm:inline">{{ flashSuccess }}</span>
                </div>
            </transition>
            <transition name="fade">
                <div v-if="flashError"
                    class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">Error:</strong>
                    <span class="block sm:inline">{{ flashError }}</span>
                </div>
            </transition>
        </div>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- FORMULARIO CREAR O EDITAR -->
                <div class="bg-white shadow sm:rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-bold mb-4">
                        {{ editingMode ? "Editar Configuración" : "Agregar Configuración" }}
                    </h3>

                    <!-- Para Superadmin, seleccionar condominio -->
                    <div v-if="condominios.length > 0" class="mb-4">
                        <label class="block mb-1 font-semibold text-gray-700" for="condominioSelect">
                            Condominio
                        </label>
                        <select id="condominioSelect" v-model="form.id_condominio"
                            class="w-full border-gray-300 rounded shadow-sm">
                            <option disabled value="">Seleccione un condominio</option>
                            <option v-for="c in condominios" :key="c.id_condominio" :value="c.id_condominio">
                                {{ c.nombre }}
                            </option>
                        </select>
                        <InputError :message="form.errors.id_condominio" />
                    </div>

                    <!-- Seleccionar Tipo de Gasto (solo consumo=1) -->
                    <div class="mb-4">
                        <label class="block mb-1 font-semibold text-gray-700" for="tipoSelect">
                            Servicio (Tipo de Gasto)
                        </label>
                        <select id="tipoSelect" v-model="form.id_tipo_gasto"
                            class="w-full border-gray-300 rounded shadow-sm">
                            <option disabled value="">Seleccione un servicio</option>
                            <option v-for="t in tiposConsumo" :key="t.id_tipo_gasto" :value="t.id_tipo_gasto">
                                {{ t.nombre }} ({{ t.medida || 'unidad' }})
                            </option>
                        </select>
                        <InputError :message="form.errors.id_tipo_gasto" />
                    </div>

                    <!-- Precio en Soles y Dólares (ingreso manual) -->
                    <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 font-semibold text-gray-700" for="precioSoles">
                                Precio en Soles
                            </label>
                            <!-- Se usa type="text" para asegurar que se trabaje con strings -->
                            <TextInput id="precioSoles" type="text" placeholder="Ej: 0.89" v-model="form.precio"
                                class="w-full" />
                            <InputError :message="form.errors.precio" />
                        </div>
                        <div>
                            <label class="block mb-1 font-semibold text-gray-700" for="precioDolares">
                                Precio en Dólares
                            </label>
                            <TextInput id="precioDolares" type="text" placeholder="Ej: 0.22"
                                v-model="form.precio_dolares" class="w-full" />
                            <InputError :message="form.errors.precio_dolares" />
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center">
                        <PrimaryButton @click="onSubmit">
                            {{ editingMode ? 'Actualizar' : 'Agregar' }}
                        </PrimaryButton>
                        <button v-if="editingMode" class="ml-4 px-4 py-2 text-sm font-medium border rounded"
                            @click="cancelEdit">
                            Cancelar
                        </button>
                    </div>
                </div>

                <!-- LISTADO DE CONFIGURACIONES -->
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold mb-4">Lista de Configuraciones</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border border-gray-200">
                            <thead class="bg-gray-50 text-gray-700">
                                <tr>
                                    <th class="px-4 py-2">#ID</th>
                                    <th class="px-4 py-2">Servicio</th>
                                    <th class="px-4 py-2">Medida</th>
                                    <th class="px-4 py-2">Precio (Soles)</th>
                                    <th class="px-4 py-2">Precio (Dólares)</th>
                                    <th class="px-4 py-2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cfg in configuraciones.data" :key="cfg.id_config" class="border-b">
                                    <td class="px-4 py-2">{{ cfg.id_config }}</td>
                                    <td class="px-4 py-2">
                                        {{ cfg.tipo_gasto ? cfg.tipo_gasto.nombre : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ cfg.tipo_gasto ? (cfg.tipo_gasto.medida || '-') : '-' }}
                                    </td>
                                    <td class="px-4 py-2">{{ cfg.precio }}</td>
                                    <td class="px-4 py-2">{{ cfg.precio_dolares }}</td>
                                    <td class="px-4 py-2 space-x-2">
                                        <button @click="startEdit(cfg)" class="text-blue-600 hover:underline">
                                            Editar
                                        </button>
                                        <button @click="confirmDelete(cfg)" class="text-red-600 hover:underline">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="configuraciones.data.length === 0">
                                    <td colspan="6" class="px-4 py-2 text-center">
                                        No hay configuraciones registradas.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-4">
                        <Pagination :links="configuraciones.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL ELIMINAR -->
        <div v-if="showDeleteModal"
            class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-50">
            <div class="bg-white rounded-md p-6 w-96">
                <h3 class="text-xl font-bold mb-4">Confirmar Eliminación</h3>
                <p class="mb-4">
                    ¿Desea eliminar la configuración #{{ toDelete?.id_config }}?
                </p>
                <div class="flex justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 mr-2 border rounded">
                        Cancelar
                    </button>
                    <button @click="deleteConfig" class="px-4 py-2 bg-red-600 text-white rounded">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { usePage, useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    configuraciones: Object,
    tiposConsumo: Array,
    condominios: Array,
});

// Mensajes Flash
const { props: pageProps } = usePage();
const flashSuccess = ref(pageProps.flash.success || "");
const flashError = ref(pageProps.flash.error || "");

// Modo edición
const editingMode = ref(false);

// Formulario (se inicializa con cadenas para evitar warnings de tipo)
const form = useForm({
    id_config: null,
    id_condominio: props.condominios.length > 0 ? "" : "",
    id_tipo_gasto: "",
    precio: "",
    precio_dolares: "",
});

// Validación front (campos no vacíos)
function validateForm() {
    if (!form.id_tipo_gasto || form.precio === "" || form.precio_dolares === "" || (props.condominios.length > 0 && !form.id_condominio)) {
        flashError.value = "Por favor, complete todos los campos correctamente.";
        return false;
    }
    flashError.value = "";
    return true;
}

// Crear / Actualizar
function onSubmit() {
    if (!validateForm()) return;

    if (editingMode.value && form.id_config) {
        form.put(route("config_servicios.update", form.id_config), {
            onSuccess: () => {
                cancelEdit();
            }
        });
    } else {
        form.post(route("config_servicios.store"), {
            onSuccess: () => {
                form.reset();
            }
        });
    }
}

function startEdit(cfg) {
    editingMode.value = true;
    form.id_config = cfg.id_config;
    form.id_condominio = cfg.id_condominio;
    form.id_tipo_gasto = cfg.id_tipo_gasto;
    form.precio = String(cfg.precio);
    form.precio_dolares = String(cfg.precio_dolares);
}

function cancelEdit() {
    editingMode.value = false;
    form.reset();
    form.clearErrors();
    form.id_config = null;
}

const showDeleteModal = ref(false);
const toDelete = reactive({});

function confirmDelete(cfg) {
    toDelete.id_config = cfg.id_config;
    showDeleteModal.value = true;
}

function deleteConfig() {
    form.delete(route("config_servicios.destroy", toDelete.id_config), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
        onError: () => {
            showDeleteModal.value = false;
        }
    });
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
