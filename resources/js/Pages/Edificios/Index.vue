<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";
import Pagination from "@/Components/Pagination.vue";
import { ref, watch, computed } from "vue";

const page = usePage();
const edificios = page.props.edificios;
// Mappings pasados desde el controlador:
const prorrateoCondominio = page.props.prorrateoCondominio;
const tipoProrrateoInd = page.props.tipoProrrateoInd;

const showSuccessMessage = ref(false);
const showErrorMessage = ref(false);

const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

watch(successMessage, (val) => {
    if (val) {
        showSuccessMessage.value = true;
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 4000);
    }
});

watch(errorMessage, (val) => {
    if (val) {
        showErrorMessage.value = true;
        setTimeout(() => {
            showErrorMessage.value = false;
        }, 4000);
    }
});
</script>

<template>

    <Head title="Edificios" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">
                    <span class="text-gray-300">Estas en:</span>
                    <span class="text-white font-bold">Edificios / Listas</span>
                </h3>
                <Link :href="route('edificios.create')" class="text-white hover:underline font-semibold">
                Crear Edificio / Lote
                </Link>
            </div>
        </template>

        <div class="container mx-auto max-w-7xl sm:px-6 lg:px-8 mt-4">
            <transition name="fade">
                <div v-if="showSuccessMessage && successMessage"
                    class="mb-4 p-4 rounded bg-green-100 text-green-800 text-sm">
                    {{ successMessage }}
                </div>
            </transition>
            <transition name="fade">
                <div v-if="showErrorMessage && errorMessage" class="mb-4 p-4 rounded bg-red-100 text-red-800 text-sm">
                    {{ errorMessage }}
                </div>
            </transition>
        </div>

        <div class="py-4">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="relative overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3">ID</th>
                                        <th class="px-6 py-3">Nombre</th>
                                        <th class="px-6 py-3">Condominio</th>
                                        <th class="px-6 py-3">Prorrateo</th>
                                        <th class="px-6 py-3">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="edificio in edificios.data" :key="edificio.id_edificio"
                                        class="bg-white border-b">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            {{ edificio.id_edificio }}
                                        </td>
                                        <td class="px-6 py-4">{{ edificio.nombre }}</td>
                                        <td class="px-6 py-4">
                                            {{ edificio.condominio ? edificio.condominio.nombre : "N/A" }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span v-if="edificio.aplica_prorrateo == 1">
                                                {{ tipoProrrateoInd[edificio.tipo_prorrateo_id]?.descripcion || "Sin prorrateo"
                                                }}
                                            </span>
                                            <span v-else>
                                                Prorrateo de Condominio:
                                                {{ prorrateoCondominio[edificio.id_condominio]?.descripcion || "Sin prorrateo"
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex flex-col items-center space-y-2">
                                                <Link :href="route('edificios.edit', edificio.id_edificio)"
                                                    class="inline-block px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-800 transition-colors duration-150">
                                                Editar
                                                </Link>
                                                <Link :href="route('edificios.destroy', edificio.id_edificio)"
                                                    method="delete" as="button" @success="() => Inertia.reload()"
                                                    class="inline-block px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-800 transition-colors duration-150">
                                                Eliminar
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="edificios.data.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center">
                                            No hay edificios registrados.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="px-4 mt-4">
                                <Pagination :links="edificios.links" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

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
