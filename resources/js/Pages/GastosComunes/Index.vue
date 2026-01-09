<script setup>
/* ==============================
   IMPORTS INICIALES
============================== */
import { computed, ref, onMounted, watch, reactive } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import axios from "axios";

// Layout principal
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

// Componentes
import Pagination from "@/Components/Pagination.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import FileInput from "@/Components/FileInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
// 1) IMPORTA INERTIA AQUÍ para usar Inertia.reload()
import { Inertia } from "@inertiajs/inertia";
// Iconos
import {
    MagnifyingGlassIcon,
    CalendarIcon,
    CurrencyDollarIcon,
    ClipboardDocumentListIcon,
    CheckBadgeIcon,
    PlusIcon,
} from "@heroicons/vue/24/solid";

// dayjs para formatear fechas
import dayjs from "dayjs";
import utc from "dayjs/plugin/utc";
import localizedFormat from "dayjs/plugin/localizedFormat";
import "dayjs/locale/es";
dayjs.extend(utc);
dayjs.extend(localizedFormat);
dayjs.locale("es");

/* ==============================
   PROPS
============================== */
const props = defineProps({
    gastos: Object, // paginación de gastos
    filters: Object, // filtros
    tipos: Array, // tipos de gasto
    edificios: Array, // torres
    unidades: Array, // unidades
});

/* ==============================
   MENSAJES FLASH & ERRORES
============================== */
const page = usePage();
const flashSuccess = ref(page.props.flash?.success || "");
const flashError = ref(page.props.flash?.error || "");
const errorMessage = ref("");

onMounted(() => {
    if (flashSuccess.value) {
        setTimeout(() => (flashSuccess.value = ""), 4000);
    }
    if (flashError.value) {
        setTimeout(() => (flashError.value = ""), 4000);
    }
    watch(errorMessage, (val) => {
        if (val) setTimeout(() => (errorMessage.value = ""), 4000);
    });
});

/* ==============================
   DATOS Y FORMATEOS
============================== */
const gastosData = computed(() => props.gastos?.data ?? []);

/** Formato de fecha con dayjs **/
function formatDate(d) {
    if (!d) return "";
    return dayjs.utc(d).format("DD/MM/YYYY");
}

/** Formato moneda: si el gasto es en Dólares, antepone $, en Soles => S/ **/
function formatMoneda(monto, tipoMoneda) {
    return tipoMoneda === "Dolares" ? `$${monto}` : `S/ ${monto}`;
}

/** Colores de estado **/
function stateColor(st) {
    if (st === "Pendiente") return "bg-yellow-500";
    if (st === "Pagado") return "bg-green-500";
    if (st === "Conciliado") return "bg-blue-500";
    return "bg-gray-500";
}

/* ==============================
   BÚSQUEDA LOCAL
============================== */
const searchQuery = ref("");
const filteredGastos = computed(() => {
    if (!searchQuery.value) return gastosData.value;
    const q = searchQuery.value.toLowerCase();
    return gastosData.value.filter((g) => {
        const desc = g.descripcion?.toLowerCase() || "";
        const montoStr = formatMoneda(
            g.monto_total,
            g.tipo_moneda
        ).toLowerCase();
        const fechaP = formatDate(g.fecha_periodo).toLowerCase();
        const fechaV = g.fecha_vencimiento
            ? formatDate(g.fecha_vencimiento).toLowerCase()
            : "";
        const st = g.estado_pago?.toLowerCase() || "";
        return (
            desc.includes(q) ||
            montoStr.includes(q) ||
            fechaP.includes(q) ||
            fechaV.includes(q) ||
            st.includes(q)
        );
    });
});
function clearSearch() {
    searchQuery.value = "";
}

/* ==============================
   EXPANDIR/CERRAR FILAS
============================== */
const expandedExpenses = ref({});
function toggleDetails(id) {
    expandedExpenses.value[id] = !expandedExpenses.value[id];
}

/* ==============================
   MODAL DE CONFIRMACIÓN
============================== */
const modalVisible = ref(false);
const modalMessage = ref("");
const modalAction = ref("");
const selectedGastoId = ref(null);

function openModal(action, gastoId) {
    selectedGastoId.value = gastoId;
    modalAction.value = action;
    if (action === "distribution") {
        modalMessage.value =
            "Se procederá a distribuir este Gasto. ¿Deseas continuar?";
    } else if (action === "validate") {
        modalMessage.value =
            "Se procederá a validar este Gasto. ¿Deseas continuar?";
    } else if (action === "delete") {
        modalMessage.value = "¿Eliminar Gasto Común?";
    }
    modalVisible.value = true;
}

async function onConfirm() {
    try {
        if (modalAction.value === "distribution") {
            // Llamamos a la previsualización final
            await openFinalDistributionModal(selectedGastoId.value);
        } else if (modalAction.value === "validate") {
            const resp = await axios.post(
                `/dashboard/GastosComunes/${selectedGastoId.value}/validateDistribution`
            );
            if (!resp.data.success) {
                errorMessage.value = resp.data.message;
            }
        } else if (modalAction.value === "delete") {
            const resp = await axios.post(
                `/dashboard/GastosComunes/${selectedGastoId.value}`,
                {
                    _method: "DELETE",
                }
            );
            if (resp.data.success || resp.request.responseURL) {
                const idx = gastosData.value.findIndex(
                    (g) => g.id_gasto === selectedGastoId.value
                );
                if (idx !== -1) {
                    gastosData.value.splice(idx, 1);
                }
            } else {
                errorMessage.value = resp.data.message;
            }
        }
    } catch (err) {
        errorMessage.value = err.response?.data?.message || err.message;
    } finally {
        modalVisible.value = false;
    }
}
function onCancel() {
    modalVisible.value = false;
}

/* ==============================
   MODAL CONSUMOS PENDIENTES
============================== */
const showConsumptionModal = ref(false);
const pendingConsumptions = ref([]);
const consumptionLinks = ref("");

function openPendingConsumption(gasto) {
    selectedGastoId.value = gasto.id_gasto;
    loadPendingConsumptions(gasto.id_gasto, 1);
}

/**
 * Carga la paginación (para ver los consumos en varias páginas),
 * pero en el back se ignora la paginación al “distribuir”.
 */
