<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Filtrar por Periodo</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div>
                        <label for="periodo" class="block text-gray-700 font-bold mb-2">
                            Seleccione Periodo
                        </label>
                        <select v-model="selectedPeriodoId" id="periodo" class="form-select mt-1 block w-full">
                            <option v-for="periodo in periodos" :key="periodo.id_gasto" :value="periodo.id_gasto">
                                {{ periodo.descripcion }} - Monto: {{ periodo.monto_total }} - Vence: {{
                                    formatDate(periodo.fecha_vencimiento) }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    periodos: Array,
    selectedPeriodo: Object,
});

// Inicializamos el select con el id del periodo seleccionado por defecto.
const selectedPeriodoId = ref(props.selectedPeriodo ? props.selectedPeriodo.id_gasto : '');

// Función para formatear la fecha (puedes ajustarla según tus necesidades).
function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const dateObj = new Date(dateStr);
    return isNaN(dateObj) ? 'N/A' : dateObj.toLocaleDateString();
}
</script>
