<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();
const arrendatario = page.props.arrendatario;
const pagos = page.props.pagos ?? [];

// Opcional: para mostrar detalles de cada pago (toggle)
const expandedPayments = ref({});

function togglePayment(idPago) {
    expandedPayments.value[idPago] = !expandedPayments.value[idPago];
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const d = new Date(dateStr);
    return isNaN(d) ? 'N/A' : d.toLocaleDateString();
}
</script>

<template>

    <Head title="Propietario - Pagos Realizados" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Pagos Realizados del Arrendatario</h3>
            </div>
        </template>

        <div class="py-8 px-4 max-w-7xl mx-auto">
            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-2xl font-bold mb-4">Información del Arrendatario</h2>
                <div v-if="!arrendatario" class="text-gray-600">
                    No hay arrendatario asignado.
                </div>
                <div v-else>
                    <p class="mb-2">
                        <strong>Nombre: </strong>{{ arrendatario.name }} {{ arrendatario.apellidos }}
                    </p>
                    <p class="mb-2">
                        <strong>Email: </strong>{{ arrendatario.email }}
                    </p>
                    <p class="mb-2">
                        <strong>Teléfono: </strong>{{ arrendatario.telefono }}
                    </p>
                </div>
            </div>

            <div class="mt-6 bg-white p-6 rounded shadow">
                <h2 class="text-2xl font-bold mb-4">Historial de Pagos</h2>
                <div v-if="!arrendatario" class="text-gray-600">
                    No hay arrendatario para mostrar pagos.
                </div>
                <div v-else-if="pagos.length === 0" class="text-gray-600">
                    No hay pagos realizados.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm text-gray-700">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2">ID Pago</th>
                                <th class="px-4 py-2">Descripción Gasto</th>
                                <th class="px-4 py-2">Monto</th>
                                <th class="px-4 py-2">Fecha Pago</th>
                                <th class="px-4 py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Fila principal de cada pago -->
                            <template v-for="pago in pagos" :key="pago.id_pago">
                                <tr @click="togglePayment(pago.id_pago)"
                                    class="border-b hover:bg-gray-50 cursor-pointer">
                                    <td class="px-4 py-2">{{ pago.id_pago }}</td>
                                    <td class="px-4 py-2">{{ pago.gastos_comune?.descripcion ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">{{ pago.monto }}</td>
                                    <td class="px-4 py-2">{{ formatDate(pago.fecha_pago) }}</td>
                                    <td class="px-4 py-2">{{ pago.estado }}</td>
                                </tr>
                                <!-- Sub-fila (toggle) para detalles del pago -->
                                <tr v-if="expandedPayments[pago.id_pago]">
                                    <td colspan="5" class="px-4 py-2">
                                        <!-- Aquí podrías mostrar la relación de distribuciones pagadas,
                         si definiste la relación en tu modelo Pago. 
                         Por ejemplo: pago.distribuciones_pagadas -->
                                        <div class="border p-2">
                                            <p class="font-semibold mb-2">Detalles del pago:</p>
                                            <div v-if="!pago.distribuciones_pagadas">
                                                Vista en Mantenimiento.
                                            </div>
                                            <div v-else>
                                                <table class="min-w-full text-sm">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-2 py-1">ID Distribución</th>
                                                            <th class="px-2 py-1">Detalle</th>
                                                            <th class="px-2 py-1">Monto Asignado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="dist in pago.distribuciones_pagadas"
                                                            :key="dist.id_distribucion"
                                                            class="hover:bg-gray-50 border-b">
                                                            <td class="px-2 py-1">{{ dist.id_distribucion }}</td>
                                                            <td class="px-2 py-1">
                                                                {{ dist.detalle_gasto_comun?.descripcion_detalle ??
                                                                'N/A' }}
                                                            </td>
                                                            <td class="px-2 py-1">{{ dist.monto_asignado }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Botón para volver al Dashboard del Propietario -->
            <div class="mt-6">
                <Link :href="route('propietario.index')"
                    class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-800">
                Volver al Dashboard
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