function loadPendingConsumptions(gastoId, page = 1) {
    axios
        .get(
            `/dashboard/GastosComunes/${gastoId}/previewConsumption?page=${page}`
        )
        .then((res) => {
            if (res.data.success) {
                pendingConsumptions.value = res.data.data || [];
                consumptionLinks.value = res.data.links || "";
                showConsumptionModal.value = true;
            } else {
                errorMessage.value =
                    res.data.message || "Error al obtener consumos pendientes.";
            }
        })
        .catch((err) => {
            errorMessage.value = err.response?.data?.message || err.message;
        });
}

function handleConsumptionPageClick(e) {
    e.preventDefault();
    const url = e.target.getAttribute("href");
    if (!url) return;
    const match = url.match(/page=(\d+)/);
    const pageNumber = match ? match[1] : 1;
    loadPendingConsumptions(selectedGastoId.value, pageNumber);
}

function closeConsumptionModal() {
    showConsumptionModal.value = false;
    pendingConsumptions.value = [];
    consumptionLinks.value = "";
    selectedGastoId.value = null;
}

function applyConsumptionDistribution() {
    if (!selectedGastoId.value) {
        errorMessage.value = "No hay Gasto seleccionado.";
        return;
    }
    if (pendingConsumptions.value.length === 0) {
        errorMessage.value = "No hay consumos pendientes.";
        return;
    }

    // Se asume que en el back se ignora la paginación y se distribuyen todos.
    axios
        .post(
            `/dashboard/GastosComunes/${selectedGastoId.value}/distributeConsumption`,
            {
                consumos: pendingConsumptions.value,
            }
        )
        .then((res) => {
            if (res.data.success) {
                flashSuccess.value = res.data.message;
                showConsumptionModal.value = false;
                pendingConsumptions.value = [];
                consumptionLinks.value = "";

                // 2) Llamar a Inertia.reload para refrescar 'gastos'
                Inertia.reload({
                    only: ["gastos"],
                    preserveScroll: true,
                });
            } else {
                errorMessage.value =
                    res.data.message || "Error al distribuir consumos.";
            }
        })
        .catch((err) => {
            errorMessage.value = err.response?.data?.message || err.message;
        });
}

/* ==============================
   MODAL DISTRIBUCIÓN FINAL
============================== */
const showFinalDistributionModal = ref(false);
const finalDistributionData = ref([]);

async function openFinalDistributionModal(gastoId) {
    selectedGastoId.value = gastoId;
    try {
        const res = await axios.get(
            `/dashboard/GastosComunes/${gastoId}/finalDistributionPreview`
        );
        if (res.data.success) {
            if (res.data.hierarchical) {
                finalDistributionData.value = res.data.hierarchical;
            } else {
                finalDistributionData.value = [];
            }
            showFinalDistributionModal.value = true;
        } else {
            errorMessage.value =
                res.data.message || "No se pudo previsualizar la distribución.";
        }
    } catch (err) {
        errorMessage.value = err.response?.data?.message || err.message;
    }
}

function closeFinalDistributionModal() {
    showFinalDistributionModal.value = false;
    finalDistributionData.value = [];
    selectedGastoId.value = null;
}

function confirmFinalDistribution() {
    if (!selectedGastoId.value) {
        errorMessage.value = "No hay Gasto seleccionado.";
        return;
    }
    axios
        .post(
            `/dashboard/GastosComunes/${selectedGastoId.value}/confirmFinalDistribution`
        )
        .then((res) => {
            if (res.data.success) {
                flashSuccess.value = res.data.message;
                showFinalDistributionModal.value = false;
                finalDistributionData.value = [];
            } else {
                errorMessage.value =
                    res.data.message || "Error al confirmar distribución.";
            }
        })
        .catch((err) => {
            errorMessage.value = err.response?.data?.message || err.message;
        });
}

/* ==============================
   MODAL AVANZADO: AGREGAR DETALLES
============================== */
const advancedModalVisible = ref(false);
const currentGasto = ref(null);
const addedDetails = ref([]);

const totalAssigned = computed(() =>
    addedDetails.value.reduce((sum, d) => sum + Number(d.amount), 0)
);
function remainingAmount() {
    return currentGasto.value
        ? Number(currentGasto.value.monto_total) - totalAssigned.value
        : 0;
}

// Banderas de edición local
const isEditingCondominium = ref(false);
const isEditingTower = ref(false);
const isEditingUnit = ref(false);

/** Inputs reactivas para cada panel (Condominio / Torre / Unidad) **/
const condominiumInput = reactive({
    selectedType: null,
    amount: "",
    description: "",
    file: null,
});
const towerInput = reactive({
    selectedType: null,
    amount: "",
    description: "",
    selectedTower: "",
    file: null,
});
const unitInput = reactive({
    selectedType: null,
    amount: "",
    description: "",
    selectedTower: "",
    selectedUnit: "",
    file: null,
});

/** Watchers para limpiar los fields al cambiar selectedType **/
watch(
    () => condominiumInput.selectedType,
    (nv, ov) => {
        if (nv !== ov) {
            condominiumInput.amount = "";
            condominiumInput.description = "";
            condominiumInput.file = null;
        }
    }
);
watch(
    () => towerInput.selectedType,
    (nv, ov) => {
        if (nv !== ov) {
            towerInput.amount = "";
            towerInput.description = "";
            towerInput.file = null;
        }
    }
);
watch(
    () => unitInput.selectedType,
    (nv, ov) => {
        if (nv !== ov) {
            unitInput.amount = "";
            unitInput.description = "";
            unitInput.file = null;
        }
    }
);

/** Abrir modal “Agregar Detalles” y cargar existing details en "addedDetails" **/
function openAdvancedModal(gasto) {
    currentGasto.value = gasto;
    advancedModalVisible.value = true;

    // Excluir los que vengan de consumos:
    addedDetails.value = (gasto.detalle_gasto_comuns || [])
        .filter(d => d.source !== 'consumption')  // <--- la clave
        .map(detail => {
            const scopeLower = (detail.distribution_scope || "").toLowerCase();
            let scopeEsp = "Condominio";
            if (scopeLower === "tower") scopeEsp = "Torre";
            if (scopeLower === "unit") scopeEsp = "Unidad";
            if (scopeLower === "extension") scopeEsp = "Extensión";

            return {
                id: detail.id_detalle,
                scope: scopeEsp,
                tipo: detail.tipo_gasto_comun,
                amount: detail.monto_detalle,
                description: detail.descripcion_detalle,
                tower: detail.targetTorre
                    ? detail.targetTorre.id_edificio
                    : null,
                unit: detail.targetUnidad
                    ? detail.targetUnidad.id_unidad
                    : null,
                file: null,
            };
        });

    resetCondominium();
    resetTower();
    resetUnit();
    isEditingCondominium.value = false;
    isEditingTower.value = false;
    isEditingUnit.value = false;
}

