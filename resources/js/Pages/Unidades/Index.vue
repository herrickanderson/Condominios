<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, usePage, router as Inertia } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
  unidades: Object, // Paginado con data y links
});

const unidadesData = computed(() => props.unidades.data || []);

// Búsqueda local: Filtrado en vivo
const searchQuery = ref('');
const filteredUnidades = computed(() => {
  if (!searchQuery.value) return unidadesData.value;
  const q = searchQuery.value.toLowerCase();
  return unidadesData.value.filter(unidad => {
    const nombre = unidad.nombre_unidad || '';
    const tipo = unidad.tipo_unidad === 'negocio' ? 'Negocio' : 'Departamento';
    const edificio = unidad.edificio ? (unidad.edificio.nombre || '') : '';
    const area = unidad.area ? unidad.area.toString() : '';
    const propietario = unidad.propietario ? (unidad.propietario.name || '') : '';
    return (
      nombre.toLowerCase().includes(q) ||
      tipo.toLowerCase().includes(q) ||
      edificio.toLowerCase().includes(q) ||
      area.includes(q) ||
      propietario.toLowerCase().includes(q)
    );
  });
});

function clearSearch() {
  searchQuery.value = '';
}

// Modal para eliminar
const showConfirmModal = ref(false);
const unidadToDelete = ref(null);
function openDeleteModal(unidad) {
  unidadToDelete.value = unidad;
  showConfirmModal.value = true;
}
function confirmDelete() {
  if (!unidadToDelete.value) return;
  Inertia.delete(route('unidades.destroy', unidadToDelete.value.id_unidad), {
    onFinish: () => {
      showConfirmModal.value = false;
      unidadToDelete.value = null;
    }
  });
}
</script>

<template>
  <Head title="Listado de Unidades" />
  <AuthenticatedLayout>
    <!-- Encabezado -->
    <template #header>
      <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
        <h3 class="text-xl font-semibold text-white">Estas en: Unidades / Lista</h3>
        <Link :href="route('unidades.create')" class="text-white hover:underline font-semibold">
          Crear Unidad
        </Link>
      </div>
    </template>

    <!-- Mensajes flash directos (se muestran si existen en $page.props.flash) -->
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 mt-3">
      <!-- Flash de éxito -->
      <div v-if="$page.props.flash.success" class="p-2 bg-green-100 text-green-700 rounded mb-3 text-sm">
        {{ $page.props.flash.success }}
      </div>
      <!-- Flash de error -->
      <div v-if="$page.props.flash.error" class="p-2 bg-red-100 text-red-700 rounded mb-3 text-sm">
        {{ $page.props.flash.error }}
      </div>
    </div>

    <div class="py-3">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
          <!-- Barra de búsqueda en vivo -->
          <div class="mb-3 max-w-md relative">
            <label for="search" class="block font-semibold text-gray-700 mb-1 text-sm">
              Buscar unidades
            </label>
            <input
              id="search"
              v-model="searchQuery"
              type="text"
              placeholder="Ej: Nombre, edificio, propietario..."
              class="w-full rounded-md border border-gray-300 p-1 text-sm focus:border-green-500 focus:ring-green-500"
            />
            <button v-if="searchQuery" @click="clearSearch" class="absolute right-2 top-8 text-gray-500 hover:text-gray-700">
              <!-- Ícono de limpiar búsqueda -->
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Tabla de Unidades -->
          <h2 class="text-lg font-semibold mb-2">Listado de Unidades</h2>
          <div class="relative overflow-x-auto overflow-y-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead>
                <tr class="bg-gray-50">
                  <!-- Se omite la columna ID -->
                  <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Nombre</th>
                  <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Tipo</th>
                  <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Edificio</th>
                  <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Área (m²)</th>
                  <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Servicios Extras</th>
                  <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Propietario</th>
                  <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="unidad in filteredUnidades" :key="unidad.id_unidad" class="hover:bg-gray-100">
                  <td class="px-4 py-2 text-sm text-gray-700">{{ unidad.nombre_unidad }}</td>
                  <td class="px-4 py-2 text-sm text-gray-700">
                    {{ unidad.tipo_unidad === 'negocio' ? 'Negocio' : 'Departamento' }}
                  </td>
                  <td class="px-4 py-2 text-sm text-gray-700">
                    {{ unidad.edificio ? unidad.edificio.nombre : '' }}
                  </td>
                  <td class="px-4 py-2 text-sm text-gray-700">
                    {{ unidad.area }} {{ unidad.unidad_medida }}
                  </td>
                  <td class="px-4 py-2 text-sm text-gray-700">
                    <span v-if="unidad.tipo_unidad === 'negocio'">
                      {{ unidad.servicios_extras ? unidad.servicios_extras.length : 0 }} servicios
                    </span>
                    <span v-else>-</span>
                  </td>
                  <td class="px-4 py-2 text-sm text-gray-700">
                    {{ unidad.propietario ? unidad.propietario.name : 'No asignado' }}
                  </td>
                  <td class="px-4 py-2 text-sm text-gray-700">
                    <div class="flex items-center space-x-3">
                      <Link :href="route('unidades.edit', unidad.id_unidad)" class="text-blue-600 hover:underline">
                        Editar
                      </Link>
                      <button @click="openDeleteModal(unidad)" class="text-red-600 hover:underline">
                        Eliminar
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div class="mt-4">
            <Pagination :links="props.unidades.links" />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <ConfirmationModal 
      :visible="showConfirmModal" 
      :message="`¿Desea eliminar la unidad ${unidadToDelete?.nombre_unidad || ''}?`" 
      @cancel="showConfirmModal = false" 
      @confirm="confirmDelete" 
    />
  </AuthenticatedLayout>
</template>

<style scoped>
/* Transiciones para mensajes flash */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
