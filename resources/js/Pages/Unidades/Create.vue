<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

// Íconos importados de Heroicons (24/solid)
import { HomeIcon, BuildingOffice2Icon, ArrowsPointingOutIcon, AdjustmentsVerticalIcon, ViewColumnsIcon, PlusIcon, UserGroupIcon } from '@heroicons/vue/24/solid';

/**
 * Props recibidas desde el controlador:
 * - edificios: [{ id, nombre }, ...]
 * - condominios: [{ id, nombre }, ...] (si eres superadmin)
 * - servicios: [{ id_tipo_gasto, nombre }, ...]
 */
const props = defineProps({
    edificios: {
        type: Array,
        default: () => [],
    },
    condominios: {
        type: Array,
        default: () => [],
    },
    servicios: {
        type: Array,
        default: () => [],
    },
});

// Extraemos auth de las props globales
const { auth } = usePage().props;

/**
 * Formulario principal de la Unidad
 */
const form = useForm({
    nombre_unidad: '',
    id_edificio: '',
    area: '',
    unidad_medida: 'mt2',
    // Si el usuario actual tiene un condominio asignado, lo fijamos
    id_condominio: auth.user && auth.user.id_condominio ? auth.user.id_condominio : '',
    tipo_unidad: 'departamento', // "departamento" o "negocio"
    // Lista de servicios extra si es "negocio"
    servicios_extra: [],
});

/**
 * Validación del área: debe ser un número y mayor que 0 (no se permiten valores negativos ni 0)
 */
const areaError = ref('');
watch(() => form.area, (newVal) => {
    const areaVal = parseFloat(newVal);
    if (isNaN(areaVal) || areaVal <= 0) {
        areaError.value = "El área debe ser un número válido y mayor que 0";
    } else {
        areaError.value = "";
    }
});
const areaValidationError = computed(() => areaError.value || form.errors.area);

/**
 * Variables para el sub-formulario de servicios extras
 */
const newServiceId = ref('');       // ID del servicio seleccionado
const newPercentage = ref('');      // Porcentaje a aplicar
const errorMsg = ref('');           // Para mostrar errores simples en la sección de servicios

/**
 * Añadir un servicio extra a la tabla temporal
 */
function addService() {
    if (!newServiceId.value) {
        errorMsg.value = 'Seleccione un servicio';
        return;
    }
    const percentageVal = parseFloat(newPercentage.value);
    if (isNaN(percentageVal) || percentageVal < 0) {
        errorMsg.value = 'Ingrese un porcentaje válido (≥ 0)';
        return;
    }
    const exists = form.servicios_extra.find(s => s.id_tipo_gasto === newServiceId.value);
    if (exists) {
        errorMsg.value = 'Este servicio ya fue agregado.';
        return;
    }
    form.servicios_extra.push({
        id_tipo_gasto: newServiceId.value,
        porcentaje_extra: percentageVal,
    });
    newServiceId.value = '';
    newPercentage.value = '';
    errorMsg.value = '';
}

/**
 * Eliminar un servicio de la tabla temporal
 */
function removeService(index) {
    form.servicios_extra.splice(index, 1);
}

/**
 * Devuelve el nombre de un servicio dado su ID
 */
function getServiceName(id) {
    const serv = props.servicios.find(s => s.id_tipo_gasto == id);
    return serv ? serv.nombre : 'Desconocido';
}

/**
 * Enviar formulario al servidor
 */
function submit() {
    const areaVal = parseFloat(form.area);
    if (isNaN(areaVal) || areaVal <= 0) {
        areaError.value = "El área debe ser un número válido y mayor que 0";
        return;
    }
    form.post(route('unidades.store'), {
        onSuccess: () => {
            form.reset('nombre_unidad', 'area', 'servicios_extra');
        },
    });
}
</script>

