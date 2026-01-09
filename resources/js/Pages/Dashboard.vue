<template>
    <AuthenticatedLayout>
        <!-- Encabezado del Dashboard -->
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Administración de Pagos</h2>
        </template>

        <div class="py-12 container mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Contenedor principal en 2 filas (flex-col) -->
            <div class="flex flex-col gap-6">
                <!-- Solo si es superadmin -->
                <div v-if="isSuperadmin" class="bg-white shadow-sm sm:rounded-lg p-6">
                    <label for="condominio" class="block text-gray-700 font-bold mb-2">Seleccionar Condominio</label>
                    <select id="condominio" v-model="selectedIdCondominio" @change="onCondominioChange"
                        class="form-select mt-1 block w-full">
                        <option disabled value="">Seleccione un condominio</option>
                        <option v-for="c in condominios" :key="c.id_condominio" :value="c.id_condominio">
                            {{ c.nombre }}
                        </option>
                    </select>
                </div>
                <!-- Fila 1: 3 Gráficos en una sola fila (flex-row) -->
                <div class="flex flex-col md:flex-row md:space-x-4 gap-4">
                    <!-- Gráfico de Barras -->
                    <div class="flex-1 bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4">Estadísticas de Gastos</h3>
                        <apexchart type="bar" height="350" :options="chartOptionsMes" :series="seriesDataMes"
                            class="w-full">
                        </apexchart>
                    </div>

                    <!-- Gráfico Donut: Gastos por Servicio -->
                    <div class="flex-1 bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4">Gastos por Servicio</h3>
                        <apexchart type="donut" height="250" :options="chartOptionsServicios" :series="seriesServicios"
                            class="w-full">
                        </apexchart>
                    </div>

                    <!-- Gráfico Pie: Gastos por Torre -->
                    <div class="flex-1 bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4">Gastos por Torre</h3>
                        <apexchart type="pie" height="250" :options="chartOptionsTorres" :series="seriesTorres"
                            class="w-full">
                        </apexchart>
                    </div>
                </div>


                <!-- Fila 2: Filtro + Mensaje éxito + Tabla -->
                <div class="flex flex-col gap-4">
                    <!-- Filtro por Período -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <label for="periodo" class="block text-gray-700 font-bold mb-2">
                            Filtrar por Período
                        </label>
                        <select id="periodo" v-model="selectedIdGasto" @change="onPeriodoChange"
                            class="form-select mt-1 block w-full">
                            <option value="0">Todos los períodos (últ. 6 meses)</option>
                            <option v-for="p in periodos" :key="p.id_gasto" :value="p.id_gasto">
                                {{ p.descripcion }} - Monto: {{ p.monto_total }} - Vence: {{
                                    formatDate(p.fecha_vencimiento) }}
                            </option>
                        </select>
                    </div>

                    <!-- Mensaje éxito con ícono -->
                    <transition name="fade">
                        <div v-if="successMessage"
                            class="flex items-center bg-green-100 text-green-800 px-4 py-2 rounded mb-4 justify-center">
                            <CheckCircleIcon class="h-5 w-5 mr-2" />
                            {{ successMessage }}
                        </div>
                    </transition>

                    <!-- Tabla de Distribuciones -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4">Distribuciones por Unidad</h3>
                        <div class="overflow-x-auto">

                            <!-- Contenedor scrollable con altura máxima de 230px -->
                            <div class="max-h-[300px] overflow-y-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <!-- Columna Usuario -->
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Usuario
                                            </th>
                                            <!-- Columna Torre (si deseas mostrar el edificio/torre) -->
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Torre
                                            </th>
                                            <!-- Columna Unidad -->
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Unidad
                                            </th>
                                            <!-- Columna Monto Asignado -->
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Asignado
                                            </th>
                                            <!-- Columna Monto Pagado (opcional) -->
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Pagado
                                            </th>
                                            <!-- Columna Estado -->
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Estado
                                            </th>
                                            <!-- Ver Archivo -->
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Archivo
                                            </th>
                                            <!-- Validar -->
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Validar
                                            </th>
                                            <!-- Rechazar -->
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Rechazar
                                            </th>
                                            <!-- Detalles (Expandir/Contraer) -->
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Detalles
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <!-- Recorremos grupos por unidad -->
                                        <template v-for="grupo in grupos" :key="grupo.group_key">
                                            <tr class="transition-colors duration-150 hover:bg-gray-100">
                                                <!-- Usuario -->
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    {{ grupo.usuario
                                                        ? grupo.usuario.name + ' ' + grupo.usuario.apellidos
                                                        : 'Sin asignar' }}
                                                </td>

                                                <!-- Torre (si la tienes en tu data; si no, usa 'N/A') -->
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    {{ grupo.torre_nombre || 'N/A' }}
                                                </td>

                                                <!-- Unidad -->
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    {{ grupo.unidad_nombre }}
                                                </td>

                                                <!-- Monto Asignado -->
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    {{ grupo.total_asignado }}
                                                </td>

                                                <!-- Monto Pagado (opcional) -->
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    {{ grupo.total_pagado || 0 }}
                                                </td>

                                                <!-- Estado -->
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    <span class="px-2 py-1 rounded text-xs font-semibold"
                                                        :class="getBadgeClass(grupo.estado)">
                                                        {{ grupo.estado }}
                                                    </span>
                                                </td>

                                                <!-- Ver Archivo (columna individual) -->
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    <button
                                                        v-if="grupo.distribuciones[0] && grupo.distribuciones[0].archivo"
                                                        @click="openFileModal(grupo.distribuciones[0].archivo)"
                                                        class="flex items-center bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded transition">
                                                        <EyeIcon class="h-4 w-4 mr-1" />
                                                        Ver
                                                    </button>
                                                </td>

                                                <!-- Validar (Aceptar) -->
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    <button v-if="grupo.distribuciones[0]
                                                        && grupo.distribuciones[0].pago_id
                                                        && grupo.estado !== 'Aceptado'"
                                                        @click="openConfirmationModal(grupo.distribuciones[0].pago_id, false)"
                                                        class="flex items-center bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded transition">
                                                        <CheckCircleIcon class="h-4 w-4 mr-1" />
                                                        Validar
                                                    </button>
                                                </td>

                                                <!-- Rechazar -->
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    <button v-if="grupo.distribuciones[0]
                                                        && grupo.distribuciones[0].pago_id
                                                        && grupo.estado !== 'Aceptado'"
                                                        @click="openConfirmationModal(grupo.distribuciones[0].pago_id, true)"
                                                        class="flex items-center bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded transition">
                                                        <XCircleIcon class="h-4 w-4 mr-1" />
                                                        Rechazar
                                                    </button>
                                                </td>

                                                <!-- Botón Expandir/Contraer Detalles -->
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    <button @click="toggleDetails(grupo.group_key)"
                                                        class="flex items-center bg-gray-300 hover:bg-gray-400 text-gray-800 px-2 py-1 rounded transition">
                                                        <template v-if="detailsShown[grupo.group_key]">
                                                            <MinusIcon class="h-4 w-4 mr-1" />
                                                            Ocultar
                                                        </template>
                                                        <template v-else>
                                                            <PlusIcon class="h-4 w-4 mr-1" />
                                                            Detalles
                                                        </template>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Detalles expandibles -->
                                            <transition name="slide-fade">
                                                <tr v-if="detailsShown[grupo.group_key]" class="bg-gray-50">
                                                    <!-- colspan = total de columnas -->
                                                    <td colspan="11" class="px-4 py-2">
                                                        <h4 class="font-semibold mb-2">Detalles de Distribución:</h4>
                                                        <div class="overflow-x-auto">
                                                            <table class="min-w-full">
                                                                <thead>
                                                                    <tr>
                                                                        <th
                                                                            class="text-left text-xs font-medium text-gray-500">
                                                                            Detalle
                                                                        </th>
                                                                        <th
                                                                            class="text-left text-xs font-medium text-gray-500">
                                                                            Monto
                                                                            Asignado</th>
                                                                        <th
                                                                            class="text-left text-xs font-medium text-gray-500">
                                                                            Fecha
                                                                            Vencimiento</th>
                                                                        <th
                                                                            class="text-left text-xs font-medium text-gray-500">
                                                                            Estado
                                                                        </th>
                                                                        <th
                                                                            class="text-left text-xs font-medium text-gray-500">
                                                                            Observación Admin</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr v-for="dist in grupo.distribuciones"
                                                                        :key="dist.id_distribucion"
                                                                        class="transition-colors duration-150 hover:bg-gray-100">
                                                                        <td class="py-1">
                                                                            {{ dist.descripcion || dist.servicio || '-' }}

                                                                        </td>
                                                                        <td class="py-1">
                                                                            {{ dist.monto_asignado }}
                                                                        </td>
                                                                        <td class="py-1">
                                                                            {{ formatDate(dist.fecha_vencimiento) }}
                                                                        </td>
                                                                        <td class="py-1">
                                                                            <span
                                                                                class="px-2 py-1 rounded text-xs font-semibold"
                                                                                :class="getBadgeClass(dist.estado)">
                                                                                {{ dist.estado }}
                                                                            </span>
                                                                        </td>
                                                                        <td class="py-1">
                                                                            <span class="text-sm text-gray-600">
                                                                                {{ dist.observacion_admin || '-' }}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </transition>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>


                </div>
            </div>
        </div>

        <!-- Modal para Visualizar Archivo -->
        <transition name="modal-fade">
            <div v-if="showFileModal"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div
                    class="bg-white p-6 rounded-lg w-11/12 md:w-3/4 lg:w-1/2 transform transition-all duration-300 scale-95">
                    <h3 class="text-lg font-bold mb-4">Comprobante</h3>
                    <div v-if="currentFileUrl">
                        <!-- Imagen -->
                        <img v-if="/\.(jpg|jpeg|png|gif)$/i.test(currentFileUrl)" :src="currentFileUrl"
                            class="w-full h-auto object-contain" alt="Comprobante" />
                        <!-- PDF u otro -->
                        <iframe v-else :src="currentFileUrl" class="w-full h-96"></iframe>
                    </div>
                    <button @click="closeFileModal"
                        class="mt-4 px-4 py-2 bg-gray-700 text-white rounded transition hover:bg-gray-800">
                        Cerrar
                    </button>
                </div>
            </div>
        </transition>

        <!-- Modal de Confirmación para Validar/Rechazar Pago -->
        <transition name="modal-fade">
            <div v-if="showConfirmationModal"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white p-6 rounded-lg w-11/12 md:w-1/2 transform transition-all duration-300 scale-95">
                    <h3 class="text-lg font-bold mb-4">Confirmar Acción</h3>
                    <p>¿Deseas <strong>{{ isRejecting ? 'rechazar' : 'validar' }}</strong> este pago?</p>

                    <!-- Campo de Observación (Solo si es rechazo) -->
                    <div v-if="isRejecting" class="mt-4">
                        <label for="observacion_admin" class="block text-gray-700 font-semibold mb-1">
                            Observación (motivo de rechazo)
                        </label>
                        <textarea v-model="observacionAdmin" id="observacion_admin" rows="3"
                            class="w-full border rounded p-2">
                </textarea>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button @click="closeConfirmationModal"
                            class="px-4 py-2 bg-gray-300 rounded transition hover:bg-gray-400">
                            Cancelar
                        </button>
                        <!-- Botón Rechazar -->
                        <button v-if="isRejecting" @click="confirmValidation('rechazado')"
                            class="px-4 py-2 bg-red-600 text-white rounded transition hover:bg-red-700">
                            Rechazar
                        </button>
                        <!-- Botón Aceptar -->
                        <button v-else @click="confirmValidation('aceptado')"
                            class="px-4 py-2 bg-green-600 text-white rounded transition hover:bg-green-700">
                            Aceptar
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Iconos de Heroicons
import {
    EyeIcon,
    CheckCircleIcon,
    MinusIcon,
    PlusIcon,
    XCircleIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
    periodos: Array,
    selectedIdGasto: Number,
    selectedCondominio: Number,
    isSuperadmin: Boolean,
    condominios: Array,
    grupos: Array,
    gastosPorMes: Array,
    gastosPorServicio: Array,
    gastosPorTorre: Array,
    gastosPorGastoComun: Array,
});
const selectedIdCondominio = ref(props.selectedCondominio);
console.log(props);
// Reactive states
const selectedIdGasto = ref(props.selectedIdGasto);
const grupos = ref(props.grupos || []);
const detailsShown = reactive({});
const successMessage = ref('');

