<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import apexchart from 'vue3-apexcharts';

// Se reciben los props iniciales para carga inicial
const props = defineProps({
  condominios: Array,
  edificios: Array,
  usuarios: Array,
  ingresos: Object,
  porDia: Array,
  porHora: Array,
  topUsuarios: Array,
  navegadores: Array,
  filtros: Object,
});

// Creamos un objeto reactivo para almacenar todos los datos
const data = ref({
  condominios: props.condominios,
  edificios: props.edificios,
  usuarios: props.usuarios,
  ingresos: props.ingresos,
  porDia: props.porDia,
  porHora: props.porHora,
  topUsuarios: props.topUsuarios,
  navegadores: props.navegadores,
  filtros: { ...props.filtros },
});

// Función que consulta la API y actualiza los datos sin redirección
async function updateData(){
  try {
    const response = await axios.get(route('reporte.ingresos'), {
      params: data.value.filtros,
      headers: { 'Accept': 'application/json' }
    });
    const res = response.data;
    data.value.condominios = res.condominios;
    data.value.edificios   = res.edificios;
    data.value.usuarios    = res.usuarios;
    data.value.ingresos    = res.ingresos;
    data.value.porDia      = res.porDia;
    data.value.porHora     = res.porHora;
    data.value.topUsuarios = res.topUsuarios;
    data.value.navegadores = res.navegadores;
  } catch (error) {
    console.error(error);
  }
}

// Observa cambios profundos en los filtros y actualiza los datos
watch(() => data.value.filtros, () => {
  updateData();
}, { deep: true });

// Función para cambiar la página (se actualiza el filtro y se consulta la API)
function changePage(page) {
  data.value.filtros.page = page;
  updateData();
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString();
}

function formatTime(dateStr) {
  return new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function exportarExcel() {
  const query = new URLSearchParams(data.value.filtros).toString();
  window.open(route('reporte.ingresos.export.excel') + '?' + query, '_blank');
}

function exportarCsv() {
  const query = new URLSearchParams(data.value.filtros).toString();
  window.open(route('reporte.ingresos.export.csv') + '?' + query, '_blank');
}

// Configuración de gráficos usando computed para que se actualicen al cambiar los datos
const chartDia = computed(() => ({
  options: {
    chart: { id: 'ingresos-dia' },
    xaxis: { categories: data.value.porDia.map(i => i.dia) },
  },
  series: [{ name: 'Ingresos', data: data.value.porDia.map(i => i.total) }]
}));

const chartHora = computed(() => ({
  options: {
    chart: { id: 'ingresos-hora' },
    xaxis: { categories: data.value.porHora.map(i => `${i.hora}:00`) },
  },
  series: [{ name: 'Ingresos', data: data.value.porHora.map(i => i.total) }]
}));

const chartUsuarios = computed(() => ({
  options: {
    labels: data.value.topUsuarios.map(u => `${u.usuario?.name ?? 'N/A'} ${u.usuario?.apellidos ?? ''}`),
    legend: { position: 'bottom' },
  },
  series: data.value.topUsuarios.map(u => u.total),
}));

const chartNavegadores = computed(() => ({
  options: {
    labels: data.value.navegadores.map(n => n.navegador),
    legend: { position: 'bottom' },
  },
  series: data.value.navegadores.map(n => n.total),
}));
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Reporte de Ingresos al Sistema</h2>
    </template>
    <div class="py-6 px-4 max-w-7xl mx-auto">
      <!-- Filtros -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">Condominio</label>
          <select v-model="data.filtros.condominio_id" class="form-select mt-1 block w-full">
            <option value="">Todos</option>
            <option v-for="c in data.condominios" :key="c.id_condominio" :value="c.id_condominio">
              {{ c.nombre }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Torre/Edificio</label>
          <select v-model="data.filtros.edificio_id" class="form-select mt-1 block w-full">
            <option value="">Todos</option>
            <option v-for="e in data.edificios" :key="e.id_edificio" :value="e.id_edificio">
              {{ e.nombre }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Usuario</label>
          <select v-model="data.filtros.usuario_id" class="form-select mt-1 block w-full">
            <option value="">Todos</option>
            <option v-for="u in data.usuarios" :key="u.id" :value="u.id">
              {{ u.name }} {{ u.apellidos }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Desde</label>
          <input type="date" v-model="data.filtros.fecha_inicio" class="form-input mt-1 block w-full" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Hasta</label>
          <input type="date" v-model="data.filtros.fecha_fin" class="form-input mt-1 block w-full" />
        </div>
      </div>
      <!-- Botones de exportación -->
      <div class="flex justify-end mb-4 gap-2">
        <button @click="exportarExcel"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
          Exportar Excel
        </button>
        <button @click="exportarCsv"
                class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800 text-sm">
          Exportar CSV
        </button>
      </div>
      <!-- Gráficos -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white p-4 rounded shadow">
          <h3 class="font-semibold mb-2">Ingresos por Día</h3>
          <apexchart type="bar" height="250" :options="chartDia.options" :series="chartDia.series" />
        </div>
        <div class="bg-white p-4 rounded shadow">
          <h3 class="font-semibold mb-2">Ingresos por Hora</h3>
          <apexchart type="bar" height="250" :options="chartHora.options" :series="chartHora.series" />
        </div>
        <div class="bg-white p-4 rounded shadow">
          <h3 class="font-semibold mb-2">Top Usuarios</h3>
          <apexchart type="donut" height="250" :options="chartUsuarios.options" :series="chartUsuarios.series" />
        </div>
        <div class="bg-white p-4 rounded shadow">
          <h3 class="font-semibold mb-2">Navegadores</h3>
          <apexchart type="pie" height="250" :options="chartNavegadores.options" :series="chartNavegadores.series" />
        </div>
      </div>
      <!-- Tabla de resultados -->
      <div class="bg-white rounded shadow p-4 overflow-x-auto">
        <h3 class="font-semibold mb-4">Ingresos Detallados</h3>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Usuario</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Condominio</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Unidad</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Fecha</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Hora</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">IP</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Navegador</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="i in data.ingresos.data" :key="i.id">
              <td class="px-4 py-2">{{ i.usuario?.name }} {{ i.usuario?.apellidos }}</td>
              <td class="px-4 py-2">{{ i.usuario?.id_condominio }}</td>
              <td class="px-4 py-2">{{ i.usuario?.unidad?.nombre_unidad ?? 'N/A' }}</td>
              <td class="px-4 py-2">{{ formatDate(i.fecha_hora_ingreso) }}</td>
              <td class="px-4 py-2">{{ formatTime(i.fecha_hora_ingreso) }}</td>
              <td class="px-4 py-2">{{ i.ip }}</td>
              <td class="px-4 py-2">{{ i.navegador }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Paginación -->
      <div class="flex justify-center mt-4 gap-1 flex-wrap">
        <button v-for="page in data.ingresos.last_page" :key="page" @click="changePage(page)"
                class="px-3 py-1 rounded border" :class="{
                  'bg-blue-600 text-white': data.ingresos.current_page === page,
                  'bg-white text-gray-700': data.ingresos.current_page !== page
                }">
          {{ page }}
        </button>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