<template>

    <Head title="Crear Unidad" />
    <AuthenticatedLayout>
        <!-- Encabezado -->
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Estas en: Unidad / Crear</h3>
                <Link :href="route('unidades.index')" class="text-white hover:underline font-semibold">
                <-Listar Unidades
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200">
                <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">
                    Registrar Unidad
                </h3>

                <!-- Formulario principal -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Nombre de la Unidad con ícono -->
                    <div>
                        <InputLabel for="nombre_unidad" value="Nombre de la Unidad" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <HomeIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <TextInput id="nombre_unidad" type="text" v-model="form.nombre_unidad" required
                                class="w-full pl-10" />
                        </div>
                        <InputError :message="form.errors.nombre_unidad" />
                    </div>

                    <!-- Edificio con ícono -->
                    <div>
                        <InputLabel for="id_edificio" value="Selecciona el Edificio" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <BuildingOffice2Icon class="h-5 w-5 text-gray-400" />
                            </div>
                            <select id="id_edificio" v-model="form.id_edificio" required
                                class="w-full border-gray-300 rounded-md shadow-sm pl-10">
                                <option disabled value="">Seleccione un edificio</option>
                                <option v-for="edificio in props.edificios" :key="edificio.id" :value="edificio.id">
                                    {{ edificio.nombre }}
                                </option>
                            </select>
                        </div>
                        <InputError :message="form.errors.id_edificio" />
                        <!-- Si no hay edificios, mostramos el link para crearlos -->
                        <p class="text-sm text-gray-600">
                                No hay tipos Torres / Zonas creadas.
                            </p>
                        <div v-if="props.edificios.length === 0" class="mt-2">
                            <DropdownLink :href="route('edificios.create')"
                                class="bg-blue-500 text-white font-bold py-2 px-4 rounded inline-block border border-transparent hover:bg-white hover:text-blue-500 hover:border-blue-500">
                                Torres / Zonas
                            </DropdownLink>
                        </div>

                    </div>

                    <!-- Área con ícono -->
                    <div>
                        <InputLabel for="area" value="Área (m²)" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <ArrowsPointingOutIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <!-- Se agrega el atributo min para evitar números negativos -->
                            <TextInput id="area" type="number" step="0.01" :min="0.01" v-model="form.area" required
                                class="w-full pl-10" placeholder="Ej: 75.50" />
                        </div>
                        <InputError :message="areaValidationError" />
                    </div>

                    <!-- Unidad de Medida con ícono -->
                    <div>
                        <InputLabel for="unidad_medida" value="Unidad de Medida" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <AdjustmentsVerticalIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <TextInput id="unidad_medida" type="text" v-model="form.unidad_medida" readonly required
                                class="w-full bg-gray-100 cursor-not-allowed pl-10" />
                        </div>
                        <InputError :message="form.errors.unidad_medida" />
                    </div>

                    <!-- Tipo de Unidad con ícono -->
                    <div>
                        <InputLabel for="tipo_unidad" value="Tipo de Unidad" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <ViewColumnsIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <select id="tipo_unidad" v-model="form.tipo_unidad"
                                class="w-full border-gray-300 rounded-md shadow-sm pl-10">
                                <option value="departamento">Departamento</option>
                                <option value="negocio">Negocio</option>
                            </select>
                        </div>
                        <InputError :message="form.errors.tipo_unidad" />
                    </div>

                    <!-- Condominio (solo si el user no tiene uno fijo) con ícono -->
                    <div v-if="!auth.user.id_condominio">
                        <InputLabel for="id_condominio" value="Condominio" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <UserGroupIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <select v-model="form.id_condominio" id="id_condominio" required
                                class="w-full border-gray-300 rounded-md pl-10">
                                <option disabled value="">Seleccione un condominio</option>
                                <option v-for="cond in props.condominios" :key="cond.id" :value="cond.id">
                                    {{ cond.nombre }}
                                </option>
                            </select>
                        </div>
                        <InputError :message="form.errors.id_condominio" />
                    </div>

                    <!-- Sección de servicios extras para "negocio" -->
                    <div v-if="form.tipo_unidad === 'negocio'">
                        <h4 class="text-lg font-semibold text-gray-600 mb-2">Servicios Adicionales</h4>
                        <!-- Si no hay servicios agregados, se muestra el link para ir a Tipo Gasto Común -->
                        <div v-if="props.servicios.length === 0" class="mb-4">
                            <p class="text-sm text-gray-600">
                                No hay tipos de servicios agregados.
                            </p>
                            <div class="mt-2">
                                <DropdownLink :href="route('tipo_gasto_comun.index')"
                                    class="bg-blue-500 text-white font-bold py-2 px-4 rounded inline-block border border-transparent hover:bg-white hover:text-blue-500 hover:border-blue-500">
                                    Tipo Gasto Común
                                </DropdownLink>
                            </div>

                        </div>
                        <!-- Si existen servicios, se muestra el sub-formulario para agregarlos -->
                        <div v-else>
                            <div class="flex items-end space-x-4 mb-2">
                                <div class="flex-1 relative">
                                    <InputLabel value="Servicio" />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <BuildingOffice2Icon class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <select v-model="newServiceId" class="border-gray-300 rounded-md w-full pl-10">
                                        <option value="">Seleccione un servicio</option>
                                        <option v-for="serv in props.servicios" :key="serv.id_tipo_gasto"
                                            :value="serv.id_tipo_gasto">
                                            {{ serv.nombre }}
                                        </option>
                                    </select>
                                </div>
                                <div class="w-32 relative">
                                    <InputLabel value="Porcentaje" />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <ArrowsPointingOutIcon class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <TextInput type="number" step="0.01" v-model="newPercentage" class="w-full pl-10" />
                                </div>
                                <div>
                                    <PrimaryButton type="button" @click="addService"
                                        class="flex items-center space-x-1">
                                        <PlusIcon class="h-5 w-5" />
                                        <span>Agregar</span>
                                    </PrimaryButton>
                                </div>
                            </div>
                            <!-- Mensaje de error simple -->
                            <div v-if="errorMsg" class="text-red-600 text-sm mb-2">
                                {{ errorMsg }}
                            </div>
                            <!-- Tabla de servicios añadidos -->
                            <table class="mt-2 w-full border text-sm">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-2 py-1 text-left">Servicio</th>
                                        <th class="px-2 py-1 text-left">Porcentaje</th>
                                        <th class="px-2 py-1 text-left">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, idx) in form.servicios_extra" :key="item.id_tipo_gasto">
                                        <td class="px-2 py-1">
                                            {{ getServiceName(item.id_tipo_gasto) }}
                                        </td>
                                        <td class="px-2 py-1">
                                            {{ item.porcentaje_extra }} %
                                        </td>
                                        <td class="px-2 py-1">
                                            <button type="button" class="text-red-600 underline"
                                                @click="removeService(idx)">
                                                Quitar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <InputError :message="form.errors['servicios_extra']" />
                        </div>
                    </div>

                    <!-- Botón de enviar -->
                    <div class="flex justify-center">
                        <PrimaryButton class="flex items-center space-x-1">
                            <PlusIcon class="h-5 w-5" />
                            <span>Crear Unidad</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Puedes agregar estilos adicionales si lo requieres */
</style>
