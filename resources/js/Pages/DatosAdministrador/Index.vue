<template>

    <Head title="Configuración de Pagos" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h2 class="text-xl font-semibold text-white">Configuración de Pagos Bancarios</h2>
            </div>
        </template>

        <div class="py-10 max-w-7xl mx-auto space-y-8">
            <!-- Formulario -->
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">Agregar Nueva Configuración</h3>
                <!-- Si es superadmin, se muestra el select para elegir condominio en el formulario -->
                <div v-if="condominios && condominios.length > 0" class="mb-4">
                    <InputLabel value="Condominio" />
                    <select v-model="form.id_condominio" class="w-full border-gray-300 rounded">
                        <option disabled value="">Seleccione un condominio</option>
                        <option v-for="c in condominios" :key="c.id_condominio" :value="c.id_condominio">
                            {{ c.nombre }}
                        </option>
                    </select>
                    <InputError :message="form.errors.id_condominio" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Banco" />
                        <select v-model="form.banco" class="w-full border-gray-300 rounded">
                            <option disabled value="">Seleccione un banco</option>
                            <option v-for="bank in banksPeru" :key="bank">{{ bank }}</option>
                        </select>
                        <InputError :message="form.errors.banco" />
                    </div>

                    <div>
                        <InputLabel value="Tipo de Cuenta" />
                        <TextInput v-model="form.tipo_cuenta" class="w-full" placeholder="Ej: Ahorros" />
                        <InputError :message="form.errors.tipo_cuenta" />
                    </div>

                    <div>
                        <InputLabel value="Número de Cuenta" />
                        <TextInput v-model="form.numero_cuenta" class="w-full" />
                        <InputError :message="form.errors.numero_cuenta" />
                    </div>

                    <div>
                        <InputLabel value="CCI" />
                        <TextInput v-model="form.cci" class="w-full" />
                        <InputError :message="form.errors.cci" />
                    </div>

                    <div>
                        <InputLabel value="Nombre del Propietario" />
                        <TextInput v-model="form.propietario" class="w-full" />
                        <InputError :message="form.errors.propietario" />
                    </div>

                    <div>
                        <InputLabel value="Teléfono" />
                        <TextInput v-model="form.telefono" class="w-full" />
                        <InputError :message="form.errors.telefono" />
                    </div>

                    <div class="md:col-span-2">
                        <InputLabel value="Dirección" />
                        <TextInput v-model="form.direccion" class="w-full" />
                        <InputError :message="form.errors.direccion" />
                    </div>

                    <div class="md:col-span-2">
                        <InputLabel value="Observaciones (opcional)" />
                        <textarea v-model="form.observaciones" rows="3"
                            class="w-full border-gray-300 rounded-md"></textarea>
                        <InputError :message="form.errors.observaciones" />
                    </div>

                    <div>
                        <InputLabel value="Imagen QR (Yape/Plin/etc)" />
                        <FileInput @change="onFileChange" class="w-full" />
                        <InputError :message="form.errors.qr" />
                        <img v-if="qrPreview" :src="qrPreview" class="h-24 mt-2 rounded" />
                    </div>
                </div>

                <div class="mt-6 flex gap-4">
                    <PrimaryButton @click="openConfirmModal">
                        {{ editing ? 'Actualizar' : 'Guardar' }}
                    </PrimaryButton>
                    <button v-if="editing" @click="cancelEdit" class="px-4 py-2 border rounded text-gray-600">
                        Cancelar
                    </button>
                </div>
            </div>

            <!-- FILTRO POR CONDOMINIO (solo para superadmin) -->
            <div v-if="condominios && condominios.length > 0" class="mb-4">
                <InputLabel value="Filtrar por Condominio" />
                <select v-model="condominioFilter" class="w-full border-gray-300 rounded">
                    <option value="">Todos</option>
                    <option v-for="c in condominios" :key="c.id_condominio" :value="c.id_condominio">
                        {{ c.nombre }}
                    </option>
                </select>
            </div>

            <!-- Lista -->
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">Configuraciones Guardadas</h3>
                <table class="w-full text-sm text-left border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2">Banco</th>
                            <th class="px-4 py-2">Cuenta</th>
                            <th class="px-4 py-2">CCI</th>
                            <th class="px-4 py-2">Propietario</th>
                            <th class="px-4 py-2">QR</th>
                            <th class="px-4 py-2">Activo</th>
                            <th class="px-4 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cfg in configsFiltrados" :key="cfg.id" class="border-t">
                            <td class="px-4 py-2 flex items-center gap-2">
                                <BanknotesIcon class="h-5 w-5 text-green-600" /> {{ cfg.banco }}
                            </td>
                            <td class="px-4 py-2">{{ cfg.numero_cuenta }}</td>
                            <td class="px-4 py-2">{{ cfg.cci }}</td>
                            <td class="px-4 py-2 flex items-center gap-2">
                                <UserIcon class="h-5 w-5 text-blue-600" /> {{ cfg.propietario }}
                            </td>
                            <td class="px-4 py-2">
                                <img v-if="cfg.qr_path" :src="getImageUrl(cfg.qr_path)" class="h-10 rounded border" />
                            </td>
                            <td class="px-4 py-2">
                                <span v-if="cfg.activo" class="text-green-600 font-bold flex items-center gap-1">
                                    <CheckCircleIcon class="h-5 w-5" /> Activo
                                </span>
                                <button v-else class="text-blue-600 underline text-sm" @click="activar(cfg.id)">
                                    Activar
                                </button>
                            </td>
                            <td class="px-4 py-2 flex gap-2">
                                <button @click="startEdit(cfg)" title="Editar">
                                    <PencilSquareIcon class="h-5 w-5 text-indigo-500" />
                                </button>
                                <button @click="eliminar(cfg.id)" title="Eliminar">
                                    <TrashIcon class="h-5 w-5 text-red-600" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="configsFiltrados.length === 0">
                            <td colspan="7" class="text-center py-4 text-gray-500">
                                No hay configuraciones registradas
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal de confirmación -->
        <div v-if="showConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-96">
                <h3 class="text-lg font-bold mb-4 text-center">¿Confirmar datos ingresados?</h3>
                <p class="text-center text-sm text-gray-600">Se guardará la información ingresada.</p>
                <div class="flex justify-around mt-6">
                    <PrimaryButton @click="confirmSubmit">Sí, guardar</PrimaryButton>
                    <button @click="showConfirmModal = false" class="px-4 py-2 text-sm border rounded text-gray-600">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import FileInput from '@/Components/FileInput.vue';
