<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
import { ref, computed } from "vue";

// Datos recibidos del backend
const page = usePage();
const data = page.props.data || [];          // [{ edificio: 'Torre A', unidades: [...] }, ...]
const ultimoGasto = page.props.ultimoGasto || null;

// Barra de búsqueda
const searchQuery = ref("");

// Función para formatear fechas
function formatDate(dateStr) {
  if (!dateStr) return "N/A";
  return dayjs(dateStr).format("DD/MM/YYYY");
}

// Aplanar la data para que cada unidad sea un objeto con {edificio, unidad, arrendatario, propietario, status}
const allUnits = computed(() => {
  return data.flatMap((tower) => {
    return tower.unidades.map((u) => ({
      edificio: tower.edificio,
      unidad: u.unidad,
      arrendatario: u.arrendatario,
      propietario: u.propietario,
      status: u.status,
    }));
  });
});

// Filtrar según la búsqueda
const filteredUnits = computed(() => {
  if (!searchQuery.value) {
    return allUnits.value;
  }
  const q = searchQuery.value.toLowerCase();

  return allUnits.value.filter((item) => {
    return (
      (item.unidad && item.unidad.toLowerCase().includes(q)) ||
      (item.arrendatario && item.arrendatario.toLowerCase().includes(q)) ||
      (item.propietario && item.propietario.toLowerCase().includes(q)) ||
      (item.edificio && item.edificio.toLowerCase().includes(q))
    );
  });
});
</script>

<template>
  <Head title="Vigilancia" />

  <AuthenticatedLayout>
    <!-- Cabecera (slot #header) -->
    <template #header>
      <div class="flex items-center justify-between p-4 rounded-md bg-gradient-to-r from-green-600 to-lime-500">
        <h3 class="text-xl font-semibold text-white">Vista de Vigilancia</h3>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
          <!-- Título principal -->
          <h1 class="text-2xl font-bold mb-4 text-gray-700">
            Control de Pagos
          </h1>

          <!-- Último gasto común vencido (si existe) -->
          <div v-if="!ultimoGasto" class="p-4 bg-gray-50 border border-gray-200 rounded mb-4">
            <p class="text-gray-600">No se encontró ningún gasto común vencido.</p>
          </div>
          <div v-else class="p-4 mb-4 border-l-4 border-red-400 bg-red-50 rounded">
            <p class="text-sm text-gray-700 mb-1 flex items-center gap-1">
              <span class="font-semibold">Último gasto común vencido:</span>
              {{ ultimoGasto.descripcion }}
            </p>
            <p class="text-sm text-gray-700 flex items-center gap-1">
              <span class="font-semibold">Fecha de vencimiento:</span>
              {{ formatDate(ultimoGasto.fecha_vencimiento) }}
            </p>
          </div>

          <!-- Barra de búsqueda con ícono de lupa -->
          <div class="mb-6 relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
              <!-- Ícono de búsqueda (Heroicon: outline/search) -->
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 16l-4.4 4.4a1 1 0 101.4 1.4L9.4 17.4m3.6-1.4a7 7 0 110-14 7 7 0 010 14z"
                />
              </svg>
            </span>
            <input
              v-model="searchQuery"
              type="text"
              class="w-full border border-gray-300 rounded pl-10 pr-4 py-2 text-gray-700 focus:outline-none focus:ring focus:ring-green-200"
              placeholder="Buscar por torre, unidad, arrendatario o propietario..."
            />
          </div>

          <!-- Tabla unificada con todos los datos en una sola fila -->
          <div class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="bg-indigo-50 border-b border-gray-200">
                  <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Unidad</th>
                  <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Arrendatario</th>
                  <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Propietario</th>
                  <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Torre</th>
                  <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Estado</th>
                </tr>
              </thead>
              <tbody>
                <!-- Recorremos unidades filtradas: cada fila muestra toda la info -->
                <tr
                  v-for="(item, index) in filteredUnits"
                  :key="index"
                  class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 border-b last:border-none"
                >
                  <!-- Columna: Unidad (con ícono casita) -->
                  <td class="px-4 py-2 text-sm text-gray-700">
                    <div class="flex items-center gap-2">
                      <!-- Ícono casita -->
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-blue-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M3 9.75L12 4.5l9 5.25M12 4.5v13.5m0-13.5L3 9.75m18 0v8.25a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18V9.75"
                        />
                      </svg>
                      {{ item.unidad || "N/A" }}
                    </div>
                  </td>

                  <!-- Columna: Arrendatario (con ícono usuario) -->
                  <td class="px-4 py-2 text-sm text-gray-700">
                    <div class="flex items-center gap-2">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-purple-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M5.121 17.804A9.003 9.003 0 0112 3v0a9.003 9.003 0 016.879 14.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                      </svg>
                      {{ item.arrendatario || "N/A" }}
                    </div>
                  </td>

                  <!-- Columna: Propietario (con ícono usuario distinto color) -->
                  <td class="px-4 py-2 text-sm text-gray-700">
                    <div class="flex items-center gap-2">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-teal-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M5.121 17.804A9.003 9.003 0 0112 3v0a9.003 9.003 0 016.879 14.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                      </svg>
                      {{ item.propietario || "N/A" }}
                    </div>
                  </td>

                  <!-- Columna: Torre (con ícono edificio) -->
                  <td class="px-4 py-2 text-sm text-gray-700">
                    <div class="flex items-center gap-2">
                      <!-- Ícono de edificio -->
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-indigo-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M3 21V3h7v7h4V3h7v18m-7-4h2m-2-4h2m-2-4h2M5 7h2M5 11h2m-2 4h2m-2 4h2"
                        />
                      </svg>
                      {{ item.edificio || "N/A" }}
                    </div>
                  </td>

                  <!-- Columna: Estado (badge de color) -->
                  <td class="px-4 py-2">
                    <span
                      class="px-2 py-1 text-sm rounded-full font-semibold"
                      :class="item.status === 'green'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'"
                    >
                      {{ item.status === 'green' ? "Al día" : "En mora" }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Si no hay resultados tras filtrar -->
          <div v-if="filteredUnits.length === 0" class="text-gray-500 italic mt-4">
            No se encontraron resultados con el término de búsqueda.
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
