<script setup>
import { computed, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";

// Íconos de Heroicons (24/solid)
import {
  PencilSquareIcon,
  TrashIcon,
} from '@heroicons/vue/24/solid';

/**
 * Genera la URL completa de un archivo en S3.
 */
const awsBaseUrl = import.meta.env.VITE_AWS_URL;
function getFileUrl(path) {
  return path ? `${awsBaseUrl}/${path}` : null;
}

/**
 * Recibe la paginación y la lista de detalles como props.
 */
const props = defineProps({
  detalles: Object,
});

/**
 * Extrae la lista de detalles (array).
 */
const detallesData = computed(() => props.detalles?.data ?? []);

/**
 * Agrupa los detalles por id_gasto para mostrarlos en “bloques”.
 */
function groupByGasto(list) {
  const groups = {};
  for (const item of list) {
    const key = item.gastos_comune?.id_gasto ?? "sin_gasto";
    if (!groups[key]) {
      groups[key] = [];
    }
    groups[key].push(item);
  }
  return groups;
}
const groupedDetalles = computed(() => groupByGasto(detallesData.value));

/**
 * Maneja cuáles grupos están expandidos.
 * expandedGroups es un objeto { [idGasto]: boolean }
 */
const expandedGroups = ref({});

/**
 * Alterna el estado expandido/colapsado de un grupo dado.
 */
function toggleGroup(idGasto) {
  expandedGroups.value[idGasto] = !expandedGroups.value[idGasto];
}

/**
 * Devuelve texto amigable según el scope y las relaciones (torre/unidad).
 */
function getDistributionInfo(detalle) {
  if (detalle.distribution_scope === "condominium") {
    return "Todo el condominio";
  } else if (detalle.distribution_scope === "tower") {
    if (detalle.target_torre && detalle.target_torre.nombre) {
      return `Torre: ${detalle.target_torre.nombre}`;
    } else {
      return "Torre no asignada";
    }
  } else if (detalle.distribution_scope === "unit") {
    let info = "";
    if (detalle.target_torre && detalle.target_torre.nombre) {
      info += `Torre: ${detalle.target_torre.nombre}`;
    }
    if (detalle.target_unidad && detalle.target_unidad.nombre_unidad) {
      if (info) {
        info += " / ";
      }
      info += `Unidad: ${detalle.target_unidad.nombre_unidad}`;
    }
    return info || "Unidad no asignada";
  }
  return "";
}
</script>

<template>
  <Head title="Detalles de Gasto Común" />

  <AuthenticatedLayout>
    <!-- Encabezado de la página -->
    <template #header>
      <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
        <h3 class="text-xl font-semibold text-white">Detalles de Gasto Común</h3>
        <Link :href="route('detalle_gasto_comun.create')" class="text-white hover:underline font-semibold">
          Crear Detalle
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
          <!-- Mensaje de éxito, si existe -->
          <div v-if="$page.props.flash?.success" class="p-2 bg-green-100 text-green-700 rounded mb-4">
            {{ $page.props.flash.success }}
          </div>
          <div v-if="$page.props.flash?.error" class="p-2 bg-red-100 text-red-700 rounded mb-4">
            {{ $page.props.flash.error }}
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full table-auto divide-y divide-gray-200">
              <!-- Encabezados de columnas -->
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2">ID</th>
                  <th class="px-4 py-2">Tipo Gasto</th>
                  <th class="px-4 py-2">Monto</th>
                  <th class="px-4 py-2">Archivo</th>
                  <th class="px-4 py-2">Observación</th>
                  <th class="px-4 py-2">Ámbito</th>
                  <th class="px-4 py-2">Destino</th>
                  <th class="px-4 py-2">Descripción</th>
                  <th class="px-4 py-2">Acciones</th>
                </tr>
              </thead>

              <tbody>
                <!-- Recorremos cada grupo de detalles -->
                <template v-for="(group, idGasto) in groupedDetalles" :key="idGasto">
                  <!-- Fila que representa el “encabezado” de cada gasto -->
                  <tr class="bg-gray-100">
                    <td colspan="9" class="px-4 py-2 border-b">
                      <div class="flex items-center space-x-2 cursor-pointer" @click="toggleGroup(idGasto)">
                        <span class="text-gray-600 font-bold text-lg">
                          {{ expandedGroups[idGasto] ? '−' : '+' }}
                        </span>
                        <strong class="text-gray-800">
                          {{ group[0]?.gastos_comune?.descripcion ?? 'Sin gasto' }}
                        </strong>
                      </div>
                    </td>
                  </tr>

                  <!-- Fila(s) de detalle, se muestran si expandedGroups[idGasto] es true -->
                  <tr
                    v-if="expandedGroups[idGasto]"
                    v-for="detalle in group"
                    :key="detalle.id_detalle"
                    class="hover:bg-gray-50"
                  >
                    <td class="px-4 py-2">
                      {{ detalle.id_detalle }}
                    </td>
                    <td class="px-4 py-2">
                      {{ detalle.tipo_gasto_comun?.nombre ?? 'Sin tipo' }}
                    </td>
                    <td class="px-4 py-2">
                      {{ detalle.monto_detalle }}
                    </td>
                    <td class="px-4 py-2">
                      <div v-if="detalle.file_url">
                        <a :href="getFileUrl(detalle.file_url)" target="_blank" class="text-blue-600 hover:underline">
                          {{ detalle.nombre_file || 'Ver archivo' }}
                        </a>
                      </div>
                      <div v-else>
                        Sin archivo
                      </div>
                    </td>
                    <td class="px-4 py-2">
                      {{ detalle.observacion || 'N/A' }}
                    </td>
                    <td class="px-4 py-2">
                      <span>
                        {{ detalle.distribution_scope === 'condominium'
                          ? 'Condominio'
                          : detalle.distribution_scope === 'tower'
                            ? 'Torre'
                            : detalle.distribution_scope === 'unit'
                              ? 'Unidad'
                              : '' }}
                      </span>
                    </td>
                    <td class="px-4 py-2">
                      {{ getDistributionInfo(detalle) }}
                    </td>
                    <td class="px-4 py-2 whitespace-normal break-words max-w-sm">
                      {{ detalle.descripcion_detalle || 'N/A' }}
                    </td>
                    <td class="px-4 py-2">
                      <div class="flex space-x-2">
                        <!-- Si no tiene distribuciones, permitir Editar/Eliminar; si tiene, deshabilitar -->
                        <template v-if="detalle.distribucion_gastos_count === 0">
                          <Link
                            :href="route('detalle_gasto_comun.edit', detalle.id_detalle)"
                            class="text-blue-600 hover:underline inline-flex items-center space-x-1"
                          >
                            <PencilSquareIcon class="h-4 w-4" />
                            <span>Editar</span>
                          </Link>
                          <form @submit.prevent="$inertia.delete(route('detalle_gasto_comun.destroy', detalle.id_detalle))">
                            <button type="submit" class="text-red-600 hover:underline inline-flex items-center space-x-1">
                              <TrashIcon class="h-4 w-4" />
                              <span>Eliminar</span>
                            </button>
                          </form>
                        </template>
                        <template v-else>
                          <span class="text-gray-400 text-xs">No se puede editar/eliminar (distribuido)</span>
                        </template>
                      </div>
                    </td>
                  </tr>
                </template>

                <!-- Mensaje si no hay detalles -->
                <tr v-if="detallesData.length === 0">
                  <td colspan="9" class="px-6 py-4 text-center">
                    No hay detalles registrados.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div class="mt-4">
            <Pagination :links="props.detalles.links" />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Para el cursor pointer en la fila del encabezado de grupo */
.bg-gray-100:hover {
  cursor: pointer;
}
</style>
