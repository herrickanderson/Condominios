<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DashboardChart from '@/Components/DashboardChartPropietario.vue';

const page = usePage();
const arrendatario = page.props.arrendatario;
const pendientes = page.props.pendientes ?? [];

// Data para el gráfico
const chartData = ref([]);

// Al montar, consultamos la ruta "propietario.arrendatarioChart"
// y guardamos la respuesta en chartData
onMounted(() => {
  axios
    .get(route('propietario.arrendatarioChart'))
    .then((response) => {
      //console.log('Chart data for arrendatario:', response.data);
      chartData.value = response.data.chartData; 
    })
    .catch((error) => {
      //console.error('Error loading chart data:', error);
    });
});

// Agrupar pendientes para la tabla
function groupByGasto(list) {
  const groups = {};
  for (const item of list) {
    const gasto = item.detalle_gasto_comun.gastos_comune;
    const key = gasto ? gasto.id_gasto : 'sin_gasto';
    if (!groups[key]) {
      groups[key] = [];
    }
    groups[key].push(item);
  }
  return groups;
}

const groupedPendientes = computed(() => groupByGasto(pendientes));
const expandedGroups = ref({});

function toggleGroup(idGasto) {
  expandedGroups.value[idGasto] = !expandedGroups.value[idGasto];
}

function formatDate(dateStr) {
  if (!dateStr) return 'N/A';
  const d = new Date(dateStr);
  return isNaN(d) ? 'N/A' : d.toLocaleDateString();
}
</script>

<template>
  <Head title="Propietario - Dashboard" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
        <h3 class="text-xl font-semibold text-white">Dashboard Propietario</h3>
      </div>
    </template>

    <div class="py-8 px-4 max-w-7xl mx-auto">
      <!-- Dos columnas: Izquierda = gráfico, Derecha = info + pendientes -->
      <div class="flex flex-col md:flex-row gap-6">
        
        <!-- Columna Izquierda: Mini-dashboard (gráfico) -->
        <div class="w-full md:w-1/2 bg-white p-6 rounded shadow">
          <h2 class="text-2xl font-bold mb-4">Resumen de Gastos (Últimos 6 Meses)</h2>
          <div v-if="chartData.length === 0" class="text-gray-600">
            No hay datos para graficar.
          </div>
          <div v-else>
            <DashboardChart :gastosPorMes="chartData" />
          </div>
        </div>

        <!-- Columna Derecha: Info arrendatario y tabla de pendientes -->
        <div class="w-full md:w-1/2 bg-white p-6 rounded shadow">
          <!-- Info arrendatario -->
          <h2 class="text-2xl font-bold mb-4">Información del Arrendatario</h2>
          <div v-if="!arrendatario" class="text-gray-600">
            No hay arrendatario asignado a tu unidad.
          </div>
          <div v-else>
            <p class="mb-2">
              <strong>Nombre: </strong>{{ arrendatario.name }} {{ arrendatario.apellidos }}
            </p>
            <p class="mb-2">
              <strong>Email: </strong>{{ arrendatario.email }}
            </p>
            <p class="mb-2">
              <strong>Teléfono: </strong>{{ arrendatario.telefono }}
            </p>
          </div>

          <!-- Tabla de pendientes -->
          <div class="mt-6">
            <h2 class="text-2xl font-bold mb-4">Gastos Pendientes del Arrendatario</h2>
            <div v-if="!arrendatario" class="text-gray-600">
              No hay arrendatario, no se pueden mostrar pendientes.
            </div>
            <div v-else-if="pendientes.length === 0" class="text-gray-600">
              El arrendatario no tiene pendientes de pago.
            </div>
            <div v-else class="overflow-x-auto">
              <table class="min-w-full text-sm text-gray-700">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2">Descripción Gasto</th>
                    <th class="px-4 py-2">Tipo Gasto</th>
                    <th class="px-4 py-2">Fecha Venc.</th>
                    <th class="px-4 py-2">Monto</th>
                  </tr>
                </thead>
                <tbody>
                  <template v-for="(group, idGasto) in groupedPendientes" :key="idGasto">
                    <tr @click="toggleGroup(idGasto)" class="cursor-pointer bg-gray-100 border-b">
                      <td class="px-4 py-2">
                        {{ group[0]?.detalle_gasto_comun.gastos_comune?.descripcion ?? 'Sin gasto' }}
                      </td>
                      <td class="px-4 py-2">
                        {{ group[0]?.detalle_gasto_comun.tipo_gasto_comun?.nombre ?? 'N/A' }}
                      </td>
                      <td class="px-4 py-2">
                        {{ formatDate(group[0]?.detalle_gasto_comun.gastos_comune?.fecha_vencimiento) }}
                      </td>
                      <td class="px-4 py-2">
                        {{ group.reduce((sum, item) => sum + parseFloat(item.monto_asignado), 0) }}
                      </td>
                    </tr>
                    <!-- Detalles -->
                    <tr v-if="expandedGroups[idGasto]">
                      <td colspan="4" class="px-4 py-2">
                        <div class="border border-gray-200 rounded p-2">
                          <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                              <tr>
                                <th class="px-2 py-1">ID Distribución</th>
                                <th class="px-2 py-1">Detalle</th>
                                <th class="px-2 py-1">Tipo Gasto</th>
                                <th class="px-2 py-1">Monto Asignado</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr 
                                v-for="item in group" 
                                :key="item.id_distribucion" 
                                class="hover:bg-gray-50 border-b"
                              >
                                <td class="px-2 py-1">{{ item.id_distribucion }}</td>
                                <td class="px-2 py-1">
                                  {{ item.detalle_gasto_comun.descripcion_detalle }}
                                </td>
                                <td class="px-2 py-1">
                                  {{ item.detalle_gasto_comun.tipo_gasto_comun?.nombre ?? 'N/A' }}
                                </td>
                                <td class="px-2 py-1">{{ item.monto_asignado }}</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
          <!-- Fin tabla pendientes -->
        </div>
      </div> <!-- fin flex -->
    </div>
  </AuthenticatedLayout>
</template>
