<script setup>
import { ref, computed, reactive, onMounted, watch } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DashboardChart from "@/Components/DashboardChart.vue";
import HistoricalChart from "@/Components/HistoricalChart.vue";
import UploadComprobanteModal from "@/Components/UploadComprobanteModal.vue";
import ViewArchivoModal from "@/Components/ViewArchivoModal.vue";
import axios from "axios";

/**
 * Props desde la Inertia page:
 * - pendientes: array de DistribucionGasto
 * - userPayments: array de Pagos
 */
const page = usePage();
const pendientesProp = page.props.pendientes ?? [];
const userPayments = page.props.userPayments ?? [];

// Aseguramos array
const pendientes = Array.isArray(pendientesProp) ? pendientesProp : Object.values(pendientesProp);

/**
 * paymentsByGasto => map { id_gasto: pago }
 */
const paymentsByGasto = computed(() => {
    const map = {};
    userPayments.forEach((pay) => {
        map[pay.id_gasto] = pay;
    });
    return map;
});

/**
 * groupByGasto => agrupar las distribuciones por id_gasto
 */
function groupByGasto(list) {
    const groups = {};
    for (const item of list) {
        const gasto = item.detalle_gasto_comun.gastos_comune;
        const key = gasto ? gasto.id_gasto : "sin_gasto";
        if (!groups[key]) {
            groups[key] = [];
        }
        groups[key].push(item);
    }
    return groups;
}
const groupedPendientes = computed(() => groupByGasto(pendientes));

/**
 * Expandir/cerrar filas
 */
const expandedGroups = ref({});
function toggleGroup(idGasto) {
    expandedGroups.value[idGasto] = !expandedGroups.value[idGasto];
}

/**
 * Determinar estado
 */
function getEstadoGasto(idGasto) {
    const pay = paymentsByGasto.value[idGasto];
    if (!pay) {
        return "Pendiente";
    }
    return pay.estado; // "Enviado", "Rechazado", "Aceptado", etc.
}

/**
 * Formatear fecha
 */
function formatDate(dateStr) {
    if (!dateStr) return "N/A";
    const d = new Date(dateStr);
    return isNaN(d) ? "N/A" : d.toLocaleDateString();
}

/**
 * Modal para subir/corregir comprobante
 */
const showModal = ref(false);
const modalData = reactive({
    idGasto: null,
    paymentId: null,
    monto: 0,
    fechaPago: "",
});
function openUploadModal(idGasto) {
    const existingPay = paymentsByGasto.value[idGasto];
    modalData.paymentId = existingPay ? existingPay.id_pago : null;

    const group = groupedPendientes.value[idGasto];
    if (group && group.length) {
        // Suma todos los montos y luego formatea a 2 decimales
        const monto = group.reduce(
            (sum, item) => sum + parseFloat(item.monto_asignado),
            0
        );
        // .toFixed(2) devuelve un string con 2 decimales
        modalData.monto = monto.toFixed(2);
    } else {
        modalData.monto = "0.00";
    }

    const today = new Date();
    modalData.fechaPago = today.toISOString().split("T")[0];
    modalData.idGasto = idGasto;
    showModal.value = true;
}


/**
 * Para construir la URL completa desde S3
 * Ajusta la variable VITE_AWS_URL según tu .env
 */
const s3BaseUrl = import.meta.env.VITE_AWS_URL || "https://mi-bucket-s3.s3.amazonaws.com";
function getFileUrl(path) {
    if (!path) return "";
    // Devuelve la ruta completa: https://mi-bucket-s3.s3.amazonaws.com/produccion/mediciones/...
    return `${s3BaseUrl}/${path}`;
}

/**
 * Modal para visualizar el archivo de medición consumo
 */
const showViewArchivoModal = ref(false);
const viewArchivoData = reactive({
    archivoUrl: "",
});
function openViewArchivoModal(path) {
    // Construimos la URL completa y la asignamos al modal
    const fullUrl = getFileUrl(path);
    //console.log("Archivo a visualizar:", fullUrl);
    viewArchivoData.archivoUrl = fullUrl;
    showViewArchivoModal.value = true;
}

/**
 * Lógica de Gráficos
 */
const allGastosPorMes = ref([]);
const selectedChartFilter = ref("6meses");
const historicalData = ref([]);

onMounted(() => {
    loadChartData("6meses");
    loadHistoricalData(null);
});

function loadChartData(filter) {
    axios
        .get(route("dashboard.gastos"), { params: { filter } })
        .then((resp) => {
            allGastosPorMes.value = resp.data.gastosPorMes;
        })
        .catch((err) => console.error(err));
}