// -------------------------------------
// Campos para rechazar o aceptar con observación
// -------------------------------------
const showFileModal = ref(false);
const currentFileUrl = ref('');
const showConfirmationModal = ref(false);
const isRejecting = ref(false);
const observacionAdmin = ref('');
const currentPaymentId = ref(null);

// -------------------------------------
// Gráficos
// -------------------------------------
import apexchart from 'vue3-apexcharts';
const seriesDataMes = ref([]);
const chartOptionsMes = ref({
    chart: {
        id: 'chart-gastos-mes',
        type: 'bar',
        toolbar: { show: true },
        foreColor: '#333',
        animations: { enabled: true, speed: 800 },
    },
    plotOptions: {
        bar: { columnWidth: '45%', borderRadius: 4, distributed: false },
    },
    dataLabels: { enabled: false },
    xaxis: { categories: [], labels: { rotate: -45, maxHeight: 80 } },
    yaxis: {
        labels: { formatter: (val) => val.toFixed(2) },
        title: { text: 'Monto' },
    },
    title: {
        text: '',
        align: 'left',
        style: { fontSize: '14px', fontWeight: 'bold' },
    },
    tooltip: { y: { formatter: (val) => val.toFixed(2) } },
});
const seriesServicios = ref([]);
const chartOptionsServicios = ref({
    chart: { type: 'donut' },
    labels: [],
    title: { text: 'Gastos por Servicio', align: 'left', style: { fontSize: '14px', fontWeight: 'bold' } },
    legend: { position: 'bottom' },
});
const seriesTorres = ref([]);
const chartOptionsTorres = ref({
    chart: { type: 'pie' },
    labels: [],
    title: { text: 'Gastos por Torre', align: 'left', style: { fontSize: '14px', fontWeight: 'bold' } },
    legend: { position: 'bottom' },
});

