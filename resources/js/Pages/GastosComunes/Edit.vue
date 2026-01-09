<template>
    <Head title="Editar Gasto Común" />

    <AuthenticatedLayout>
      <!-- Encabezado -->
      <template #header>
        <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
          <h2 class="text-xl font-semibold text-white">Estas en: Gasto Común / Editar</h2>
          <Link :href="route('gastos_comunes.index')" class="text-white hover:underline font-semibold">
            <-Listado de Gastos
          </Link>
        </div>
      </template>

      <div class="py-12">
        <div class="mx-auto max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200">
          <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">Actualizar Gasto Común</h3>

          <form @submit.prevent="submitForm" class="space-y-6">
            <!-- Descripción -->
            <div>
              <InputLabel for="descripcion" value="Descripción" />
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <DocumentTextIcon class="h-5 w-5 text-gray-400" />
                </div>
                <TextInput
                  id="descripcion"
                  type="text"
                  v-model="form.descripcion"
                  required
                  class="w-full pl-10"
                />
              </div>
              <InputError :message="form.errors.descripcion" />
            </div>



            <!-- Tipo de Moneda -->
            <div>
              <InputLabel for="tipo_moneda" value="Tipo de Moneda" />
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <BanknotesIcon class="h-5 w-5 text-gray-400" />
                </div>
                <select
                  id="tipo_moneda"
                  v-model="form.tipo_moneda"
                  required
                  class="w-full border-gray-300 rounded-md pl-10"
                >
                  <option value="Soles">Soles</option>
                  <option value="Dolares">Dolares</option>
                </select>
              </div>
              <InputError :message="form.errors.tipo_moneda" />
            </div>

            <!-- Selección de Mes -->
            <div>
              <InputLabel for="selectedMonth" value="Mes" />
              <div class="relative">
                <select
                  id="selectedMonth"
                  v-model="selectedMonth"
                  required
                  class="w-full border-gray-300 rounded-md"
                >
                  <option v-for="(mes, index) in months" :key="index" :value="index + 1">
                    {{ mes }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Selección de Año -->
            <div>
              <InputLabel for="selectedYear" value="Año" />
              <div class="relative">
                <select
                  id="selectedYear"
                  v-model="selectedYear"
                  required
                  class="w-full border-gray-300 rounded-md"
                >
                  <option v-for="year in years" :key="year" :value="year">
                    {{ year }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Previsualización de Fechas calculadas (Inicio, Fin, Vencimiento) -->
            <div v-if="activeConfig">
              <p class="text-gray-600">
                <strong>Configuración Activa:</strong>
                Día inicio: {{ activeConfig.dia_inicio }},
                Día fin: {{ activeConfig.dia_fin }},
                Día vencimiento: {{ activeConfig.dia_vencimiento }}
              </p>
              <p class="mt-2 text-gray-800">
                <strong>Fechas calculadas:</strong>
              </p>
              <ul class="list-disc ml-5 text-gray-700">
                <li>Fecha Inicio: {{ computedFechaInicio }}</li>
                <li>Fecha Fin: {{ computedFechaFin }}</li>
                <li>Fecha Vencimiento: {{ computedFechaVencimiento }}</li>
              </ul>
            </div>
            <div v-else class="text-gray-600">
              <p>No se encontró una configuración activa para el período.</p>
            </div>

            <!-- Campos ocultos para enviar las fechas calculadas -->
            <input type="hidden" v-model="form.fecha_periodo" />
            <input type="hidden" v-model="form.fecha_inicio" />
            <input type="hidden" v-model="form.fecha_fin" />
            <input type="hidden" v-model="form.fecha_vencimiento" />

            <!-- Condominio (si existen varios) -->
            <div v-if="condominios.length > 0">
              <InputLabel for="id_condominio" value="Condominio" />
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <BuildingOffice2Icon class="h-5 w-5 text-gray-400" />
                </div>
                <select
                  id="id_condominio"
                  v-model="form.id_condominio"
                  required
                  class="w-full border-gray-300 rounded-md pl-10"
                >
                  <option disabled value="">Seleccione un condominio</option>
                  <option v-for="cond in condominios" :key="cond.id" :value="cond.id">
                    {{ cond.nombre }}
                  </option>
                </select>
              </div>
              <InputError :message="form.errors.id_condominio" />
            </div>

            <!-- Botón Actualizar -->
            <div class="flex justify-center">
              <PrimaryButton type="submit" class="flex items-center space-x-1">
                <ArrowPathIcon class="h-5 w-5" />
                <span>ACTUALIZAR GASTO COMÚN</span>
              </PrimaryButton>
            </div>
          </form>
        </div>
      </div>
    </AuthenticatedLayout>
  </template>

  <script setup>
  import { ref, computed, watch } from 'vue';
  import { useForm } from '@inertiajs/vue3';
  import { Head, Link } from '@inertiajs/vue3';
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
  import InputLabel from '@/Components/InputLabel.vue';
  import TextInput from '@/Components/TextInput.vue';
  import InputError from '@/Components/InputError.vue';
  import PrimaryButton from '@/Components/PrimaryButton.vue';

  import {
    DocumentTextIcon,
    CurrencyDollarIcon,
    BanknotesIcon,
    BuildingOffice2Icon,
    ArrowPathIcon
  } from '@heroicons/vue/24/solid';

  const props = defineProps({
    gasto: {
      type: Object,
      required: true,
    },
    condominios: {
      type: Array,
      default: () => [],
    },
    activeConfig: {
      type: Object,
      default: null,
    },
  });

  // Inicializamos el formulario con datos existentes
  const form = useForm({
    descripcion: props.gasto.descripcion,
    monto_total: props.gasto.monto_total,
    tipo_moneda: props.gasto.tipo_moneda || 'Soles',
    fecha_periodo: '',
    fecha_inicio: '',
    fecha_fin: '',
    fecha_vencimiento: '',
    id_condominio: props.gasto.id_condominio,
  });

  // Computada para el monto_total (evita warning en inputs numéricos)
  const montoTotalString = computed({
    get: () => (form.monto_total ? form.monto_total.toString() : ''),
    set: (value) => (form.monto_total = value),
  });

  // Meses y años para los selectores
  const months = [
    'Enero','Febrero','Marzo','Abril','Mayo','Junio',
    'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
  ];
  const currentYear = new Date().getFullYear();
  const years = [currentYear, currentYear + 1, currentYear + 2];

  // Obtenemos la fecha_inicio original del gasto para determinar la selección inicial de mes/año
  const initialDate = props.gasto.fecha_inicio ? new Date(props.gasto.fecha_inicio) : new Date();
  const selectedMonth = ref(initialDate.getMonth() + 1);
  const selectedYear = ref(initialDate.getFullYear());

  // Función para agregar ceros
  function pad(num) {
    return num.toString().padStart(2, '0');
  }

  // Función para obtener el último día de un mes dado (month es 1-based)
  function getLastDay(year, month) {
    return new Date(year, month, 0).getDate();
  }

  // Computadas de fechas según la configuración activa
  const computedFechaInicio = computed(() => {
    if (props.activeConfig) {
      const dayConfig = props.activeConfig.dia_inicio;
      const lastDay = getLastDay(selectedYear.value, selectedMonth.value);
      const day = dayConfig > lastDay ? lastDay : dayConfig;
      return `${selectedYear.value}-${pad(selectedMonth.value)}-${pad(day)}`;
    } else {
      // Por defecto, primer día del mes
      return `${selectedYear.value}-${pad(selectedMonth.value)}-01`;
    }
  });

  const computedFechaFin = computed(() => {
    if (props.activeConfig) {
      let nextYear = selectedYear.value;
      let nextMonth = selectedMonth.value + 1;
      if (nextMonth > 12) {
        nextMonth = 1;
        nextYear += 1;
      }
      const dayConfig = props.activeConfig.dia_fin;
      const lastDayNext = getLastDay(nextYear, nextMonth);
      const day = dayConfig > lastDayNext ? lastDayNext : dayConfig;
      return `${nextYear}-${pad(nextMonth)}-${pad(day)}`;
    } else {
      // Por defecto, último día del mes siguiente
      let nextYear = selectedYear.value;
      let nextMonth = selectedMonth.value + 1;
      if (nextMonth > 12) {
        nextMonth = 1;
        nextYear += 1;
      }
      const lastDayNext = getLastDay(nextYear, nextMonth);
      return `${nextYear}-${pad(nextMonth)}-${pad(lastDayNext)}`;
    }
  });

  const computedFechaVencimiento = computed(() => {
    if (props.activeConfig) {
      let nextYear = selectedYear.value;
      let nextMonth = selectedMonth.value + 1;
      if (nextMonth > 12) {
        nextMonth = 1;
        nextYear += 1;
      }
      const dayConfig = props.activeConfig.dia_vencimiento;
      const lastDayNext = getLastDay(nextYear, nextMonth);
      const day = dayConfig > lastDayNext ? lastDayNext : dayConfig;
      return `${nextYear}-${pad(nextMonth)}-${pad(day)}`;
    } else {
      let nextYear = selectedYear.value;
      let nextMonth = selectedMonth.value + 1;
      if (nextMonth > 12) {
        nextMonth = 1;
        nextYear += 1;
      }
      const lastDayNext = getLastDay(nextYear, nextMonth);
      return `${nextYear}-${pad(nextMonth)}-${pad(lastDayNext)}`;
    }
  });

  // Actualizamos los campos ocultos cuando cambian mes/año o las computadas
  watch(
    [selectedMonth, selectedYear, computedFechaInicio, computedFechaFin, computedFechaVencimiento],
    () => {
      form.fecha_inicio = computedFechaInicio.value;
      form.fecha_fin = computedFechaFin.value;
      form.fecha_vencimiento = computedFechaVencimiento.value;
      form.fecha_periodo = computedFechaInicio.value; // o como desees asignarlo
    },
    { immediate: true }
  );

  function submitForm() {
    form.put(route('gastos_comunes.update', props.gasto.id_gasto));
  }
  </script>

  <style scoped>
  /* Estilos opcionales */
  </style>