import Pagination from '@/Components/Pagination.vue';
import { CheckCircleIcon, BanknotesIcon, UserIcon, TrashIcon, PencilSquareIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    configs: Array,
    awsBaseUrl: String,
    condominios: {
        type: Array,
        default: () => []
    },
    selectedCondominio: {
        type: [String, Number, null],
        default: null
    },
});

// Formulario para crear/editar
const form = useForm({
    banco: '',
    tipo_cuenta: '',
    numero_cuenta: '',
    cci: '',
    propietario: '',
    telefono: '',
    direccion: '',
    observaciones: '',
    qr: null,
});

const banksPeru = [
    "BCP", "Interbank", "BBVA", "Scotiabank", "Banco de la Nación", "Banbif", "Banco Ripley", "Banco Falabella", "MiBanco"
];

const editing = ref(false);
const editingId = ref(null);
const showConfirmModal = ref(false);
const qrPreview = ref(null);

// Filtro extra para superadmin
const condominioFilter = ref(props.selectedCondominio || "");

// Actualiza la lista mediante Inertia al cambiar el filtro
watch(condominioFilter, (newVal) => {
    Inertia.get(route("DatosAdministrador.index"), { condominio_id: newVal }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
});

// Función para leer el archivo y mostrar preview
function onFileChange(e) {
    const file = e.target.files[0];
    form.qr = file;
    if (file) {
        const reader = new FileReader();
        reader.onload = () => {
            qrPreview.value = reader.result;
        };
        reader.readAsDataURL(file);
    }
}

function openConfirmModal() {
    if (!form.banco || !form.tipo_cuenta || !form.numero_cuenta || !form.propietario) {
        alert('Por favor completa los campos obligatorios.');
        return;
    }
    showConfirmModal.value = true;
}

function confirmSubmit() {
    showConfirmModal.value = false;

    const options = {
        onSuccess: () => {
            form.reset();
            qrPreview.value = null;
            editing.value = false;
            editingId.value = null;
            // Recargar para traer los datos nuevos
            window.location.reload();
        }
    };

    if (editing.value && editingId.value) {
        form.post(route('DatosAdministrador.update', editingId.value), options);
    } else {
        form.post(route('DatosAdministrador.store'), options);
    }
}

function cancelEdit() {
    form.reset();
    editing.value = false;
    editingId.value = null;
    qrPreview.value = null;
}

function startEdit(cfg) {
    editing.value = true;
    editingId.value = cfg.id;
    form.banco = cfg.banco;
    form.tipo_cuenta = cfg.tipo_cuenta;
    form.numero_cuenta = cfg.numero_cuenta;
    form.cci = cfg.cci;
    form.propietario = cfg.propietario;
    form.telefono = cfg.telefono;
    form.direccion = cfg.direccion;
    form.observaciones = cfg.observaciones;
    qrPreview.value = cfg.qr_path ? getImageUrl(cfg.qr_path) : null;
}

function activar(id) {
    form.post(route('DatosAdministrador.toggle', id));
}

function eliminar(id) {
    if (confirm('¿Estás seguro de eliminar esta configuración?')) {
        form.delete(route('DatosAdministrador.destroy', id));
    }
}

const getImageUrl = (path) => {
    return path ? `${props.awsBaseUrl}/${path}` : null;
};

// Computed para filtrar localmente las configuraciones según el condominio (solo si es superadmin)
const configsFiltrados = computed(() => {
    if (condominioFilter.value === "") {
        return props.configs;
    }
    return props.configs.filter(cfg => String(cfg.id_condominio) === String(condominioFilter.value));
});
</script>