// Observamos cambios en las props para el gráfico de Barras
watch(
    [() => props.gastosPorMes, () => props.gastosPorGastoComun, selectedIdGasto],
    ([newGastosPorMes, newGastosPorGastoComun, newSelectedIdGasto]) => {
        if (newSelectedIdGasto == 0 && newGastosPorGastoComun && newGastosPorGastoComun.length > 0) {
            // Mostrar gráfico por Gasto Común
            seriesDataMes.value = [
                { name: 'Gastos', data: newGastosPorGastoComun.map((item) => parseFloat(item.total)) },
            ];
            chartOptionsMes.value.xaxis.categories = newGastosPorGastoComun.map((item) => item.gasto);
            chartOptionsMes.value.title.text = 'Gastos por Gasto Común';
        } else {
            // Mostrar gráfico por Mes
            seriesDataMes.value = [
                { name: 'Gastos', data: newGastosPorMes.map((item) => parseFloat(item.total)) },
            ];
            chartOptionsMes.value.xaxis.categories = newGastosPorMes.map((item) => item.mes);
            chartOptionsMes.value.title.text = 'Gastos por Mes';
        }
    },
    { deep: true, immediate: true }
);

// Donut (Servicios)
const labelsServicios = ref(props.gastosPorServicio.map((s) => s.servicio));
seriesServicios.value = props.gastosPorServicio.map((s) => parseFloat(s.total));
chartOptionsServicios.value.labels = labelsServicios.value;

