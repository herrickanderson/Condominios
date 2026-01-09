<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";
import { ref } from "vue";

const awsBaseUrl = import.meta.env.VITE_AWS_URL; // "https://solufacil-files.s3.us-east-2.amazonaws.com";

const getImageUrl = (path) => {
    return path ? `${awsBaseUrl}/${path}` : null;
};

const page = usePage();
const condominios = ref(page.props.condominios);
//const condominios = page.props.condominios; // Objeto de paginación
const onDeleteSuccess = (e) => {
    //console.log(e);
    condominios.value = e.props.condominios;
};
console.log(condominios.value.data);
</script>

<template>

    <Head title="Condominios" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h2 class="text-xl font-semibold text-white">Condominios</h2>
                <Link :href="route('condominios.create')" class="text-white hover:underline font-semibold">
                Crear Condominios
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Usamos overflow-x-auto y forzamos ocultar el scroll vertical -->
                        <div class="relative overflow-x-auto overflow-y-hidden">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3">Nombre</th>
                                        <th class="px-6 py-3">Rut</th>
                                        <th class="px-6 py-3">Dirección</th>
                                        <th class="px-6 py-3">Teléfono</th>
                                        <th class="px-6 py-3">Email</th>
                                        <th class="px-6 py-3">
                                            Fecha Contable Inicial
                                        </th>
                                        <th class="px-6 py-3">
                                            Fondo de Reserva
                                        </th>
                                        <th class="px-6 py-3">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="condominio in condominios.data" :key="condominio.id_condominio"
                                        class="bg-white border-b">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            <div class="flex items-center gap-3">
                                                <img v-if="condominio.logo" :src="getImageUrl(condominio.logo)" alt="Logo"
                                                    class="h-10 w-10 object-cover rounded-full" />
                                                <div v-else class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs">
                                                    Sin logo
                                                </div>
                                                <span>{{ condominio.nombre }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ condominio.rut }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ condominio.direccion }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ condominio.telefono }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ condominio.email }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{
                                                condominio.fecha_contable_inicial
                                                    ? condominio.fecha_contable_inicial
                                                    : "No definida"
                                            }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ condominio.fondo_reserva }}
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="flex flex-col space-y-2">
                                                <Link :href="route(
                                                    'condominios.edit',
                                                    condominio
                                                )
                                                    "
                                                    class="px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-800 transition-colors duration-150 text-center">
                                                Editar
                                                </Link>
                                                <Link @success="onDeleteSuccess" :href="route(
                                                    'condominios.destroy',
                                                    condominio
                                                )
                                                    "
                                                    class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-800 transition-colors duration-150 text-center"
                                                    method="delete" as="button">
                                                Eliminar
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="condominios.data.length === 0">
                                        <td colspan="8" class="px-6 py-4 text-center">
                                            No hay condominios registrados.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="px-4">
                                <Pagination :links="condominios.links" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