/** Reset fields de cada panel **/
function resetCondominium() {
    condominiumInput.selectedType = null;
    condominiumInput.amount = "";
    condominiumInput.description = "";
    condominiumInput.file = null;
}
function resetTower() {
    towerInput.selectedType = null;
    towerInput.amount = "";
    towerInput.description = "";
    towerInput.selectedTower = "";
    towerInput.file = null;
}
function resetUnit() {
    unitInput.selectedType = null;
    unitInput.amount = "";
    unitInput.description = "";
    unitInput.selectedTower = "";
    unitInput.selectedUnit = "";
    unitInput.file = null;
}

/** Cerrar modal “Agregar Detalles” **/
function cancelAdvancedModal() {
    advancedModalVisible.value = false;
    currentGasto.value = null;
}

/** Editar un item existente en la tabla local “addedDetails” **/
function editDetail(idx) {
    const detail = addedDetails.value[idx];
    if (detail.scope === "Condominio") {
        condominiumInput.selectedType = detail.tipo;
        condominiumInput.amount = detail.amount;
        condominiumInput.description = detail.description;
        condominiumInput.file = detail.file;
        isEditingCondominium.value = true;
    } else if (detail.scope === "Torre") {
        towerInput.selectedType = detail.tipo;
        towerInput.amount = detail.amount;
        towerInput.description = detail.description;
        towerInput.selectedTower = detail.tower;
        towerInput.file = detail.file;
        isEditingTower.value = true;
    } else if (detail.scope === "Unidad") {
        unitInput.selectedType = detail.tipo;
        unitInput.amount = detail.amount;
        unitInput.description = detail.description;
        unitInput.selectedTower = detail.tower;
        unitInput.selectedUnit = detail.unit;
        unitInput.file = detail.file;
        isEditingUnit.value = true;
    }
    // Lo quitamos de la tabla local para que no aparezca duplicado
    addedDetails.value.splice(idx, 1);
}

/* Filtros para los combos */
const limitedTipos = computed(() => props.tipos.slice(0, 10));
const filteredUnitOptions = computed(() => {
    if (!unitInput.selectedTower) return [];
    return props.unidades.filter(
        (u) => u.id_edificio === unitInput.selectedTower
    );
});

/* Errores en cada panel */
const errorCondominium = ref("");
const errorTower = ref("");
const errorUnit = ref("");

/** AGREGAR AL ÁMBITO CONDOMINIO **/
function addCondominiumDetail() {
    errorCondominium.value = "";
    const st = condominiumInput.selectedType;
    if (!st) {
        errorCondominium.value = "Debe seleccionar un tipo para Condominio.";
        return;
    }
    if (!condominiumInput.amount) {
        errorCondominium.value = "Debe ingresar el monto para Condominio.";
        return;
    }
    // Evitar duplicado
    const existe = addedDetails.value.some(
        (d) =>
            d.scope === "Condominio" &&
            String(d.tipo.id_tipo_gasto) === String(st.id_tipo_gasto)
    );
    if (existe) {
        errorCondominium.value =
            "Ya se agregó este tipo de gasto para el Condominio.";
        return;
    }
    addedDetails.value.push({
        scope: "Condominio",
        tipo: st,
        amount: condominiumInput.amount,
        description: condominiumInput.description,
        tower: null,
        unit: null,
        file: condominiumInput.file,
    });
    resetCondominium();
    isEditingCondominium.value = false;
}

/** AGREGAR AL ÁMBITO TORRE **/
function addTowerDetail() {
    errorTower.value = "";
    const st = towerInput.selectedType;
    if (!st) {
        errorTower.value = "Debe seleccionar un tipo para Torre.";
        return;
    }
    if (!towerInput.amount) {
        errorTower.value = "Debe ingresar el monto para Torre.";
        return;
    }
    if (!towerInput.selectedTower) {
        errorTower.value = "Debe seleccionar una torre.";
        return;
    }
    // Evitar duplicado
    const existe = addedDetails.value.some(
        (d) =>
            d.scope === "Torre" &&
            String(d.tipo.id_tipo_gasto) === String(st.id_tipo_gasto) &&
            String(d.tower) === String(towerInput.selectedTower)
    );
    if (existe) {
        errorTower.value = "Ya se agregó este tipo de gasto para esa Torre.";
        return;
    }
    addedDetails.value.push({
        scope: "Torre",
        tipo: st,
        amount: towerInput.amount,
        description: towerInput.description,
        tower: towerInput.selectedTower,
        unit: null,
        file: towerInput.file,
    });
    resetTower();
    isEditingTower.value = false;
}

/** AGREGAR AL ÁMBITO UNIDAD **/
function addUnitDetail() {
    errorUnit.value = "";
    const st = unitInput.selectedType;
    if (!st) {
        errorUnit.value = "Debe seleccionar un tipo para la Unidad.";
        return;
    }
    if (!unitInput.amount) {
        errorUnit.value = "Debe ingresar el monto para la Unidad.";
        return;
    }
    if (!unitInput.selectedTower) {
        errorUnit.value = "Debe seleccionar una torre.";
        return;
    }
    if (!unitInput.selectedUnit) {
        errorUnit.value = "Debe seleccionar una unidad.";
        return;
    }
    // Evitar duplicado
    const existe = addedDetails.value.some(
        (d) =>
            d.scope === "Unidad" &&
            String(d.tipo.id_tipo_gasto) === String(st.id_tipo_gasto) &&
            String(d.tower) === String(unitInput.selectedTower) &&
            String(d.unit) === String(unitInput.selectedUnit)
    );
    if (existe) {
        errorUnit.value = "Ya se agregó este tipo de gasto para esa Unidad.";
        return;
    }
    addedDetails.value.push({
        scope: "Unidad",
        tipo: st,
        amount: unitInput.amount,
        description: unitInput.description,
        tower: unitInput.selectedTower,
        unit: unitInput.selectedUnit,
        file: unitInput.file,
    });
    resetUnit();
    isEditingUnit.value = false;
}