watch(
    () => props.gastosPorServicio,
    (newVal) => {
        labelsServicios.value = newVal.map((s) => s.servicio);
        seriesServicios.value = newVal.map((s) => parseFloat(s.total));
        chartOptionsServicios.value.labels = labelsServicios.value;
    },
    { deep: true }
);

// Pie (Torres)
const labelsTorres = ref(props.gastosPorTorre.map((t) => t.torre));
seriesTorres.value = props.gastosPorTorre.map((t) => parseFloat(t.total));
chartOptionsTorres.value.labels = labelsTorres.value;

watch(
    () => props.gastosPorTorre,
    (newVal) => {
        labelsTorres.value = newVal.map((t) => t.torre);
        seriesTorres.value = newVal.map((t) => parseFloat(t.total));
        chartOptionsTorres.value.labels = labelsTorres.value;
    },
    { deep: true }
);

// -------------------------------------
// Funciones de Tabla
// -------------------------------------
function onPeriodoChange() {
    const params = {
        id_gasto: selectedIdGasto.value,
    };
    if (props.isSuperadmin) {
        params.condominio_id = selectedIdCondominio.value;
    }
    Inertia.get(route('dashboard'), params, {
        preserveState: true,
        replace: true,
    });
}
function onCondominioChange() {
    Inertia.get(route('dashboard'), {
        condominio_id: selectedIdCondominio.value,
        id_gasto: 0
    }, {
        preserveState: true,
        replace: true
    });
}
function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const dateObj = new Date(dateStr);
    return isNaN(dateObj) ? 'N/A' : dateObj.toLocaleDateString();
}

