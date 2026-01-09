<template>
    <Head title="Ingreso Rápido de Mediciones" />
    <AuthenticatedLayout>
      <!-- Encabezado verde con título -->
      <template #header>
        <div class="flex items-center justify-center bg-green-600 p-4 rounded-md">
          <h1 class="text-xl font-bold text-white">Ingreso Rápido de Mediciones</h1>
        </div>
      </template>
  
      <!-- Mensajes Flash -->
      <div class="container mx-auto max-w-7xl sm:px-6 lg:px-8 mt-4">
        <transition name="fade">
          <div
            v-if="flashError"
            class="mb-4 p-4 rounded bg-red-100 text-red-800 text-sm"
          >
            <strong class="font-bold">Error:</strong>
            <span class="block sm:inline">{{ flashError }}</span>
          </div>
        </transition>
        <transition name="fade">
          <div
            v-if="flashSuccess"
            class="mb-4 p-4 rounded bg-green-100 text-green-800 text-sm"
          >
            <strong class="font-bold">Éxito:</strong>
            <span class="block sm:inline">{{ flashSuccess }}</span>
          </div>
        </transition>
      </div>
  
      <!-- Contenedor principal -->
      <div class="py-4">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <!-- Contenido interno -->
            <div class="p-6">
              <!-- Filtros (Torre / Búsqueda) -->
              <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:space-x-4 space-y-4 sm:space-y-0">
                <!-- Filtro Torre -->
                <div class="w-full sm:w-1/3">
                  <label class="block text-sm font-semibold mb-1">
                    Torre / Edificio
                  </label>
                  <select
                    @change="handleTorreChange"
                    class="w-full border border-gray-300 p-2 rounded text-sm"
                  >
                    <option value="">Todas</option>
                    <option
                      v-for="torre in torres"
                      :key="torre.id"
                      :value="torre.id"
                    >
                      {{ torre.nombre }}
                    </option>
                  </select>
                </div>
                <!-- Búsqueda -->
                <div class="w-full sm:w-1/3">
                  <label class="block text-sm font-semibold mb-1">
                    Buscar (Unidad, Extensión, Ocupante)
                  </label>
                  <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Ej: 101, Bodega, 'Juan'..."
                    class="w-full border border-gray-300 p-2 rounded text-sm"
                  />
                </div>
              </div>
  
              <!-- Sección UNIDADES -->
              <div class="mb-8">
                <h2 class="text-lg font-bold text-gray-700 mb-2">Unidades</h2>
                <!-- Contenedor con scroll para ~3 filas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-80 overflow-y-auto">
                  <div
                    v-for="unit in filteredUnidadMeasurements"
                    :key="unit.id_unidad"
                    class="border border-gray-200 rounded p-3"
                  >
                    <div class="mb-2">
                      <h3 class="text-sm font-bold text-blue-700">
                        {{ unit.nombre_unidad }}
                      </h3>
                      <p v-if="unit.occupantName" class="text-xs text-gray-500">
                        Ocupante: <strong>{{ unit.occupantName }}</strong>
                      </p>
                    </div>
                    <!-- Medidas -->
                    <div
                      v-for="measure in unit.measurements"
                      :key="measure.id"
                      class="border-b pb-2 mb-2 last:border-0 last:pb-0 last:mb-0"
                    >
                      <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-medium">
                          {{ measure.serviceName }}
                          <span v-if="measure.medida" class="text-gray-400">
                            ({{ measure.medida }})
                          </span>
                        </span>
                        <span v-if="measure.consumo > 0" class="text-gray-500">
                          Consumo:
                          <strong>{{ measure.consumo }}</strong>
                        </span>
                      </div>
  
                      <div class="flex space-x-2 items-center mb-2">
                        <!-- Lectura Anterior -->
                        <input
                          :placeholder="measure.placeholderInicial"
                          :value="measure.lecturaInicial"
                          @input="e => updateConsumption(measure, 'lecturaInicial', e)"
                          class="border rounded p-1 text-xs w-20"
                        />
                        <!-- Lectura Actual -->
                        <input
                          placeholder="Lectura Actual *"
                          :value="measure.lecturaFinal"
                          @input="e => updateConsumption(measure, 'lecturaFinal', e)"
                          class="border rounded p-1 text-xs w-20"
                        />
                        <!-- Botón Guardar -->
                        <button
                          :disabled="measure.saving"
                          @click="saveMeasurement(unit, 'unidad', measure)"
                          class="bg-green-500 hover:bg-green-600 text-white rounded px-3 py-1 text-xs"
                        >
                          {{ measure.saving ? "..." : "Guardar" }}
                        </button>
                      </div>
  
                      <!-- Campo para subir archivo -->
                      <div class="mb-2">
                        <input
                          type="file"
                          @change="e => updateFile(measure, e)"
                          class="border rounded p-1 text-xs"
                        />
                      </div>
  
                      <!-- Mensaje de error local -->
                      <div
                        v-if="measure.errorMessage"
                        class="text-red-600 text-xs"
                      >
                        {{ measure.errorMessage }}
                      </div>
                    </div>
                  </div>
  
                  <!-- Mensaje si no hay resultados -->
                  <div
                    v-if="filteredUnidadMeasurements.length === 0"
                    class="col-span-full text-center text-sm text-gray-500"
                  >
                    No hay unidades pendientes de medición (o no coinciden con la búsqueda).
                  </div>
                </div>
              </div>
  
              <!-- Sección EXTENSIONES -->
              <div>
                <h2 class="text-lg font-bold text-gray-700 mb-2">
                  Extensiones (Bodegas / Estacionamientos)
                </h2>
                <!-- Contenedor con scroll para ~3 filas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-80 overflow-y-auto">
                  <div
                    v-for="ext in filteredExtencionMeasurements"
                    :key="ext.id_extencion"
                    class="border border-gray-200 rounded p-3"
                  >
                    <div class="mb-2">
                      <h3 class="text-sm font-bold text-blue-700">
                        {{ ext.nombre_extencion }}
                      </h3>
                      <p v-if="ext.occupantName" class="text-xs text-gray-500">
                        Ocupante:
                        <strong>{{ ext.occupantName }}</strong>
                      </p>
                    </div>
                    <!-- Medidas -->
                    <div
                      v-for="measure in ext.measurements"
                      :key="measure.id"
                      class="border-b pb-2 mb-2 last:border-0 last:pb-0 last:mb-0"
                    >
                      <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-medium">
                          {{ measure.serviceName }}
                          <span v-if="measure.medida" class="text-gray-400">
                            ({{ measure.medida }})
                          </span>
                        </span>
                        <span v-if="measure.consumo > 0" class="text-gray-500">
                          Consumo:
                          <strong>{{ measure.consumo }}</strong>
                        </span>
                      </div>
  
                      <div class="flex space-x-2 items-center mb-2">
                        <!-- Lectura Anterior -->
                        <input
                          :placeholder="measure.placeholderInicial"
                          :value="measure.lecturaInicial"
                          @input="e => updateConsumption(measure, 'lecturaInicial', e)"
                          class="border rounded p-1 text-xs w-20"
                        />
                        <!-- Lectura Actual -->
                        <input
                          placeholder="Lectura Actual *"
                          :value="measure.lecturaFinal"
                          @input="e => updateConsumption(measure, 'lecturaFinal', e)"
                          class="border rounded p-1 text-xs w-20"
                        />
                        <!-- Botón Guardar -->
                        <button
                          :disabled="measure.saving"
                          @click="saveMeasurement(ext, 'extencion', measure)"
                          class="bg-green-500 hover:bg-green-600 text-white rounded px-3 py-1 text-xs"
                        >
                          {{ measure.saving ? "..." : "Guardar" }}
                        </button>
                      </div>
  
                      <!-- Campo para subir archivo -->
                      <div class="mb-2">
                        <input
                          type="file"
                          @change="e => updateFile(measure, e)"
                          class="border rounded p-1 text-xs"
                        />
                      </div>
  
                      <!-- Mensaje de error local -->
                      <div
                        v-if="measure.errorMessage"
                        class="text-red-600 text-xs"
                      >
                        {{ measure.errorMessage }}
                      </div>
                    </div>
                  </div>
  
                  <!-- Mensaje si no hay resultados -->
                  <div
                    v-if="filteredExtencionMeasurements.length === 0"
                    class="col-span-full text-center text-sm text-gray-500"
                  >
                    No hay extensiones pendientes de medición (o no coinciden con la búsqueda).
                  </div>
                </div>
              </div>
              <!-- Fin secciones -->
            </div>
            <!-- Fin p-6 -->
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  </template>
  
  <script setup>
  import { Inertia } from '@inertiajs/inertia'
  import { ref, computed, watch } from "vue";
  import { Head, usePage } from "@inertiajs/vue3";
  import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
  
  // PROPS
  const props = defineProps({
    torres: { type: Array, default: () => [] },
    pendientes_unidades: { type: Array, default: () => [] },
    pendientes_extenciones: { type: Array, default: () => [] },
    condominioId: { type: Number, default: 0 },
  });
  
  // FLASH
  const { props: pageProps } = usePage();
  const flashError = ref(pageProps.flash?.error || "");
  const flashSuccess = ref(pageProps.flash?.success || "");
  
  // TORRE
  const selectedTorre = ref(null);
  function handleTorreChange(e) {
    selectedTorre.value = e.target.value ? parseInt(e.target.value) : null;
  }
  
  // BÚSQUEDA
  const searchQuery = ref("");
  
  // FILTROS por torre
  const filteredUnidades = computed(() => {
    if (!selectedTorre.value) return props.pendientes_unidades;
    return props.pendientes_unidades.filter(
      (u) => u.id_edificio === selectedTorre.value
    );
  });
  const filteredExtenciones = computed(() => {
    if (!selectedTorre.value) return props.pendientes_extenciones;
    return props.pendientes_extenciones.filter(
      (e) => e.id_edificio === selectedTorre.value
    );
  });
  
  // ESTRUCTURAS para mediciones
  const unidadMeasurements = ref([]);
  const extencionMeasurements = ref([]);
  
  // Normalizar decimal (remplaza coma por punto)
  function normalizeDecimalInput(value) {
    return value.replace(",", ".");
  }
  
  // Crear fila de medición
  function createMeasurementRow(itemId, service) {
    return {
      id: `${itemId}-${service.id_tipo_gasto}`,
      serviceId: service.id_tipo_gasto,
      serviceName: service.nombre,
      medida: service.medida,
      lecturaInicial: service.ultima_lectura ?? "",
      lecturaFinal: "",
      consumo: 0,
      fechaMedicion: new Date().toISOString().substr(0, 10),
      saving: false,
      placeholderInicial:
        service.ultima_lectura !== null ? "Lectura Anterior" : "Lectura Inicial",
      errorMessage: "",
      archivo: null, // nuevo campo para almacenar el archivo seleccionado
    };
  }
  
  // Inicializar mediciones
  function initMeasurements() {
    // UNIDADES
    unidadMeasurements.value = filteredUnidades.value
      .map((unit) => ({
        id_unidad: unit.id_unidad,
        nombre_unidad: unit.tipo_unidad
          ? `${unit.tipo_unidad} ${unit.nombre_unidad}`
          : unit.nombre_unidad,
        occupantName: unit.occupantName || null,
        measurements: unit.services.map((s) =>
          createMeasurementRow(unit.id_unidad, s)
        ),
      }))
      .filter((u) => u.measurements.length > 0);
  
    // EXTENSIONES
    extencionMeasurements.value = filteredExtenciones.value
      .map((ext) => ({
        id_extencion: ext.id_extencion,
        nombre_extencion: ext.tipo_extencion
          ? `${ext.tipo_extencion} ${ext.nombre}`
          : ext.nombre,
        occupantName: ext.occupantName || null,
        measurements: ext.services.map((s) =>
          createMeasurementRow(ext.id_extencion, s)
        ),
      }))
      .filter((e) => e.measurements.length > 0);
  }
  
  watch(
    [selectedTorre, () => props.pendientes_unidades, () => props.pendientes_extenciones],
    () => initMeasurements(),
    { immediate: true }
  );
  
  // Filtros de búsqueda
  function matchesSearch(str, query) {
    if (!str) return false;
    return str.toLowerCase().includes(query.toLowerCase());
  }
  
  const filteredUnidadMeasurements = computed(() => {
    if (!searchQuery.value) return unidadMeasurements.value;
    const q = searchQuery.value.toLowerCase();
    return unidadMeasurements.value.filter((u) => {
      if (matchesSearch(u.nombre_unidad, q)) return true;
      if (matchesSearch(u.occupantName, q)) return true;
      return u.measurements.some(
        (m) => matchesSearch(m.serviceName, q) || matchesSearch(m.medida, q)
      );
    });
  });
  
  const filteredExtencionMeasurements = computed(() => {
    if (!searchQuery.value) return extencionMeasurements.value;
    const q = searchQuery.value.toLowerCase();
    return extencionMeasurements.value.filter((ext) => {
      if (matchesSearch(ext.nombre_extencion, q)) return true;
      if (matchesSearch(ext.occupantName, q)) return true;
      return ext.measurements.some(
        (m) => matchesSearch(m.serviceName, q) || matchesSearch(m.medida, q)
      );
    });
  });
  
  // Actualizar consumo y limpiar errores al cambiar campos
  function updateConsumption(measure, field, e) {
    const val = normalizeDecimalInput(e.target.value);
    e.target.value = val;
    measure.errorMessage = "";
    if (field === "lecturaInicial") {
      measure.lecturaInicial = val;
    } else {
      measure.lecturaFinal = val;
    }
    const ini = parseFloat(measure.lecturaInicial) || 0;
    const fin = parseFloat(measure.lecturaFinal) || 0;
    measure.consumo = fin - ini;
  }
  
  // Actualizar archivo seleccionado
  function updateFile(measure, e) {
    measure.archivo = e.target.files[0];
  }
  
  // Validar en el front
  function validateMeasurement(measure) {
    if (!measure.lecturaInicial) {
      measure.errorMessage = "Debe ingresar la Lectura Anterior.";
      return false;
    }
    if (!measure.lecturaFinal) {
      measure.errorMessage = "Debe ingresar la Lectura Actual.";
      return false;
    }
    const ini = parseFloat(measure.lecturaInicial);
    const fin = parseFloat(measure.lecturaFinal);
    if (isNaN(ini)) {
      measure.errorMessage = "La Lectura Anterior no es un valor numérico.";
      return false;
    }
    if (isNaN(fin)) {
      measure.errorMessage = "La Lectura Actual no es un valor numérico.";
      return false;
    }
    if (fin <= ini) {
      measure.errorMessage = "La Lectura Actual debe ser mayor que la Anterior.";
      return false;
    }
    return true;
  }
  
  // Guardar medición con validación local y FormData
  function saveMeasurement(item, type, measure) {
    measure.saving = true;
    measure.errorMessage = "";
  
    if (!validateMeasurement(measure)) {
      measure.saving = false;
      return;
    }
  
    // Crear FormData para soportar archivo
    const formData = new FormData();
    formData.append('id_condominio', props.condominioId);
    formData.append('id_tipo_gasto', measure.serviceId);
    formData.append('fecha_medicion', measure.fechaMedicion);
    formData.append('lectura_anterior', measure.lecturaInicial);
    formData.append('lectura_actual', measure.lecturaFinal);
    formData.append('observacion', '');
  
    if (type === "unidad") {
      formData.append('id_unidad', item.id_unidad);
    } else {
      formData.append('id_extencion', item.id_extencion);
    }
  
    if (measure.archivo) {
      formData.append('archivo', measure.archivo);
    }
  
    Inertia.post(route("mediciones.store"), formData, {
      preserveScroll: true,
      onSuccess: () => {
        Inertia.reload({ preserveScroll: true });
      },
      onFinish: () => {
        measure.saving = false;
      },
      onError: () => {
        measure.saving = false;
      },
    });
  }
  </script>
  
  <style scoped>
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.5s;
  }
  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }
  </style>
  