/** Eliminar de la tabla local */
function removeDetail(idx) {
    addedDetails.value.splice(idx, 1);
}

/** Helper para mostrar nombre de Torre y Unidad */
function getEdificioName(id) {
    const e = props.edificios.find((x) => x.id_edificio === id);
    return e ? e.nombre : "";
}
function getUnidadName(id) {
    const u = props.unidades.find((x) => x.id_unidad === id);
    return u ? u.nombre_unidad : "";
}

/** SUBMIT final del modal “Agregar Detalles” **/
async function submitAdvancedDetails() {
    /* if (!addedDetails.value.length) {
         errorMessage.value = "Agregue al menos un detalle.";
         return;
     }*/
    if (!currentGasto.value) {
        errorMessage.value = "No hay gasto activo.";
        return;
    }

    const formData = new FormData();
    formData.append("id_gasto", currentGasto.value.id_gasto);

    addedDetails.value.forEach((d, i) => {
        formData.append(`detalles[${i}][scope]`, d.scope);
        formData.append(
            `detalles[${i}][tipo][id_tipo_gasto]`,
            d.tipo.id_tipo_gasto
        );
        formData.append(`detalles[${i}][amount]`, d.amount);
        formData.append(`detalles[${i}][description]`, d.description || "");
        if (d.scope === "Torre" || d.scope === "Unidad") {
            formData.append(`detalles[${i}][tower]`, d.tower);
        }
        if (d.scope === "Unidad") {
            formData.append(`detalles[${i}][unit]`, d.unit);
        }
        if (d.file) {
            formData.append(`detalles[${i}][file]`, d.file);
        }
        if (d.id) {
            formData.append(`detalles[${i}][id]`, d.id);
        }
    });

    try {
        const resp = await axios.post(
            "/dashboard/detalle_gasto_comun/storeMultiple",
            formData,
            {
                headers: { "Content-Type": "multipart/form-data" },
            }
        );
        if (resp.data.success) {
            advancedModalVisible.value = false;

            // Actualizamos la lista “detalle_gasto_comuns” en el Gasto, localmente
            const gastoX = gastosData.value.find(
                (g) => g.id_gasto === currentGasto.value.id_gasto
            );
            if (gastoX) {
                gastoX.detalle_gasto_comuns = addedDetails.value.map((d) => {
                    // Convertimos “Condominio/Torre/Unidad/Extensión” => distribution_scope
                    let scopeKey = "condominium";
                    if (d.scope === "Torre") scopeKey = "tower";
                    if (d.scope === "Unidad") scopeKey = "unit";
                    if (d.scope === "Extensión") scopeKey = "extension";

                    return {
                        id_detalle: d.id || Math.random(),
                        distribution_scope: scopeKey,
                        monto_detalle: d.amount,
                        descripcion_detalle: d.description,
                        tipo_gasto_comun: d.tipo,
                        targetTorre: d.tower
                            ? {
                                id_edificio: d.tower,
                                nombre: getEdificioName(d.tower),
                            }
                            : null,
                        targetUnidad: d.unit
                            ? {
                                id_unidad: d.unit,
                                nombre_unidad: getUnidadName(d.unit),
                            }
                            : null,
                    };
                });

                // Recalcular el monto total sumando los details
                const newTotal = gastoX.detalle_gasto_comuns.reduce(
                    (acc, dd) => acc + Number(dd.monto_detalle),
                    0
                );
                gastoX.monto_total = newTotal;
            }
            addedDetails.value = [];

            // OPCIONAL: en lugar de sólo manipular localmente,
            // hacemos un reload parcial de 'gastos' para traer datos frescos
            Inertia.reload({
                only: ['gastos'],        // recarga sólo la prop 'gastos' en tu controlador
                preserveScroll: true,    // opcional: evita que la página salte al principio
            });
        } else {
            errorMessage.value = resp.data.message;
            advancedModalVisible.value = true;
        }
    } catch (err) {
        errorMessage.value = err.response?.data?.message || err.message;
        advancedModalVisible.value = true;
    }
}

/** Paneles colapsables en el modal Avanzado (Condominio/Torre/Unidad) **/
const showCondominium = ref(true);
const showTower = ref(false);
const showUnit = ref(false);

function togglePanel(panel) {
    if (panel === "condominium") {
        showCondominium.value = !showCondominium.value;
        if (showCondominium.value) {
            showTower.value = false;
            showUnit.value = false;
        }
    } else if (panel === "tower") {
        showTower.value = !showTower.value;
        if (showTower.value) {
            showCondominium.value = false;
            showUnit.value = false;
        }
    } else if (panel === "unit") {
        showUnit.value = !showUnit.value;
        if (showUnit.value) {
            showCondominium.value = false;
            showTower.value = false;
        }
    }
}
</script>

