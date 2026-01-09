<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// Importar íconos de Heroicons (24/solid)
import {
  HomeIcon,
  BuildingOffice2Icon,
  ArrowsPointingOutIcon,
  AdjustmentsVerticalIcon,
  ViewColumnsIcon,
  PlusIcon,
  UserGroupIcon
} from '@heroicons/vue/24/solid';

/**
 * Props:
 * - unidad: { id_unidad, nombre_unidad, tipo_unidad, area, unidad_medida, id_edificio, servicios_extras: [ { id_tipo_gasto, porcentaje_extra, tipo_gasto: { nombre } }, ... ], propietario: { name } }
 * - edificios: [ { id, nombre }, ... ]
 * - servicios: [ { id_tipo_gasto, nombre }, ... ]
 */
const props = defineProps({
    unidad: Object,
    edificios: Array,
    servicios: Array,
});

/**
 * Formulario con datos iniciales de la unidad
 */
const form = useForm({
    nombre_unidad: props.unidad.nombre_unidad,
    id_edificio: props.unidad.id_edificio,
    area: props.unidad.area,
    unidad_medida: props.unidad.unidad_medida,
    tipo_unidad: props.unidad.tipo_unidad, // 'departamento' o 'negocio'
    // Convertir los servicios_extras en un array con { id_tipo_gasto, porcentaje_extra }
    servicios_extra: props.unidad.servicios_extras
        ? props.unidad.servicios_extras.map(se => ({
            id_tipo_gasto: se.id_tipo_gasto,
            porcentaje_extra: se.porcentaje_extra,
        }))
        : [],
});

/**
 * Variables para el sub-formulario de agregar un servicio
 */
const newServiceId = ref('');
const newPercentage = ref('');
const errorMsg = ref('');

/**
 * Agregar servicio
 */
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

/**
 * Quitar un servicio de la lista
 */
function removeService(index) {
    form.servicios_extra.splice(index, 1);
}

/**
 * Obtener el nombre de un servicio dado su ID
 */
function getServiceName(id) {
    const s = props.servicios.find(x => x.id_tipo_gasto == id);
    return s ? s.nombre : 'Desconocido';
}

/**
 * Enviar formulario al controlador para actualizar
 */
function submit() {
    form.put(route('unidades.update', props.unidad.id_unidad), {
        onSuccess: () => {
            // Puedes agregar acciones post actualización si es necesario
        },
    });
}
</script>

<template>
  <Head title="Editar Unidad" />
  <AuthenticatedLayout>
    <!-- Encabezado -->
    <template #header>
      <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
        <h3 class="text-xl font-semibold text-white">Estas en: Unidades / Editar</h3>
        <Link :href="route('unidades.index')" class="text-white hover:underline font-semibold">
          <-Volver al listado
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200">
        <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">Editar Unidad</h3>

        <!-- Información del usuario asignado, si existe -->
        <div class="mb-4 text-center">
          <span class="font-semibold">Usuario asignado: </span>
          <span>{{ unidad.propietario ? unidad.propietario.name : 'No asignado' }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Nombre de la Unidad -->
          <div>
            <InputLabel for="nombre_unidad" value="Nombre de la Unidad" />
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <HomeIcon class="h-5 w-5 text-gray-400" />
              </div>
              <TextInput id="nombre_unidad" type="text" v-model="form.nombre_unidad" required class="w-full pl-10" />
            </div>
            <InputError :message="form.errors.nombre_unidad" />
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

          <!-- Tipo de Unidad -->
          <div>
            <InputLabel for="tipo_unidad" value="Tipo de Unidad" />
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <ViewColumnsIcon class="h-5 w-5 text-gray-400" />
              </div>
              <select id="tipo_unidad" v-model="form.tipo_unidad" class="w-full border-gray-300 rounded-md shadow-sm pl-10">
                <option value="departamento">Departamento</option>
                <option value="negocio">Negocio</option>
              </select>
            </div>
            <InputError :message="form.errors.tipo_unidad" />
          </div>

          <!-- Sección de servicios extras (solo si es negocio) -->
          <div v-if="form.tipo_unidad === 'negocio'">
            <h4 class="text-lg font-semibold text-gray-600 mb-2">Servicios Adicionales</h4>
            <!-- Sub-formulario: Seleccionar servicio + porcentaje -->
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
            <!-- Tabla de servicios agregados -->
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
                    <button type="button" class="text-red-600 underline" @click="removeService(idx)">
                      Quitar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
            <InputError :message="form.errors['servicios_extra']" />
          </div>

          <!-- Botón de envío -->
          <div class="flex justify-center">
            <PrimaryButton class="flex items-center space-x-1">
              <PlusIcon class="h-5 w-5" />
              <span>Actualizar Unidad</span>
            </PrimaryButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Puedes agregar estilos adicionales si los requieres */
</style>
