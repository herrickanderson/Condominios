<template>
    <Head title="Configuración de Mora" />
    <AuthenticatedLayout>
      <template #header>
        <div class="flex items-center justify-between bg-purple-600 p-4 rounded-md">
          <h2 class="text-xl font-semibold text-white">Configuración de Mora</h2>
        </div>
      </template>
  
      <div class="py-10 max-w-7xl mx-auto space-y-8">
        <!-- Formulario -->
        <div class="bg-white p-6 rounded shadow">
          <h3 class="text-lg font-bold mb-4">Agregar Nueva Configuración de Mora</h3>
          <!-- Si es superadmin, mostrar select para elegir condominio -->
          <div v-if="isSuperAdmin && condominios.length > 0" class="mb-4">
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
              <InputLabel value="Tipo de Periodo" />
              <select v-model="form.tipo_periodo" class="w-full border-gray-300 rounded">
                <option disabled value="">Seleccione un periodo</option>
                <option value="diario">Diario</option>
                <option value="semanal">Semanal</option>
                <option value="mensual">Mensual</option>
              </select>
              <InputError :message="form.errors.tipo_periodo" />
            </div>
            <div>
              <InputLabel value="Porcentaje de Mora (%)" />
              <TextInput v-model="form.porcentaje" type="number" step="0.01" class="w-full" placeholder="Ej: 1.50" />
              <InputError :message="form.errors.porcentaje" />
            </div>
          </div>
  
          <div class="mt-4">
            <div v-if="periodConfig">
              <p class="text-gray-600">
                <strong>Configuración del Período Activo:</strong>
                Día vencimiento: {{ periodConfig.dia_vencimiento }}
              </p>
            </div>
            <div v-else>
              <p class="text-red-600 text-sm">
                No se encontró una configuración de período activa.
                <Link :href="route('periodos.index')" class="underline text-blue-500">Configurar Período</Link>
              </p>
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
  
        <!-- Filtro para superadmin -->
        <div v-if="isSuperAdmin && condominios.length > 0" class="mb-4">
          <InputLabel value="Filtrar por Condominio" />
          <select v-model="condominioFilter" class="w-full border-gray-300 rounded">
            <option value="">Todos</option>
            <option v-for="c in condominios" :key="c.id_condominio" :value="c.id_condominio">
              {{ c.nombre }}
            </option>
          </select>
        </div>
  
        <!-- Lista de configuraciones -->
        <div class="bg-white p-6 rounded shadow">
          <h3 class="text-lg font-bold mb-4">Configuraciones Guardadas</h3>
          <table class="w-full text-sm text-left border">
            <thead class="bg-gray-50">
              <tr>
                <th v-if="isSuperAdmin" class="px-4 py-2">Condominio</th>
                <th class="px-4 py-2">Periodo</th>
                <th class="px-4 py-2">Porcentaje (%)</th>
                <th class="px-4 py-2">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="cfg in configsFiltrados" :key="cfg.id" class="border-t">
                <td v-if="isSuperAdmin" class="px-4 py-2">
                  {{ cfg.condominio ? cfg.condominio.nombre : 'N/A' }}
                </td>
                <td class="px-4 py-2 capitalize">{{ cfg.tipo_periodo }}</td>
                <td class="px-4 py-2">{{ cfg.porcentaje }}</td>
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
                <td :colspan="isSuperAdmin ? 4 : 3" class="text-center py-4 text-gray-500">
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
          <p class="text-center text-sm text-gray-600">Se guardará la configuración de mora.</p>
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
  import { ref, computed, watch } from 'vue';
  import { Inertia } from '@inertiajs/inertia';
  import { useForm, Head, Link } from '@inertiajs/vue3';
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
  import PrimaryButton from '@/Components/PrimaryButton.vue';
  import TextInput from '@/Components/TextInput.vue';
  import InputLabel from '@/Components/InputLabel.vue';
  import InputError from '@/Components/InputError.vue';
  import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/solid';
  
  const props = defineProps({
    configs: Array,
    periodConfig: {
      type: Object,
      default: null
    },
    condominios: {
      type: Array,
      default: () => []
    },
    selectedCondominio: {
      type: [String, Number, null],
      default: null
    },
    isSuperAdmin: {
      type: Boolean,
      default: false
    }
  });
  
  const form = useForm({
    id_condominio: props.selectedCondominio || '',
    tipo_periodo: '',
    porcentaje: ''
  });
  
  const editing = ref(false);
  const editingId = ref(null);
  const showConfirmModal = ref(false);
  
  // Filtro para superadmin
  const condominioFilter = ref(props.selectedCondominio || "");
  
  watch(condominioFilter, (newVal) => {
    Inertia.get(route("ConfMora.index"), { condominio_id: newVal }, {
      preserveState: false,
      preserveScroll: true,
      replace: true
    });
  });
  
  function openConfirmModal() {
    if (!form.tipo_periodo || !form.porcentaje) {
      alert('Complete los campos obligatorios.');
      return;
    }
    if (!props.selectedCondominio && !form.id_condominio) {
      alert('Seleccione un condominio.');
      return;
    }
    showConfirmModal.value = true;
  }
  
  function confirmSubmit() {
    showConfirmModal.value = false;
    const options = {
      onSuccess: () => {
        form.reset();
        editing.value = false;
        editingId.value = null;
        window.location.reload();
      }
    };
    if (editing.value && editingId.value) {
      form.post(route('ConfMora.update', editingId.value), options);
    } else {
      form.post(route('ConfMora.store'), options);
    }
  }
  
  function cancelEdit() {
    form.reset();
    editing.value = false;
    editingId.value = null;
  }
  
  function startEdit(cfg) {
    editing.value = true;
    editingId.value = cfg.id;
    form.id_condominio = cfg.id_condominio;
    form.tipo_periodo = cfg.tipo_periodo;
    form.porcentaje = cfg.porcentaje;
  }
  
  function eliminar(id) {
    if (confirm('¿Está seguro de eliminar esta configuración?')) {
      form.delete(route('ConfMora.destroy', id));
    }
  }
  
  const configsFiltrados = computed(() => {
    if (condominioFilter.value === "") {
      return props.configs;
    }
    return props.configs.filter(cfg => String(cfg.id_condominio) === String(condominioFilter.value));
  });
  </script>
  