function getBadgeClass(state) {
    const s = state.toLowerCase();
    if (s === 'pendiente') return 'bg-yellow-200 text-yellow-800';
    if (s === 'enviado') return 'bg-blue-200 text-blue-800';
    if (s === 'aceptado') return 'bg-green-200 text-green-800';
    if (s === 'rechazado') return 'bg-red-200 text-red-800';
    return 'bg-gray-200 text-gray-800';
}

function toggleDetails(grupoId) {
    detailsShown[grupoId] = !detailsShown[grupoId];

}

// -------------------------------------
// Modal de Archivo
// -------------------------------------
function openFileModal(fileUrl) {
    currentFileUrl.value = import.meta.env.VITE_AWS_URL + "/" + fileUrl;
    showFileModal.value = true;
}
function closeFileModal() {
    showFileModal.value = false;
    currentFileUrl.value = '';
}

// -------------------------------------
// Modal de Confirmación (Validar/Rechazar Pago)
// -------------------------------------
function openConfirmationModal(paymentId, rejecting = false) {
    currentPaymentId.value = paymentId;
    isRejecting.value = rejecting;
    observacionAdmin.value = "";
    showConfirmationModal.value = true;
}
function closeConfirmationModal() {
    showConfirmationModal.value = false;
    currentPaymentId.value = null;
}

function confirmValidation(accion) {
    // accion = 'aceptado' o 'rechazado'
    axios
        .post(route("admin.pagos.validate"), {
            id_pago: currentPaymentId.value,
            accion,
            observacion_admin: accion === "rechazado" ? observacionAdmin.value : null,
        })
        .then((response) => {
            // Actualizamos localmente
            grupos.value.forEach((grupo) => {
                grupo.distribuciones.forEach((dist) => {
                    if (dist.pago_id === currentPaymentId.value) {
                        dist.estado = response.data.estado; // 'Aceptado' o 'Rechazado'
                        dist.observacion_admin = response.data.observacion_admin;
                    }
                });

                // Reevaluar estado general del grupo
                let estadoGrupo = 'Aceptado';
                for (const dist of grupo.distribuciones) {
                    const s = (dist.estado || '').toLowerCase();
                    if (s === 'pendiente') {
                        estadoGrupo = 'Pendiente';
                        break;
                    } else if (s === 'enviado') {
                        estadoGrupo = 'Enviado';
                    } else if (s === 'rechazado') {
                        estadoGrupo = 'Rechazado';
                    }
                }
                grupo.estado = estadoGrupo;
            });

            successMessage.value =
                accion === "aceptado"
                    ? "Pago validado correctamente"
                    : "Pago rechazado con observación";
            setTimeout(() => {
                successMessage.value = "";
            }, 3000);

            closeConfirmationModal();
        })
        .catch((error) => {
            console.error("Error al validar/rechazar el pago", error);
            successMessage.value = "Ocurrió un error al procesar la acción";
            setTimeout(() => {
                successMessage.value = "";
            }, 3000);
        });
}
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.slide-fade-enter-active {
    transition: all 0.3s ease;
}

.slide-fade-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.slide-fade-leave-active {
    transition: all 0.2s ease;
}

.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
