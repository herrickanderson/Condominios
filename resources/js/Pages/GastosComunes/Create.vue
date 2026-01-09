<template>

    <Head title="Crear Gasto Común" />

    <AuthenticatedLayout>
        <!-- Encabezado -->
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h2 class="text-xl font-semibold text-white">Estas en: Gasto Común / Crear</h2>
                <Link :href="route('gastos_comunes.index')" class="text-white hover:underline font-semibold">
                <-Listado de Gastos
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200">
                <!-- Mensaje de error inline con transición -->
                <transition name="fade">
                    <div v-if="errorMessage" class="mb-4">
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                            role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ errorMessage }}</span>
                        </div>
                    </div>
                </transition>

                <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">
                    Registrar Gasto Común
                </h3>

                <!-- Formulario -->
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Descripción -->
                    <div>
                        <InputLabel for="descripcion" value="Descripción" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <DocumentTextIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <TextInput id="descripcion" type="text" v-model="form.descripcion" required
                                class="w-full pl-10" />
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
                            <select id="tipo_moneda" v-model="form.tipo_moneda" required
                                class="w-full border-gray-300 rounded-md pl-10">
                                <option value="Soles">Soles</option>
                                <option value="Dolares">Dolares</option>
                            </select>
                        </div>
                        <InputError :message="form.errors.tipo_moneda" />
                    </div>

                    <!-- Combo Box Mes -->
                    <div>
                        <InputLabel for="selectedMonth" value="Mes" />
                        <div class="relative">
                            <select id="selectedMonth" v-model="selectedMonth" required
                                class="w-full border-gray-300 rounded-md">
                                <option v-for="(mes, index) in months" :key="index" :value="index + 1">
                                    {{ mes }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Combo Box Año -->
                    <div>
                        <InputLabel for="selectedYear" value="Año" />
                        <div class="relative">
                            <select id="selectedYear" v-model="selectedYear" required
                                class="w-full border-gray-300 rounded-md">
                                <option v-for="year in years" :key="year" :value="year">
                                    {{ year }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Previsualización de Fechas calculadas -->
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
                    <!-- Si no hay configuración activa, se muestra el mensaje con el enlace y se deshabilita el guardado -->
                    <div v-else class="mb-4">
                        <p class="text-red-600 text-sm mb-2">
                            No se encontró una configuración activa para el período. Por favor, configúrelo primero.
                        </p>
                        <DropdownLink :href="route('periodos.index')"
                            class="bg-blue-500 text-white font-bold py-2 px-4 rounded inline-block border border-transparent hover:bg-white hover:text-blue-500 hover:border-blue-500">
                            Conf. de Periodo
                        </DropdownLink>
                    </div>

                    <!-- Campos ocultos para enviar las fechas calculadas, y monto_total = 0 -->
                    <input type="hidden" v-model="form.fecha_periodo" />
                    <input type="hidden" v-model="form.estado_pago" />
                    <input type="hidden" v-model="form.tipo_cobro" />
                    <input type="hidden" v-model="form.fecha_inicio" />
                    <input type="hidden" v-model="form.fecha_fin" />
                    <input type="hidden" v-model="form.fecha_vencimiento" />
                    <!-- Hidden para monto_total -->
                    <input type="hidden" v-model="form.monto_total" />

                    <!-- Botón Registrar -->
                    <div class="flex justify-center">
                        <PrimaryButton :disabled="!activeConfig" class="flex items-center space-x-1">
                            <PlusIcon class="h-5 w-5" />
                            <span>Registrar Gasto Común</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

import {
    DocumentTextIcon,
    BanknotesIcon,
    PlusIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
    condominios: {
        type: Array,
        default: () => [],
    },
    activeConfig: {
        type: Object,
        default: null,
    },
});

// Inicializa el formulario, con monto_total = 0
const form = useForm({
    descripcion: '',
    monto_total: 0, // Lo dejamos en cero
    tipo_moneda: 'Soles',
    fecha_periodo: '',
    fecha_inicio: '',
    fecha_fin: '',
    fecha_vencimiento: '',
    estado_pago: 'Pendiente',
    tipo_cobro: 'General',
    id_tipo_prorrateo: '',
    id_condominio: props.condominios.length > 0 ? props.condominios[0].id : '',
});

// Meses / Años
const months = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];
const currentYear = new Date().getFullYear();
const years = [currentYear, currentYear + 1, currentYear + 2];

// Se inicializan mes y año con la fecha actual
const selectedMonth = ref(new Date().getMonth() + 1);
const selectedYear = ref(currentYear);

function pad(num) {
    return num.toString().padStart(2, '0');
}
function getLastDay(year, month) {
    return new Date(year, month, 0).getDate();
}

// Computadas para fecha_inicio, fecha_fin y fecha_vencimiento
const computedFechaInicio = computed(() => {
    if (props.activeConfig) {
        const dayConfig = props.activeConfig.dia_inicio;
        const lastDay = getLastDay(selectedYear.value, selectedMonth.value);
        const day = dayConfig > lastDay ? lastDay : dayConfig;
        return `${selectedYear.value}-${pad(selectedMonth.value)}-${pad(day)}`;
    } else {
        return `${selectedYear.value}-${pad(selectedMonth.value)}-01`;
    }
});

const computedFechaFin = computed(() => {
    if (props.activeConfig) {
        let ny = selectedYear.value;
        let nm = selectedMonth.value + 1;
        if (nm > 12) { nm = 1; ny++; }
        const dayConfig = props.activeConfig.dia_fin;
        const lastDayNext = getLastDay(ny, nm);
        const day = dayConfig > lastDayNext ? lastDayNext : dayConfig;
        return `${ny}-${pad(nm)}-${pad(day)}`;
    } else {
        let ny = selectedYear.value;
        let nm = selectedMonth.value + 1;
        if (nm > 12) { nm = 1; ny++; }
        const lastDayNext = getLastDay(ny, nm);
        return `${ny}-${pad(nm)}-${pad(lastDayNext)}`;
    }
});

const computedFechaVencimiento = computed(() => {
    if (props.activeConfig) {
        let ny = selectedYear.value;
        let nm = selectedMonth.value + 1;
        if (nm > 12) { nm = 1; ny++; }
        const dayConfig = props.activeConfig.dia_vencimiento;
        const lastDayNext = getLastDay(ny, nm);
        const day = dayConfig > lastDayNext ? lastDayNext : dayConfig;
        return `${ny}-${pad(nm)}-${pad(day)}`;
    } else {
        let ny = selectedYear.value;
        let nm = selectedMonth.value + 1;
        if (nm > 12) { nm = 1; ny++; }
        const lastDayNext = getLastDay(ny, nm);
        return `${ny}-${pad(nm)}-${pad(lastDayNext)}`;
    }
});

watch(
    [selectedMonth, selectedYear, computedFechaInicio, computedFechaFin, computedFechaVencimiento],
    () => {
        form.fecha_inicio = computedFechaInicio.value;
        form.fecha_fin = computedFechaFin.value;
        form.fecha_vencimiento = computedFechaVencimiento.value;
        form.fecha_periodo = computedFechaInicio.value;
    },
    { immediate: true }
);

function submitForm() {
    // Solo se permite el envío si existe una configuración activa para el período
    if (!props.activeConfig) {
        return;
    }
    form.post(route('gastos_comunes.store'));
}

const errorMessage = ref('');
const page = usePage();
watch(
    () => page.props.flash?.error,
    (newError) => {
        if (newError) {
            errorMessage.value = newError;
            setTimeout(() => {
                errorMessage.value = '';
            }, 4000);
        }
    },
    { immediate: true }
);
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
