<script setup>
import { ref, watch, computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import FileInput from "@/Components/FileInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

// Íconos de Heroicons (24/solid)
import {
  ClipboardDocumentListIcon, // Para Gasto Común
  FolderOpenIcon,            // Para Tipo de Gasto
  Bars3BottomLeftIcon,       // Para Ámbito de Distribución
  BuildingOffice2Icon,       // Para Torre
  HomeModernIcon,            // Para Unidad
  BanknotesIcon,             // Para Monto
  DocumentTextIcon,          // Para Descripción
  ChatBubbleLeftRightIcon,   // Para Observación (opcional)
  PaperClipIcon,             // Para Documento Respaldo
  ArrowPathIcon              // Para botón "Actualizar Detalle"
} from '@heroicons/vue/24/solid';

const props = defineProps({
  detalle: Object,   // Detalle a editar
  gastos: Array,     // Lista de gastos comunes
  tipos: Array,      // Lista de tipos de gasto
  edificios: Array,  // Lista de edificios (torres)
  unidades: {
    type: Array,
    default: () => []
  },
});

const form = useForm({
  id_gasto: props.detalle.id_gasto,
  id_tipo_gasto: props.detalle.id_tipo_gasto,
  monto_detalle: props.detalle.monto_detalle,
  nombre_file: props.detalle.nombre_file,
  file_url: null, // se usará para el nuevo archivo
  observacion: props.detalle.observacion,
  // Se cambia a input text, no textarea
  descripcion_detalle: props.detalle.descripcion_detalle || "",
  distribution_scope: props.detalle.distribution_scope || 'condominium',
  target_tower: props.detalle.target_tower ? String(props.detalle.target_tower) : '',
  target_unit: props.detalle.target_unit ? String(props.detalle.target_unit) : '',
});

function onSelectFile(e) {
  const files = e.target.files;
  if (files.length) {
    form.file_url = files[0];
  }
}

const selectedGasto = ref(null);
watch(() => form.id_gasto, (newVal) => {
  selectedGasto.value = props.gastos.find((g) => g.id_gasto === newVal) || null;
}, { immediate: true });

const montoRestante = computed(() => {
  if (!selectedGasto.value) return 0;
  const total = selectedGasto.value.monto_total || 0;
  const asignado = selectedGasto.value.monto_asignado || 0;
  return total - asignado;
});

// Filtrar unidades por torre cuando el ámbito es "unit"
const filteredUnidades = computed(() => {
  if (form.distribution_scope !== 'unit' || !form.target_tower) return [];
  return props.unidades.filter(u => u.id_edificio == form.target_tower);
});

// Al cambiar el scope, limpiar campos correspondientes
watch(() => form.distribution_scope, (newVal) => {
  if (newVal !== 'tower') form.target_tower = '';
  if (newVal !== 'unit') form.target_unit = '';
});

function updateForm() {
  // Convertir strings vacíos a null
  if (form.target_tower === '') form.target_tower = null;
  if (form.target_unit === '') form.target_unit = null;

  form.post(route("detalle_gasto_comun.update", props.detalle.id_detalle), {
    onSuccess: () => {
      form.reset("file_url");
    },
  });
}

const awsBaseUrl = import.meta.env.VITE_AWS_URL;
function getFileUrl(path) {
  return path ? `${awsBaseUrl}/${path}` : null;
}
</script>

<template>
  <Head title="Editar Detalle Gasto Común" />
  <AuthenticatedLayout>
    <!-- Encabezado -->
    <template #header>
      <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
        <h3 class="text-xl font-semibold text-white">Editar Detalle Gasto Común</h3>
        <Link :href="route('detalle_gasto_comun.index')" class="text-white hover:underline font-semibold">
          Volver al listado
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200">
        <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">Actualizar Detalle</h3>

        <form @submit.prevent="updateForm" class="space-y-6">
          <!-- Gasto Común -->
          <div>
            <InputLabel for="id_gasto" value="Gasto Común" />
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <ClipboardDocumentListIcon class="h-5 w-5 text-gray-400" />
              </div>
              <select
                id="id_gasto"
                v-model="form.id_gasto"
                required
                class="w-full border-gray-300 rounded-md pl-10"
              >
                <option disabled value="">Seleccione un gasto</option>
                <option v-for="g in gastos" :key="g.id_gasto" :value="g.id_gasto">
                  {{ g.descripcion }}
                </option>
              </select>
            </div>
            <InputError :message="form.errors.id_gasto" />
          </div>

          <!-- Información del Gasto Seleccionado -->
          <div v-if="selectedGasto" class="p-3 mt-1 border rounded bg-gray-50 text-sm">
            <p class="mb-1"><strong>Monto Total:</strong> {{ selectedGasto.monto_total ?? 'N/A' }}</p>
            <p class="mb-1"><strong>Asignado:</strong> {{ selectedGasto.monto_asignado ?? 0 }}</p>
            <p><strong>Restante:</strong> {{ montoRestante }}</p>
          </div>

          <!-- Tipo de Gasto -->
          <div>
            <InputLabel for="id_tipo_gasto" value="Tipo de Gasto" />
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <FolderOpenIcon class="h-5 w-5 text-gray-400" />
              </div>
              <select
                id="id_tipo_gasto"
                v-model="form.id_tipo_gasto"
                required
                class="w-full border-gray-300 rounded-md pl-10"
              >
                <option disabled value="">Seleccione un tipo</option>
                <option v-for="t in tipos" :key="t.id_tipo_gasto" :value="t.id_tipo_gasto">
                  {{ t.nombre }}
                </option>
              </select>
            </div>
            <InputError :message="form.errors.id_tipo_gasto" />
          </div>

          <!-- Ámbito de Distribución -->
          <div>
            <InputLabel for="distribution_scope" value="Ámbito de Distribución" />
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Bars3BottomLeftIcon class="h-5 w-5 text-gray-400" />
              </div>
              <select
                id="distribution_scope"
                v-model="form.distribution_scope"
                class="w-full border-gray-300 rounded-md pl-10"
              >
                <option value="condominium">Condominio</option>
                <option value="tower">Torre</option>
                <option value="unit">Unidad</option>
              </select>
            </div>
            <InputError :message="form.errors.distribution_scope" />
          </div>

          <!-- Selección de Torre (para 'tower' o 'unit') -->
          <div v-if="form.distribution_scope === 'tower' || form.distribution_scope === 'unit'">
            <InputLabel for="target_tower" value="Seleccionar Torre" />
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <BuildingOffice2Icon class="h-5 w-5 text-gray-400" />
              </div>
              <select
                id="target_tower"
                v-model="form.target_tower"
                class="w-full border-gray-300 rounded-md pl-10"
                required
              >
                <option disabled value="">Seleccione una torre</option>
                <option
                  v-for="ed in edificios"
                  :key="ed.id_edificio"
                  :value="String(ed.id_edificio)"
                >
                  {{ ed.nombre }}
                </option>
              </select>
            </div>
            <InputError :message="form.errors.target_tower" />
          </div>

          <!-- Selección de Unidad (solo para 'unit') -->
          <div v-if="form.distribution_scope === 'unit'">
            <InputLabel for="target_unit" value="Seleccionar Unidad" />
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <HomeModernIcon class="h-5 w-5 text-gray-400" />
              </div>
              <select
                id="target_unit"
                v-model="form.target_unit"
                class="w-full border-gray-300 rounded-md pl-10"
                required
              >
                <option disabled value="">Seleccione una unidad</option>
                <option
                  v-for="u in filteredUnidades"
                  :key="u.id_unidad"
                  :value="String(u.id_unidad)"
                >
                  {{ u.nombre_unidad }}
                </option>
              </select>
            </div>
            <InputError :message="form.errors.target_unit" />
          </div>

          <!-- Monto Detalle -->
          <div>
            <InputLabel for="monto_detalle" value="Monto Detalle" />
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <BanknotesIcon class="h-5 w-5 text-gray-400" />
              </div>
              <TextInput
                id="monto_detalle"
                type="number"
                step="0.01"
                v-model="form.monto_detalle"
                required
                class="w-full pl-10"
              />
            </div>
            <InputError :message="form.errors.monto_detalle" />
          </div>

          <!-- Descripción Detalle (input text en lugar de textarea) -->
          <div>
            <InputLabel for="descripcion_detalle" value="Descripción del Detalle" />
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <DocumentTextIcon class="h-5 w-5 text-gray-400" />
              </div>
              <TextInput
                id="descripcion_detalle"
                type="text"
                v-model="form.descripcion_detalle"
                class="w-full pl-10"
              />
            </div>
            <InputError :message="form.errors.descripcion_detalle" />
          </div>

          <!-- Observación -->
          <div>
            <InputLabel for="observacion" value="Observación" />
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <ChatBubbleLeftRightIcon class="h-5 w-5 text-gray-400" />
              </div>
              <textarea
                id="observacion"
                v-model="form.observacion"
                rows="3"
                class="w-full border-gray-300 rounded-md p-2 pl-10"
              ></textarea>
            </div>
            <InputError :message="form.errors.observacion" />
          </div>

          <!-- Documento Respaldo -->
          <div>
            <InputLabel for="file_url" value="Documento Respaldo" />
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <PaperClipIcon class="h-5 w-5 text-gray-400" />
              </div>
              <FileInput
                name="file_url"
                @change="onSelectFile"
                class="w-full pl-10"
              />
            </div>
            <InputError :message="form.errors.file_url" />
            <!-- Enlace al archivo actual si existe -->
            <div v-if="detalle.file_url" class="mt-2">
              <a
                :href="getFileUrl(detalle.file_url)"
                target="_blank"
                class="text-blue-600 hover:underline text-sm"
              >
                Ver archivo actual
              </a>
            </div>
          </div>

          <div class="flex justify-center">
            <PrimaryButton class="flex items-center space-x-1">
              <ArrowPathIcon class="h-5 w-5" />
              <span>Actualizar Detalle</span>
            </PrimaryButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos adicionales si lo requieres */
</style>
