<template>

    <Head title="Generar Distribución de Gasto" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-blue-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Generar Distribución de Gasto</h3>
                <Link :href="route('distribucion_gasto.index')" class="text-white hover:underline font-semibold">
                Volver al listado
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200">
                <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">
                    Seleccione el Gasto en Común y su Detalle a Distribuir
                </h3>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Seleccionar Gasto en Común -->
                    <div>
                        <InputLabel for="id_gasto" value="Gasto en Común" />
                        <select id="id_gasto" v-model="form.id_gasto" class="w-full border-gray-300 rounded-md"
                            required>
                            <option disabled value="">Seleccione un gasto en común</option>
                            <option v-for="g in props.gastos" :key="g.id_gasto" :value="g.id_gasto">
                                {{ g.descripcion }} - Vence: {{ formatDate(g.fecha_vencimiento) }}
                            </option>
                        </select>
                        <InputError :message="form.errors.id_gasto" />
                    </div>

                    <!-- Seleccionar Detalle pendiente, filtrado por el gasto en común seleccionado -->
                    <div v-if="filteredDetalles.length > 0">
                        <InputLabel for="id_detalle" value="Detalle de Gasto Común" />
                        <select id="id_detalle" v-model="form.id_detalle" class="w-full border-gray-300 rounded-md"
                            required>
                            <option disabled value="">Seleccione un detalle</option>
                            <option v-for="d in filteredDetalles" :key="d.id_detalle" :value="d.id_detalle">
                                {{ d.descripcion_detalle }} - (Monto: {{ d.monto_detalle }})
                            </option>
                        </select>
                        <InputError :message="form.errors.id_detalle" />
                    </div>
                    <div v-else class="p-2 bg-yellow-100 text-yellow-700 rounded">
                        No hay detalles pendientes para este gasto en común.
                    </div>

                    <!-- Campo adicional para gasto individual (por unidad) -->
                    <div
                        v-if="selectedDetalle && !selectedDetalle.id_edificio && !selectedDetalle.tipo_gasto_comun.aplica_a_todos_edificios">
                        <InputLabel for="id_unidad" value="Seleccione la Unidad" />
                        <select id="id_unidad" v-model="form.id_unidad" class="w-full border-gray-300 rounded-md"
                            required>
                            <option disabled value="">Seleccione una unidad</option>
                            <option v-for="u in props.unidades" :key="u.id_unidad" :value="u.id_unidad">
                                {{ u.nombre_unidad }}
                            </option>
                        </select>
                        <InputError :message="form.errors.id_unidad" />
                    </div>

                    <div class="flex justify-center">
                        <PrimaryButton>Generar Distribución</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    gastos: Array,    // Lista de gastos en común (con id_gasto, descripcion, fecha_vencimiento, etc.)
    detalles: Array,  // Lista de detalles pendientes (cada uno con id_gasto, descripcion_detalle, monto_detalle, etc.)
    unidades: Array,  // Lista de unidades
});

const form = useForm({
    id_gasto: '',
    id_detalle: '',
    id_unidad: '',
});

// Computed para filtrar los detalles por el gasto en común seleccionado
const filteredDetalles = computed(() => {
    if (!form.id_gasto) return [];
    return props.detalles.filter(d => d.id_gasto == form.id_gasto);
});

// Computed para obtener el detalle seleccionado (opcional)
const selectedDetalle = computed(() => {
    return props.detalles.find(d => d.id_detalle == form.id_detalle) || null;
});

// Función para formatear la fecha
function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const dateObj = new Date(dateStr);
    return isNaN(dateObj) ? 'N/A' : dateObj.toLocaleDateString();
}

function submitForm() {
    if (!form.id_gasto || !form.id_detalle) return;
    form.post(route('distribucion_gasto.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
}
</script>
