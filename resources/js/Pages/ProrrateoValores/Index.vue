<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    prorrateos: Object,
});

const prorrateosData = computed(() => props.prorrateos.data || []);
</script>

<template>

    <Head title="Listado de Prorrateos" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Listado de Prorrateos</h3>
                <Link :href="route('prorrateos.create')" class="text-white hover:underline font-semibold">
                Crear Prorrateo
                </Link>
            </div>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">ID</th>
                                <th class="px-4 py-2">Criterio</th>
                                <th class="px-4 py-2">Valor</th>
                                <th class="px-4 py-2">Tipo Prorrateo</th>
                                <th class="px-4 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in prorrateosData" :key="item.id">
                                <td class="px-4 py-2">{{ item.id }}</td>
                                <td class="px-4 py-2">{{ item.criterio }}</td>
                                <td class="px-4 py-2">{{ item.valor_criterio }}</td>
                                <td class="px-4 py-2">
                                    {{ item.tipo_prorrateo ? item.tipo_prorrateo.descripcion : '' }}
                                </td>
                                <td class="px-4 py-2">
                                    <Link :href="route('prorrateos.edit', item.id)"
                                        class="text-blue-600 hover:underline">
                                    Editar
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-4">
                        <Pagination :links="props.prorrateos.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
