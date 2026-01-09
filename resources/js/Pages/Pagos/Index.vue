<template>

    <Head title="Pagos Realizados" />

    <AuthenticatedLayout>
        <!-- Cabecera superior -->
        <template #header>
            <div class="flex flex-col sm:flex-row items-center justify-between bg-green-600 p-4 rounded-md shadow-md">
                <h3 class="text-xl font-bold text-white">Pagos Realizados</h3>
                <Link :href="route('pagos.pendientes')" class="mt-2 sm:mt-0 text-white font-semibold hover:underline">
                Ver Pendientes
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-lg sm:rounded-lg p-6">
                    <!-- Filtro de Gasto Común -->
                    <div class="mb-6">
                        <label for="filtroGasto" class="block text-base font-semibold text-gray-700 mb-2">
                            Filtrar por Gasto Común:
                        </label>
                        <select id="filtroGasto" v-model="selectedGasto"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="all">Todos</option>
                            <option v-for="gasto in uniqueGastos" :key="gasto.id_gasto" :value="gasto.id_gasto">
                                {{ gasto.descripcion }}
                            </option>
                        </select>
                    </div>

                    <h2 class="text-xl font-bold text-gray-800 mb-4">Historial de Pagos</h2>
                    <div v-if="filteredPagos.length === 0" class="text-gray-600">
                        No tienes pagos registrados para el gasto seleccionado.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600 border border-gray-200">
                            <thead class="bg-green-50">
                                <tr class="text-gray-700">
                                    <th class="px-3 py-2 border-b">ID Pago</th>
                                    <th class="px-3 py-2 border-b">Gasto Común</th>
                                    <th class="px-3 py-2 border-b">Monto</th>
                                    <th class="px-3 py-2 border-b">Fecha Pago</th>
                                    <th class="px-3 py-2 border-b">Estado</th>
                                    <th class="px-3 py-2 border-b">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="pago in filteredPagos" :key="pago.id_pago">
                                    <!-- Fila principal del pago -->
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-3 py-2 font-medium text-gray-800">{{ pago.id_pago }}</td>
                                        <td class="px-3 py-2">{{ pago.gastos_comune?.descripcion ?? 'N/A' }}</td>
                                        <td class="px-3 py-2">
                                            {{ formatCurrency(pago.monto, pago.gastos_comune?.tipo_moneda) }}
                                        </td>
                                        <td class="px-3 py-2">{{ formatDate(pago.fecha_pago) }}</td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-1 rounded text-xs font-semibold" :class="{
                                                'bg-yellow-200 text-yellow-800': pago.estado === 'Pendiente',
                                                'bg-blue-200 text-blue-800': pago.estado === 'Enviado',
                                                'bg-green-200 text-green-800': pago.estado === 'Aceptado',
                                                'bg-red-200 text-red-800': pago.estado === 'Rechazado'
                                            }">
                                                {{ pago.estado }}
                                            </span>
                                            <div v-if="pago.estado === 'Rechazado' && pago.observacion_admin"
                                                class="text-sm text-red-600 mt-1">
                                                <strong>Motivo:</strong> {{ pago.observacion_admin }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-wrap gap-2">
                                                <!-- Botón para ver sustento adjunto en el pago -->
                                                <button v-if="pago.archivo"
                                                    @click="openFileModal(getFileUrl(pago.archivo))"
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition duration-200">
                                                    Ver sustento
                                                </button>
                                                <!-- Botón para generar comprobante (si Aceptado) -->
                                                <button v-if="pago.estado === 'Aceptado'"
                                                    @click="generateReceipt(pago.id_pago)"
                                                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs transition duration-200">
                                                    Generar Comprobante
                                                </button>
                                                <!-- Botón para ver/ocultar detalles del pago -->
                                                <button @click="toggleRow(pago.id_pago)"
                                                    class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs transition duration-200">
                                                    {{ expandedRows[pago.id_pago] ? "Ocultar Detalles" : "Ver Detalles"
                                                    }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Fila expandible con detalles del pago -->
                                    <tr v-if="expandedRows[pago.id_pago]" class="bg-gray-50">
                                        <td colspan="6" class="px-4 py-2">
                                            <!-- Información adicional del pago -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                <div>
                                                    <span class="font-semibold text-gray-700">Método de Pago:</span>
                                                    <span class="text-gray-800">{{ pago.metodo_pago }}</span>
                                                </div>
                                                <div>
                                                    <span class="font-semibold text-gray-700">Referencia:</span>
                                                    <span class="text-gray-800">{{ pago.referencia ?? "N/A" }}</span>
                                                </div>
                                                <div>
                                                    <span class="font-semibold text-gray-700">Observación:</span>
                                                    <span class="text-gray-800">{{ pago.observacion ?? "N/A" }}</span>
                                                </div>
                                                <div>
                                                    <span class="font-semibold text-gray-700">Fecha de Registro:</span>
                                                    <span class="text-gray-800">{{ formatDate(pago.created_at) }}</span>
                                                </div>
                                            </div>

                                            <!-- Detalles asignados (filtrados por el gasto común) -->
                                            <div
                                                v-if="pago.gastos_comune && pago.gastos_comune.detalle_gasto_comuns?.length">
                                                <h4 class="text-base font-bold text-gray-700 mt-2 mb-2">
                                                    Detalles Asignados a Ti
                                                </h4>
                                                <table
                                                    class="min-w-full text-sm text-left text-gray-600 border border-gray-200">
                                                    <thead class="bg-green-50">
                                                        <tr>
                                                            <th class="px-3 py-2 border-b">Servicio</th>
                                                            <th class="px-3 py-2 border-b">Descripción</th>
                                                            <th class="px-3 py-2 border-b">Monto Detalle</th>
                                                            <th class="px-3 py-2 border-b">Tu Monto Asignado</th>
                                                            <th class="px-3 py-2 border-b">Archivo</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Recorremos cada detalle y, en éste, cada distribución asignada
                                                        <tr v-for="detalle in pago.gastos_comune.detalle_gasto_comuns"
                                                            :key="detalle.id_detalle"> -->
                                                        <tr v-for="detalle in pago.gastos_comune.detalle_gasto_comuns.filter(d => d.distribucion_gastos?.length > 0)"
                                                            :key="detalle.id_detalle">

                                                            <td class="px-3 py-2 border-b">
                                                                {{ detalle.tipo_gasto_comun?.nombre ?? 'N/A' }}
                                                            </td>
                                                            <td class="px-3 py-2 border-b">
                                                                {{ detalle.descripcion_detalle }}
                                                            </td>
                                                            <td class="px-3 py-2 border-b">
                                                                <!--  {{ formatCurrency(detalle.monto_detalle, pago.gastos_comune?.tipo_moneda) }}-->
                                                                {{ formatCurrency(detalle.monto_detalle,
                                                                    getMoneda(pago)) }}
                                                            </td>
                                                            <td class="px-3 py-2 border-b">
                                                                <div v-if="detalle.distribucion_gastos?.length">
                                                                    <div v-for="dist in detalle.distribucion_gastos"
                                                                        :key="dist.id_distribucion">
                                                                        {{ formatCurrency(dist.monto_asignado,
                                                                            pago.gastos_comune?.tipo_moneda) }}
                                                                    </div>
                                                                </div>
                                                                <div v-else>
                                                                    {{ formatCurrency(0,
                                                                        pago.gastos_comune?.tipo_moneda) }}
                                                                </div>
                                                            </td>
                                                            <td class="px-3 py-2 border-b">
                                                                <template v-if="detalle.distribucion_gastos?.length">
                                                                    <div v-for="dist in detalle.distribucion_gastos"
                                                                        :key="dist.id_distribucion">
                                                                        <template v-if="dist.archivo">
                                                                            <button
                                                                                class="bg-green-500 text-white px-2 py-1 rounded text-xs hover:bg-green-600"
                                                                                @click="openViewArchivoModal(dist.archivo)">
                                                                                Ver
                                                                            </button>
                                                                        </template>
                                                                        <template v-else>
                                                                            -
                                                                        </template>
                                                                    </div>
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
            </div>
        </div>

        <!-- Modal para visualizar comprobante subido -->
        <transition name="modal-fade">
            <div v-if="showFileModal"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div
                    class="bg-white p-6 rounded-lg w-11/12 md:w-3/4 lg:w-1/2 shadow-lg transform transition-all duration-300 scale-95">
                    <h3 class="text-xl font-bold mb-4">Comprobante</h3>
                    <div v-if="currentFileUrl">
                        <!-- Si es imagen -->
                        <img v-if="/\.(jpg|jpeg|png|gif)$/i.test(currentFileUrl)" :src="currentFileUrl"
                            alt="Comprobante" class="w-full h-auto object-contain rounded" />
                        <!-- PDF u otro -->
                        <iframe v-else :src="currentFileUrl" class="w-full h-96 rounded"></iframe>
                    </div>
                    <button @click="closeFileModal"
                        class="mt-6 w-full bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg transition duration-200">
                        Cerrar
                    </button>
                </div>
            </div>
        </transition>
        <!-- Modal para visualizar archivo desde distribucion -->
        <ViewArchivoModal :visible="showViewArchivoModal" @update:visible="showViewArchivoModal = $event"
            :archivoUrl="viewArchivoData.archivoUrl" />

    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, reactive } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ViewArchivoModal from "@/Components/ViewArchivoModal.vue";


const page = usePage();
const pagos = page.props.pagos || [];


//para mostrar modal:
const showViewArchivoModal = ref(false);
const viewArchivoData = reactive({
    archivoUrl: "",
});
function openViewArchivoModal(path) {
    const fullUrl = getFileUrl(path);
    viewArchivoData.archivoUrl = fullUrl;
    showViewArchivoModal.value = true;
}


// Filtro por Gasto Común
const selectedGasto = ref("all");
const uniqueGastos = computed(() => {
    const map = {};
    pagos.forEach((pago) => {
        if (pago.gastos_comune) {
            map[pago.gastos_comune.id_gasto] = pago.gastos_comune;
        }
    });
    return Object.values(map);
});
const filteredPagos = computed(() => {
    if (selectedGasto.value === "all") return pagos;
    return pagos.filter(
        (pago) =>
            pago.gastos_comune && pago.gastos_comune.id_gasto == selectedGasto.value
    );
});

// Control para filas expandidas
const expandedRows = reactive({});

function toggleRow(pagoId) {
    expandedRows[pagoId] = !expandedRows[pagoId];
}

// Función para construir la URL completa de un archivo
function getFileUrl(path) {
    if (!path) return null;
    return import.meta.env.VITE_AWS_URL + "/" + path;
}

// Modal para visualizar comprobante
const showFileModal = ref(false);
const currentFileUrl = ref("");
function openFileModal(fileUrl) {
    currentFileUrl.value = fileUrl;
    showFileModal.value = true;
}
function closeFileModal() {
    showFileModal.value = false;
    currentFileUrl.value = "";
}

// Formateo de fecha
function formatDate(dateStr) {
    if (!dateStr) return "N/A";
    const d = new Date(dateStr);
    return isNaN(d) ? "N/A" : d.toLocaleDateString();
}

// Formateo de moneda según el tipo (Soles, USD, etc.)
function formatCurrency(amount, currencyType) {
    if (!amount) amount = 0;
    let symbol = "";

    switch (currencyType) {
        case "Soles":
        case "soles":
            symbol = "S/";
            break;
        case "Dolares":
        case "dolares":
        case "USD":
            symbol = "$";
            break;
        case "Euros":
        case "euros":
        case "EUR":
            symbol = "€";
            break;
        default:
            symbol = ""; // Por si viene algo no esperado
    }

    return `${symbol} ${parseFloat(amount).toFixed(2)}`;
}


// Función para generar comprobante en PDF
function generateReceipt(pagoId) {
    if (typeof window !== "undefined" && window.open) {
        const url = `/dashboard/pagos/${pagoId}/comprobante`;
        window.open(url, "_blank");
    } else {
        console.error("window.open no está disponible en este entorno");
    }
}

function getMoneda(pago) {
    console.log(pago);
    return pago?.gastos_comune?.tipo_moneda ?? "Soles";
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
</style>