<template>

    <Head title="Gastos Comunes" />
    <AuthenticatedLayout>
        <!-- ENCABEZADO -->
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h2 class="text-xl font-semibold text-white">
                    Estas en: Gastos Comunes / Lista
                </h2>
                <Link :href="route('gastos_comunes.create')" class="text-white hover:underline font-semibold">
                Crear Gasto Común
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <!-- Mensajes Flash -->
                    <transition name="fade">
                        <div v-if="flashSuccess" class="p-2 bg-green-100 text-green-700 rounded mb-4">
                            {{ flashSuccess }}
                        </div>
                    </transition>
                    <transition name="fade">
                        <div v-if="flashError" class="p-2 bg-red-100 text-red-700 rounded mb-4">
                            {{ flashError }}
                        </div>
                    </transition>

                    <!-- Error AJAX -->
                    <transition name="fade">
                        <div v-if="errorMessage" class="mb-4">
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                                <strong class="font-bold">Error!</strong>
                                <span class="block sm:inline">{{
                                    errorMessage
                                    }}</span>
                            </div>
                        </div>
                    </transition>

                    <!-- Barra de búsqueda -->
                    <div class="mb-6 relative">
                        <label for="search" class="block font-semibold text-gray-700 mb-1 text-sm">
                            Buscar Gastos
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <input id="search" v-model="searchQuery" type="text"
                                placeholder="Buscar por descripción, monto, fecha..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-green-200" />
                            <button v-if="searchQuery" @click="clearSearch"
                                class="absolute right-2 top-2 text-gray-500 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- TABLA LISTADO DE GASTOS -->
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto border border-gray-200">
                            <thead class="bg-gray-50 text-gray-700 text-sm uppercase">
                                <tr>
                                    <th class="px-4 py-2 border-b border-gray-200 text-left">
                                        <span class="inline-flex items-center gap-1">
                                            <ClipboardDocumentListIcon class="h-4 w-4" />
                                            Descripción
                                        </span>
                                    </th>
                                    <th class="px-4 py-2 border-b border-gray-200 text-left">
                                        <span class="inline-flex items-center gap-1">
                                            <CurrencyDollarIcon class="h-4 w-4" />
                                            Monto
                                        </span>
                                    </th>
                                    <th class="px-4 py-2 border-b border-gray-200 text-left">
                                        <CalendarIcon class="h-4 w-4" />
                                        Periodo
                                    </th>
                                    <th class="px-4 py-2 border-b border-gray-200 text-left">
                                        <CalendarIcon class="h-4 w-4" />
                                        Venc.
                                    </th>
                                    <th class="px-4 py-2 border-b border-gray-200 text-left">
                                        <CheckBadgeIcon class="h-4 w-4" />
                                        Estado
                                    </th>
                                    <th class="px-4 py-2 border-b border-gray-200 text-left">
                                        Acciones
                                    </th>
                                    <th class="px-4 py-2 border-b border-gray-200 text-left">
                                        Distribuir
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <template v-for="gasto in filteredGastos" :key="gasto.id_gasto">
                                    <tr>
                                        <td class="px-4 py-2 whitespace-normal break-words max-w-xs">
                                            <button @click="
                                                toggleDetails(
                                                    gasto.id_gasto
                                                )
                                                " class="mr-2 text-xl font-bold">
                                                {{
                                                    expandedExpenses[
                                                        gasto.id_gasto
                                                    ]
                                                        ? "−"
                                                        : "+"
                                                }}
                                            </button>
                                            {{ gasto.descripcion }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{
                                                formatMoneda(
                                                    gasto.monto_total,
                                                    gasto.tipo_moneda
                                                )
                                            }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{
                                                formatDate(gasto.fecha_periodo)
                                            }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{
                                                gasto.fecha_vencimiento
                                                    ? formatDate(
                                                        gasto.fecha_vencimiento
                                                    )
                                                    : "-"
                                            }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <span :class="[
                                                'px-2 py-1 text-xs font-semibold text-white rounded-full',
                                                stateColor(
                                                    gasto.estado_pago
                                                ),
                                            ]">
                                                {{ gasto.estado_pago }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <template v-if="
                                                gasto.distribuciones_count ==
                                                0
                                            ">
                                                <Link :href="route(
                                                    'gastos_comunes.edit',
                                                    gasto.id_gasto
                                                )
                                                    " class="text-green-600 hover:underline mr-2">Editar</Link>
                                                <button @click="
                                                    openModal(
                                                        'delete',
                                                        gasto.id_gasto
                                                    )
                                                    " class="text-red-600 hover:underline">
                                                    Eliminar
                                                </button>
                                            </template>
                                            <template v-else>
                                                <span class="text-gray-500 text-xs">No se puede
                                                    editar/eliminar</span>
                                            </template>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <template v-if="
                                                gasto.distribuciones_count >
                                                0
                                            ">
                                                <template v-if="
                                                    gasto.estado_pago !==
                                                    'Conciliado'
                                                ">
                                                    <button @click="
                                                        openModal(
                                                            'validate',
                                                            gasto.id_gasto
                                                        )
                                                        "
                                                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-1 px-3 rounded">
                                                        Validar
                                                    </button>
                                                </template>
                                                <template v-else>
                                                    <span class="text-gray-500 text-xs">Distribución
                                                        validada</span>
                                                </template>
                                            </template>
                                            <template v-else>
                                                <button @click="
                                                    openFinalDistributionModal(
                                                        gasto.id_gasto
                                                    )
                                                    "
                                                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-1 px-3 rounded">
                                                    Distribuir
                                                </button>
                                            </template>
                                        </td>
                                    </tr>

                                    <!-- Fila expandida con detalles -->
                                    <tr v-if="expandedExpenses[gasto.id_gasto]">
                                        <td colspan="7" class="px-4 py-4 bg-gray-50">
                                            <!-- Botones si no ha sido distribuido ni conciliado -->
                                            <div v-if="
                                                gasto.distribuciones_count ===
                                                0 &&
                                                gasto.estado_pago !==
                                                'Conciliado'
                                            " class="mb-2 flex items-center space-x-2">
                                                <!-- Botón: Agregar Detalle -->
                                                <button @click="
                                                    openAdvancedModal(gasto)
                                                    "
                                                    class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold py-1 px-3 rounded">
                                                    <PlusIcon class="h-4 w-4 inline-block mr-1" />
                                                    Agregar Detalle
                                                </button>
                                                <!-- Botón: Consumos Pendientes -->
                                                <button @click="
                                                    openPendingConsumption(
                                                        gasto
                                                    )
                                                    "
                                                    class="bg-red-600 hover:bg-red-700 text-white text-xs font-semibold py-1 px-3 rounded">
                                                    Consumos Pendientes
                                                </button>
                                            </div>
                                            <!-- Lista de detalles -->
                                            <div v-if="
                                                gasto.detalle_gasto_comuns &&
                                                gasto.detalle_gasto_comuns
                                                    .length > 0
                                            ">
                                                <table class="w-full table-auto border">
                                                    <thead class="bg-gray-200">
                                                        <tr>
                                                            <th class="px-2 py-1">
                                                                Tipo
                                                            </th>
                                                            <th class="px-2 py-1">
                                                                Monto
                                                            </th>
                                                            <th class="px-2 py-1">
                                                                Ámbito
                                                            </th>
                                                            <th class="px-2 py-1">
                                                                Destino
                                                            </th>
                                                            <th class="px-2 py-1">
                                                                Descripción
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="detalle in gasto.detalle_gasto_comuns" :key="detalle.id_detalle
                                                            " class="border-t">
                                                            <td class="px-2 py-1">
                                                                {{
                                                                    detalle
                                                                        .tipo_gasto_comun
                                                                        ?.nombre ||
                                                                    "Sin tipo"
                                                                }}
                                                            </td>
                                                            <td class="px-2 py-1">
                                                                {{
                                                                    formatMoneda(
                                                                        detalle.monto_detalle,
                                                                        gasto.tipo_moneda
                                                                    )
                                                                }}
                                                            </td>
                                                            <td class="px-2 py-1">
                                                                <span v-if="
                                                                    detalle.distribution_scope ===
                                                                    'condominium'
                                                                ">Condominio</span>
                                                                <span v-else-if="
                                                                    detalle.distribution_scope ===
                                                                    'tower'
                                                                ">Torre</span>
                                                                <span v-else-if="
                                                                    detalle.distribution_scope ===
                                                                    'unit'
                                                                ">Unidad</span>
                                                                <span v-else-if="
                                                                    detalle.distribution_scope ===
                                                                    'extension'
                                                                ">Extensión</span>
                                                            </td>
                                                            <td class="px-2 py-1">
                                                                <!-- TOWER -->
                                                                <template v-if="
                                                                    detalle.distribution_scope ===
                                                                    'tower'
                                                                ">
                                                                    {{
                                                                        detalle
                                                                            .targetTorre
                                                                            ?.nombre ||
                                                                        "Torre no asignada"
                                                                    }}
                                                                </template>
                                                                <!-- UNIT -->
                                                                <template v-else-if="
                                                                    detalle.distribution_scope ===
                                                                    'unit'
                                                                ">
                                                                    Torre:
                                                                    {{
                                                                        detalle
                                                                            .targetTorre
                                                                            ?.nombre ||
                                                                        "N/A"
                                                                    }}, Unidad:
                                                                    {{
                                                                        detalle
                                                                            .targetUnidad
                                                                            ?.nombre_unidad ||
                                                                        "N/A"
                                                                    }}
                                                                </template>
                                                                <!-- EXTENSION -->
                                                                <template v-else-if="
                                                                    detalle.distribution_scope ===
                                                                    'extension'
                                                                ">
                                                                    Extensión:
                                                                    {{
                                                                        detalle
                                                                            .targetExtencion
                                                                            ?.nombre ||
                                                                        "N/A"
                                                                    }}
                                                                </template>
                                                                <!-- CONDOMINIUM -->
                                                                <template v-else>
                                                                    Todo el
                                                                    condominio
                                                                </template>
                                                            </td>
                                                            <td class="px-2 py-1">
                                                                {{
                                                                    detalle.descripcion_detalle ||
                                                                    "N/A"
                                                                }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div v-else class="text-gray-600">
                                                No hay detalles asignados aún.
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-if="filteredGastos.length === 0">
                                    <td colspan="7" class="px-4 py-2 text-center">
                                        No hay gastos registrados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-4">
                        <Pagination :links="props.gastos.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE CONFIRMACIÓN (BÁSICO) -->
        <ConfirmationModal v-if="modalVisible" :visible="modalVisible" :message="modalMessage" @confirm="onConfirm"
            @cancel="onCancel" />

        <!-- MODAL DE CONSUMOS PENDIENTES -->
        <div v-if="showConsumptionModal"
            class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
            <div class="bg-white p-4 w-full max-w-4xl rounded shadow-lg">
                <h2 class="text-lg font-bold mb-2">Consumos Pendientes</h2>

                <p v-if="pendingConsumptions.length === 0" class="text-gray-600">
                    No hay consumos pendientes para mostrar.
                </p>
                <div v-else class="overflow-x-auto max-h-96">
                    <table class="min-w-full border border-gray-300 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-2 py-1">Servicio / Destino</th>
                                <th class="px-2 py-1">Lect. Anterior</th>
                                <th class="px-2 py-1">Lect. Actual</th>
                                <th class="px-2 py-1">Consumo</th>
                                <th class="px-2 py-1">Precio</th>
                                <th class="px-2 py-1">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in pendingConsumptions" :key="index" class="border-b">
                                <td class="px-2 py-1 font-semibold">
                                    {{ item.service_label || item.servicio }}
                                </td>
                                <td class="px-2 py-1">
                                    {{ item.lectura_anterior }}
                                </td>
                                <td class="px-2 py-1">
                                    {{ item.lectura_actual }}
                                </td>
                                <td class="px-2 py-1">{{ item.consumo }}</td>
                                <!-- Estas 2 columnas (Precio y Total) usan item.precio y item.total
                                     que ya viene formateado desde el Back (controlador) -->
                                <td class="px-2 py-1">{{ item.precio }}</td>
                                <td class="px-2 py-1 font-bold">
                                    {{ item.total }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Paginación HTML devuelta por Laravel -->
                <div v-if="consumptionLinks" v-html="consumptionLinks" class="mt-2 text-xs text-blue-600"
                    @click.prevent="handleConsumptionPageClick" />

                <div class="mt-4 flex justify-end space-x-2">
                    <button @click="closeConsumptionModal" class="px-4 py-2 border rounded">
                        Cerrar
                    </button>
                    <button v-if="pendingConsumptions.length > 0" @click="applyConsumptionDistribution"
                        class="px-4 py-2 bg-blue-600 text-white rounded">
                        Distribuir
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL FINAL DISTRIBUCIÓN (JERÁRQUICA) -->
        <div v-if="showFinalDistributionModal"
            class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
            <div class="bg-white p-6 w-full max-w-3xl rounded shadow-lg">
                <h2 class="text-lg font-bold mb-4">
                    Previsualización de la Distribución Final
                </h2>
                <div v-if="finalDistributionData.length === 0">
                    <p class="text-gray-600">
                        No hay detalles para distribuir.
                    </p>
                </div>
                <div v-else class="max-h-80 overflow-y-auto">
                    <!-- Jerarquía tower-> occupant-> units-> extension-> services -->
                    <div v-for="(towerItem, idxT) in finalDistributionData" :key="'tw-' + idxT"
                        class="mb-4 border-b pb-2">
                        <h3 class="font-semibold text-green-700">
                            Torre: {{ towerItem.tower_name }}
                        </h3>
                        <div v-for="(occ, idxO) in towerItem.occupants" :key="'occ-' + idxO" class="ml-4 mt-2">
                            <h4 class="font-semibold text-blue-700">
                                Ocupante: {{ occ.occupant_name }}
                            </h4>
                            <!-- Unidades -->
                            <div v-if="occ.units && occ.units.length > 0" class="ml-4 mt-1">
                                <div v-for="(un, idxU) in occ.units" :key="'un-' + idxU" class="mb-2">
                                    <p class="text-sm font-medium text-gray-800">
                                        Unidad: {{ un.unit_name }}
                                    </p>
                                    <ul class="pl-4 list-disc text-xs text-gray-700">
                                        <li v-for="(srv, idxSrv) in un.services" :key="'srv-' + idxSrv">
                                            {{ srv.service }} - {{ srv.monto }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- Extensiones -->
                            <div v-if="
                                occ.extensions && occ.extensions.length > 0
                            " class="ml-4 mt-1">
                                <div v-for="(ex, idxE) in occ.extensions" :key="'ex-' + idxE" class="mb-2">
                                    <p class="text-sm font-medium text-gray-800">
                                        Extensión: {{ ex.extension_name }}
                                    </p>
                                    <ul class="pl-4 list-disc text-xs text-gray-700">
                                        <li v-for="(
srv, idxSrv2
                                            ) in ex.services" :key="'srvE-' + idxSrv2">
                                            {{ srv.service }} - {{ srv.monto }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button @click="closeFinalDistributionModal" class="px-4 py-2 border rounded text-gray-700">
                        Cancelar
                    </button>
                    <button @click="confirmFinalDistribution" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL AVANZADO: AGREGAR DETALLES -->
        <div v-if="advancedModalVisible"
            class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl p-6">
                <h2 class="text-xl font-bold mb-2">
                    Agregar Detalles al Gasto: {{ currentGasto?.descripcion }}
                    <span class="text-sm font-normal">
                        (Total:
                        {{
                            formatMoneda(
                                currentGasto?.monto_total,
                                currentGasto?.tipo_moneda
                            )
                        }}, Asignado:
                        {{
                            formatMoneda(
                                totalAssigned,
                                currentGasto?.tipo_moneda
                            )
                        }}, Falta:
                        {{
                            formatMoneda(
                                remainingAmount(),
                                currentGasto?.tipo_moneda
                            )
                        }})
                    </span>
                </h2>
                <div class="grid grid-cols-2 gap-6">
                    <!-- Panel Condominio -->
                    <div>
                        <div class="border rounded mb-4">
                            <div class="bg-green-600 text-white px-4 py-2 flex justify-between items-center cursor-pointer"
                                @click="togglePanel('condominium')">
                                <span>Distribución por Condominio</span>
                                <span>{{ showCondominium ? "−" : "+" }}</span>
                            </div>
                            <transition name="slide">
                                <div v-if="showCondominium" class="p-4 space-y-2">
                                    <!-- Listado de tipos para Condominio -->
                                    <div class="max-h-40 overflow-y-auto">
                                        <div v-for="(
tipo, index
                                            ) in limitedTipos" :key="'condo-' + tipo.id_tipo_gasto"
                                            class="flex items-center space-x-2">
                                            <input type="radio" :value="tipo" v-model="condominiumInput.selectedType
                                                " class="form-radio" name="condominiumType" />
                                            <span>{{ tipo.nombre }}</span>
                                        </div>
                                    </div>

                                    <!-- Monto -->
                                    <div class="mt-2">
                                        <InputLabel for="condoAmount" :value="currentGasto
                                            ? formatMoneda(
                                                currentGasto.monto_total,
                                                currentGasto.tipo_moneda
                                            )
                                            : 'Monto'
                                            " />
                                        <TextInput id="condoAmount" v-model="condominiumInput.amount" type="number"
                                            step="0.01" class="w-full" />
                                    </div>
                                    <!-- Descripción -->
                                    <div>
                                        <InputLabel for="condoDesc" value="Descripción (opcional)" />
                                        <TextInput id="condoDesc" v-model="condominiumInput.description
                                            " class="w-full" />
                                    </div>
                                    <!-- Archivo Sustento -->
                                    <div class="mt-2">
                                        <InputLabel for="condoFile" value="Archivo Sustento (opcional)" />
                                        <FileInput id="condoFile" v-model="condominiumInput.file" />
                                    </div>
                                    <p v-if="errorCondominium" class="text-red-500 text-xs">
                                        {{ errorCondominium }}
                                    </p>
                                    <div>
                                        <PrimaryButton @click="addCondominiumDetail">
                                            <template v-if="isEditingCondominium">
                                                Actualizar
                                            </template>
                                            <template v-else>Agregar</template>
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <!-- Panel Torre -->
                        <div class="border rounded mb-4">
                            <div class="bg-green-600 text-white px-4 py-2 flex justify-between items-center cursor-pointer"
                                @click="togglePanel('tower')">
                                <span>Distribución por Torre</span>
                                <span>{{ showTower ? "−" : "+" }}</span>
                            </div>
                            <transition name="slide">
                                <div v-if="showTower" class="p-4 space-y-2">
                                    <div class="max-h-40 overflow-y-auto">
                                        <div v-for="(
tipo, index
                                            ) in limitedTipos" :key="'tower-' + tipo.id_tipo_gasto"
                                            class="flex items-center space-x-2">
                                            <input type="radio" :value="tipo" v-model="towerInput.selectedType
                                                " class="form-radio" name="towerType" />
                                            <span>{{ tipo.nombre }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <InputLabel for="towerAmount" :value="currentGasto
                                            ? formatMoneda(
                                                currentGasto.monto_total,
                                                currentGasto.tipo_moneda
                                            )
                                            : 'Monto'
                                            " />
                                        <TextInput id="towerAmount" v-model="towerInput.amount" type="number"
                                            step="0.01" class="w-full" />
                                    </div>
                                    <div>
                                        <InputLabel for="towerDesc" value="Descripción (opcional)" />
                                        <TextInput id="towerDesc" v-model="towerInput.description" class="w-full" />
                                    </div>
                                    <div>
                                        <InputLabel for="selectTower" value="Seleccionar Torre" />
                                        <select id="selectTower" v-model="towerInput.selectedTower"
                                            class="w-full border rounded px-2 py-1">
                                            <option disabled value="">
                                                Seleccione una torre
                                            </option>
                                            <option v-for="ed in props.edificios" :key="'ed-' + ed.id_edificio"
                                                :value="ed.id_edificio">
                                                {{ ed.nombre }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="mt-2">
                                        <InputLabel for="towerFile" value="Archivo Sustento (opcional)" />
                                        <FileInput id="towerFile" v-model="towerInput.file" />
                                    </div>
                                    <p v-if="errorTower" class="text-red-500 text-xs">
                                        {{ errorTower }}
                                    </p>
                                    <div>
                                        <PrimaryButton @click="addTowerDetail">
                                            <template v-if="isEditingTower">
                                                Actualizar
                                            </template>
                                            <template v-else>Agregar</template>
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <!-- Panel Unidad -->
                        <div class="border rounded">
                            <div class="bg-green-600 text-white px-4 py-2 flex justify-between items-center cursor-pointer"
                                @click="togglePanel('unit')">
                                <span>Distribución por Unidad</span>
                                <span>{{ showUnit ? "−" : "+" }}</span>
                            </div>
                            <transition name="slide">
                                <div v-if="showUnit" class="p-4 space-y-2">
                                    <div class="max-h-40 overflow-y-auto">
                                        <div v-for="(
tipo, index
                                            ) in limitedTipos" :key="'unit-' + tipo.id_tipo_gasto"
                                            class="flex items-center space-x-2">
                                            <input type="radio" :value="tipo" v-model="unitInput.selectedType"
                                                class="form-radio" name="unitType" />
                                            <span>{{ tipo.nombre }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <InputLabel for="unitAmount" :value="currentGasto
                                            ? formatMoneda(
                                                currentGasto.monto_total,
                                                currentGasto.tipo_moneda
                                            )
                                            : 'Monto'
                                            " />
                                        <TextInput id="unitAmount" v-model="unitInput.amount" type="number" step="0.01"
                                            class="w-full" />
                                    </div>
                                    <div>
                                        <InputLabel for="unitDesc" value="Descripción (opcional)" />
                                        <TextInput id="unitDesc" v-model="unitInput.description" class="w-full" />
                                    </div>
                                    <div>
                                        <InputLabel for="selectUnitTower" value="Seleccionar Torre" />
                                        <select id="selectUnitTower" v-model="unitInput.selectedTower"
                                            class="w-full border rounded px-2 py-1">
                                            <option disabled value="">
                                                Seleccione una torre
                                            </option>
                                            <option v-for="ed in props.edificios" :key="'unit-ed-' + ed.id_edificio
                                                " :value="ed.id_edificio">
                                                {{ ed.nombre }}
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <InputLabel for="selectUnit" value="Seleccionar Unidad" />
                                        <select id="selectUnit" v-model="unitInput.selectedUnit"
                                            class="w-full border rounded px-2 py-1">
                                            <option disabled value="">
                                                Seleccione una unidad
                                            </option>
                                            <option v-for="u in filteredUnitOptions" :key="'u-' + u.id_unidad"
                                                :value="u.id_unidad">
                                                {{ u.nombre_unidad }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="mt-2">
                                        <InputLabel for="unitFile" value="Archivo Sustento (opcional)" />
                                        <FileInput id="unitFile" v-model="unitInput.file" />
                                    </div>
                                    <p v-if="errorUnit" class="text-red-500 text-xs">
                                        {{ errorUnit }}
                                    </p>
                                    <div>
                                        <PrimaryButton @click="addUnitDetail">
                                            <template v-if="isEditingUnit">
                                                Actualizar
                                            </template>
                                            <template v-else>Agregar</template>
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <!-- Tabla de Detalles Agregados localmente -->
                    <div>
                        <h3 class="text-lg font-semibold mb-2">
                            Detalles Agregados:
                        </h3>
                        <div v-if="addedDetails.length > 0" class="overflow-y-auto max-h-96">
                            <table class="w-full table-auto border">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="px-2 py-1">Ámbito</th>
                                        <th class="px-2 py-1">Tipo</th>
                                        <th class="px-2 py-1">Monto</th>
                                        <th class="px-2 py-1">Descripción</th>
                                        <th class="px-2 py-1">Destino</th>
                                        <th class="px-2 py-1">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(detail, idx) in addedDetails" :key="idx" class="border-t">
                                        <td class="px-2 py-1">
                                            {{ detail.scope }}
                                        </td>
                                        <td class="px-2 py-1">
                                            {{ detail.tipo.nombre }}
                                        </td>
                                        <td class="px-2 py-1">
                                            {{ detail.amount }}
                                        </td>
                                        <td class="px-2 py-1">
                                            {{ detail.description }}
                                        </td>
                                        <td class="px-2 py-1">
                                            <template v-if="detail.scope === 'Torre'">
                                                Torre:
                                                {{
                                                    getEdificioName(
                                                        detail.tower
                                                    )
                                                }}
                                            </template>
                                            <template v-else-if="
                                                detail.scope === 'Unidad'
                                            ">
                                                Torre:
                                                {{
                                                    getEdificioName(
                                                        detail.tower
                                                    )
                                                }}, Unidad:
                                                {{ getUnidadName(detail.unit) }}
                                            </template>
                                            <template v-else>-</template>
                                        </td>
                                        <td class="px-2 py-1 space-x-2">
                                            <button @click="editDetail(idx)" class="text-blue-600 text-xs">
                                                Editar
                                            </button>
                                            <button @click="removeDetail(idx)" class="text-red-600 text-xs">
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-gray-600">
                            No hay detalles agregados.
                        </div>
                    </div>
                </div>
                <!-- Botones finales modal -->
                <div class="mt-6 flex justify-end space-x-4">
                    <button @click="cancelAdvancedModal"
                        class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                        Cancelar
                    </button>
                    <PrimaryButton @click="submitAdvancedDetails">
                        Aceptar
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Transición para expandir/contraer paneles */
.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    max-height: 0;
    opacity: 0;
}

.slide-enter-to,
.slide-leave-from {
    max-height: 500px;
    opacity: 1;
}
</style>