function loadHistoricalData(gastoFilter) {
    axios
        .get(route("dashboard.historical"), { params: { gasto: gastoFilter } })
        .then((resp) => {
            historicalData.value = resp.data.historicalData;
        })
        .catch((err) => console.error(err));
}

const gastosPorMes = computed(() => {
    if (selectedChartFilter.value === "6meses") {
        return allGastosPorMes.value;
    }
    return allGastosPorMes.value.filter((item) => item.mes === selectedChartFilter.value);
});

const chartFilterOptions = computed(() => {
    const options = [{ value: "6meses", text: "Últimos 6 Meses" }];
    const uniqueGastos = Array.from(new Set(allGastosPorMes.value.map((item) => item.mes)));
    uniqueGastos.forEach((mes) => {
        options.push({ value: mes, text: mes });
    });
    return options;
});

watch(selectedChartFilter, (newVal) => {
    if (newVal === "6meses") {
        loadChartData("6meses");
        loadHistoricalData(null);
    } else {
        loadChartData(newVal);
        loadHistoricalData(newVal);
    }
});

/**
 * Helper para porcentaje prorrateo
 * Si el item corresponde a una extensión (id_extencion), se retorna "2--"
 * De lo contrario, calcula el porcentaje
 */
function getPercentage(item) {
    if (item.id_extencion) {
        return "2--";
    }
    const gasto = item.detalle_gasto_comun.gastos_comune;
    if (!gasto || !gasto.monto_total) {
        return 0;
    }
    const pct = (item.monto_asignado / gasto.monto_total) * 100;
    return pct.toFixed(2) + "%";
}
</script>

