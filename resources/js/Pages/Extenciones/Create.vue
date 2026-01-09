<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { HomeIcon, BuildingOffice2Icon, ArrowsPointingOutIcon, AdjustmentsVerticalIcon, ViewColumnsIcon, PlusIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    edificios: Array,
    condominios: Array,
    servicios: Array,
});

const { auth } = usePage().props;

const form = useForm({
    nombre: '',
    id_edificio: '',
    area: '',
    unidad_medida: 'mt2',
    id_condominio: auth.user && auth.user.id_condominio ? auth.user.id_condominio : '',
    tipo_extencion: 'Estacionamiento', // Opciones: "Estacionamiento" o "Bodega"
    cobro_unico: '0', // Para Estacionamiento; "0" o "1"
    servicios_extra: [] // Array dinámico para servicios extras (mismos para ambos tipos)
});

const newServiceId = ref('');
const newPercentage = ref('');
const errorMsg = ref('');

function addService() {
    if (!newServiceId.value) {
        errorMsg.value = 'Seleccione un servicio';
        return;
    }
    const perc = parseFloat(newPercentage.value);
    if (isNaN(perc) || perc < 0) {
        errorMsg.value = 'Porcentaje inválido (≥ 0)';
        return;
    }
    const exists = form.servicios_extra.find(s => s.id_tipo_gasto == newServiceId.value);
    if (exists) {
        errorMsg.value = 'Este servicio ya está agregado.';
        return;
    }
    form.servicios_extra.push({
        id_tipo_gasto: newServiceId.value,
        porcentaje_extra: perc,
    });
    newServiceId.value = '';
    newPercentage.value = '';
    errorMsg.value = '';
}

function removeService(index) {
    form.servicios_extra.splice(index, 1);
}

function getServiceName(id) {
    const s = props.servicios.find(x => x.id_tipo_gasto == id);
    return s ? s.nombre : 'Desconocido';
}

function submit() {
    form.post(route('extenciones.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
}
</script>

<template>
    <Head title="Crear Extención" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Estas en: Extenciones / Crear</h3>
                <Link :href="route('extenciones.index')" class="text-white hover:underline font-semibold">
                    <- Listar Extenciones
                </Link>
            </div>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200">
                <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">Registrar Extención</h3>
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Nombre -->
                    <div>
                        <InputLabel for="nombre" value="Nombre" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <HomeIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <TextInput id="nombre" type="text" v-model="form.nombre" required class="w-full pl-10" />
                        </div>
                        <InputError :message="form.errors.nombre" />
                    </div>
                    <!-- Edificio -->
                    <div>
                        <InputLabel for="id_edificio" value="Edificio" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <BuildingOffice2Icon class="h-5 w-5 text-gray-400" />
                            </div>
                            <select id="id_edificio" v-model="form.id_edificio" required class="w-full border-gray-300 rounded-md shadow-sm pl-10">
                                <option disabled value="">Seleccione un edificio</option>
                                <option v-for="ed in edificios" :key="ed.id" :value="ed.id">
                                    {{ ed.nombre }}
                                </option>
                            </select>
                        </div>
                        <InputError :message="form.errors.id_edificio" />
                    </div>
                    <!-- Área -->
                    <div>
                        <InputLabel for="area" value="Área (m²)" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <ArrowsPointingOutIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <TextInput id="area" type="number" step="0.01" v-model="form.area" required class="w-full pl-10" />
                        </div>
                        <InputError :message="form.errors.area" />
                    </div>
                    <!-- Unidad de Medida -->
                    <div>
                        <InputLabel for="unidad_medida" value="Unidad de Medida" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <AdjustmentsVerticalIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <TextInput id="unidad_medida" type="text" v-model="form.unidad_medida" readonly required class="w-full bg-gray-100 cursor-not-allowed pl-10" />
                        </div>
                        <InputError :message="form.errors.unidad_medida" />
                    </div>
                    <!-- Tipo de Extención -->
                    <div>
                        <InputLabel for="tipo_extencion" value="Tipo de Extención" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <ViewColumnsIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <select id="tipo_extencion" v-model="form.tipo_extencion" class="w-full border-gray-300 rounded-md shadow-sm pl-10">
                                <option value="Estacionamiento">Estacionamiento / Parqueo</option>
                                <option value="Bodega">Bodega</option>
                            </select>
                        </div>
                        <InputError :message="form.errors.tipo_extencion" />
                    </div>
                    <!-- Para Estacionamiento, campo de Cobro Único -->
                    <div v-if="form.tipo_extencion === 'Estacionamiento'">
                        <InputLabel for="cobro_unico" value="Cobro Único" />
                        <select id="cobro_unico" v-model="form.cobro_unico" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                        <InputError :message="form.errors.cobro_unico" />
                    </div>
                    <!-- Sección de Servicios Extra -->
                    <div>
                        <h4 class="text-lg font-semibold text-gray-600 mb-2">Servicios Adicionales</h4>
                        <!-- Si no existen servicios, mostramos el enlace para crear Tipo Gasto Común -->
                        <div v-if="servicios.length === 0" class="mb-4">
                            <p class="text-sm text-gray-600">
                                No hay tipos de servicios agregados.
                                <DropdownLink :href="route('tipo_gasto_comun.index')" class="bg-blue-500 text-white font-bold py-2 px-4 rounded inline-block border border-transparent hover:bg-white hover:text-blue-500 hover:border-blue-500">
                                    Tipo Gasto Común
                                </DropdownLink>
                            </p>
                        </div>
                        <!-- Si existen servicios, mostramos el sub-formulario para agregarlos -->
                        <div v-else>
                            <div class="flex items-end space-x-4 mb-2">
                                <div class="flex-1 relative">
                                    <InputLabel value="Servicio" />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <BuildingOffice2Icon class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <select v-model="newServiceId" class="border-gray-300 rounded-md w-full pl-10">
                                        <option value="">Seleccione un servicio</option>
                                        <option v-for="serv in servicios" :key="serv.id_tipo_gasto" :value="serv.id_tipo_gasto">
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
                                    <PrimaryButton type="button" @click="addService" class="flex items-center space-x-1">
                                        <PlusIcon class="h-5 w-5" />
                                        <span>Agregar</span>
                                    </PrimaryButton>
                                </div>
                            </div>
                            <div v-if="errorMsg" class="text-red-600 text-sm mb-2">
                                {{ errorMsg }}
                            </div>
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
                                        <td class="px-2 py-1">{{ getServiceName(item.id_tipo_gasto) }}</td>
                                        <td class="px-2 py-1">{{ item.porcentaje_extra }} %</td>
                                        <td class="px-2 py-1">
                                            <button type="button" class="text-red-600 underline" @click="removeService(idx)">
                                                Quitar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <InputError :message="form.errors['servicios_extra']" />
                        </div>
                    </div>
                    <div class="flex justify-center">
                        <PrimaryButton class="flex items-center space-x-1">
                            <PlusIcon class="h-5 w-5" />
                            <span>Crear Extención</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