<template>

    <Head title="Mis Gastos Pendientes" />
    <AuthenticatedLayout>
        <!-- CABECERA -->
        <template #header>
            <div class="flex flex-col sm:flex-row items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white mb-2 sm:mb-0">
                    Mis Gastos Pendientes
                </h3>
                <Link :href="route('pagos.index')" class="text-white hover:underline font-semibold">
                Ver Pagos Realizados
                </Link>
            </div>
        </template>

        <!-- CONTENIDO -->
        <div class="py-8 px-4 max-w-7xl mx-auto space-y-8">
            <!-- PRIMERA SECCIÓN: Distribuciones Pendientes -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Distribuciones Pendientes</h2>

                <div v-if="pendientes.length === 0" class="text-gray-600">
                    No tienes gastos pendientes.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm text-gray-700">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3">Gasto Común</th>
                                <th class="px-4 py-3">Fecha Venc.</th>
                                <th class="px-4 py-3">Monto Asignado</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(group, idGasto) in groupedPendientes" :key="idGasto">
                                <!-- Fila principal -->
                                <tr @click="toggleGroup(idGasto)" class="cursor-pointer bg-gray-100 border-b">
                                    <td class="px-4 py-3 font-semibold">
                                        {{ group[0]?.detalle_gasto_comun.gastos_comune?.descripcion ?? 'Sin gasto' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ formatDate(group[0]?.detalle_gasto_comun.gastos_comune?.fecha_vencimiento) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{group.reduce((sum, item) => sum + parseFloat(item.monto_asignado),
                                        0).toFixed(2) }}
                                    </td>

                                    <!-- Estado -->
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded text-xs font-semibold" :class="{
                                            'bg-yellow-200 text-yellow-800': getEstadoGasto(idGasto) === 'Pendiente',
                                            'bg-blue-200 text-blue-800': getEstadoGasto(idGasto) === 'Enviado',
                                            'bg-red-200 text-red-800': getEstadoGasto(idGasto) === 'Rechazado'
                                        }">
                                            {{ getEstadoGasto(idGasto) }}
                                        </span>
                                        <!-- Motivo de rechazo (opcional) -->
                                        <div v-if="
                                            paymentsByGasto[idGasto]?.estado === 'Rechazado' &&
                                            paymentsByGasto[idGasto]?.observacion_admin
                                        " class="text-sm text-red-600 mt-1">
                                            <strong>Motivo:</strong> {{ paymentsByGasto[idGasto].observacion_admin }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-blue-600">
                                        <button
                                            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-800 focus:outline-none"
                                            @click.stop="openUploadModal(idGasto)">
                                            {{
                                                getEstadoGasto(idGasto) === 'Rechazado'
                                                    ? 'Corregir Comprobante'
                                                    : 'Subir Comprobante'
                                            }}
                                        </button>
                                        <span class="ml-2">
                                            {{ expandedGroups[idGasto] ? '− Detalles' : '+ Detalles' }}
                                        </span>
                                    </td>
                                </tr>

                                <!-- Fila expandida con detalles -->
                                <tr v-if="expandedGroups[idGasto]">
                                    <td colspan="5" class="px-4 py-3">
                                        <div class="border border-gray-200 rounded p-3">
                                            <table class="min-w-full text-sm">
                                                <thead class="bg-gray-50">
                                                    <tr>

                                                        <th class="px-2 py-1">Detalle</th>
                                                        <th class="px-2 py-1">Extensión</th>
                                                        <th class="px-2 py-1">Porcentaje</th>
                                                        <th class="px-2 py-1">Monto Asignado</th>
                                                        <th class="px-2 py-1">Archivo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="item in group" :key="item.id_distribucion"
                                                        class="hover:bg-gray-50 border-b">

                                                        <td class="px-2 py-1">
                                                            {{ item.detalle_gasto_comun.descripcion_detalle || 'N/A' }}
                                                            <div class="text-xs text-gray-500">
                                                                {{ item.detalle_gasto_comun.tipo_gasto_comun?.nombre ??
                                                                    'Gasto' }}
                                                            </div>
                                                        </td>
                                                        <td class="px-2 py-1">
                                                            <template v-if="item.id_extencion && item.extencion">
                                                                <span class="font-semibold">
                                                                    {{ item.extencion.nombre }}
                                                                </span>
                                                                <br />
                                                                <span v-if="item.extencion.usuarios?.length">
                                                                    Ocupantes:
                                                                    <span v-for="(u, idxU) in item.extencion.usuarios"
                                                                        :key="u.id">
                                                                        {{ u.name }}{{ u.apellidos ? ' ' + u.apellidos :
                                                                            '' }}
                                                                        <span v-if="
                                                                            idxU <
                                                                            item.extencion.usuarios.length - 1
                                                                        ">, </span>
                                                                    </span>
                                                                </span>
                                                                <span v-else class="text-gray-400">
                                                                    (sin usuarios)
                                                                </span>
                                                            </template>
                                                            <template v-else>
                                                                <span class="text-gray-400">
                                                                    No aplica
                                                                </span>
                                                            </template>
                                                        </td>
                                                        <td class="px-2 py-1">
                                                            {{ getPercentage(item) }}
                                                        </td>
                                                        <td class="px-2 py-1">
                                                            {{ item.monto_asignado }}
                                                        </td>
                                                        <td class="px-2 py-1">
                                                            <template v-if="item.archivo_consumo">
                                                                <button
                                                                    class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 focus:outline-none"
                                                                    @click="openViewArchivoModal(item.archivo_consumo)">
                                                                    Ver </button>
                                                            </template>
                                                            <template v-else>
                                                                -
                                                            </template>
                                                        </td>
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

            <!-- SEGUNDA SECCIÓN: Resumen e Histórico (opcional) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h2 class="text-2xl font-bold mb-4">Resumen de Gastos Comunes</h2>
                    <div class="mb-4">
                        <label for="chartFilter" class="block text-sm font-medium text-gray-700">
                            Filtrar por:
                        </label>
                        <select id="chartFilter" v-model="selectedChartFilter"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option v-for="option in chartFilterOptions" :key="option.value" :value="option.value">
                                {{ option.text }}
                            </option>
                        </select>
                    </div>
                    <div v-if="gastosPorMes.length === 0" class="text-gray-600">
                        No hay datos de gastos comunes.
                    </div>
                    <div v-else>
                        <DashboardChart :gastosPorMes="gastosPorMes" />
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h2 class="text-2xl font-bold mb-4">Histórico de Gastos por Servicio</h2>
                    <div v-if="historicalData.length === 0" class="text-gray-600">
                        No hay datos históricos disponibles.
                    </div>
                    <div v-else>
                        <HistoricalChart :historicalData="historicalData" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para subir/corregir comprobante -->
        <UploadComprobanteModal :visible="showModal" @update:visible="showModal = $event" :idGasto="modalData.idGasto"
            :paymentId="modalData.paymentId" :monto="modalData.monto" :fechaPago="modalData.fechaPago" />

        <!-- Modal para visualizar archivo de medición consumo -->
        <ViewArchivoModal :visible="showViewArchivoModal" @update:visible="showViewArchivoModal = $event"
            :archivoUrl="viewArchivoData.archivoUrl" />
    </AuthenticatedLayout>
</template>

<style scoped>
.cursor-pointer:hover {
    background-color: #f5f5f5;
}
</style>
