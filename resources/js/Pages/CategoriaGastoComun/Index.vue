<script setup>
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    categorias: Object,
});
const categoriasData = computed(() => props.categorias.data || []);
</script>

<template>

    <Head title="Categorías de Gasto Común" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">
                    Estas en: Categorías de Gasto Común
                </h3>
                <Link :href="route('categoria_gasto_comun.create')" class="text-white hover:underline font-semibold">
                Crear Categoría
                </Link>
            </div>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-8">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">ID</th>
                                <th class="px-4 py-2">Nombre</th>
                                <th class="px-4 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="categoria in categoriasData" :key="categoria.id_categoria">
                                <td class="px-4 py-2">
                                    {{ categoria.id_categoria }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ categoria.nombre }}
                                </td>
                                <td class="px-4 py-2">
                                    <Link :href="route(
                                        'categoria_gasto_comun.edit',
                                        categoria.id_categoria
                                    )
                                        " class="text-blue-600 hover:underline mr-2">
                                    Editar
                                    </Link>
                                    <form @submit.prevent="
                                        $inertia.delete(
                                            route(
                                                'categoria_gasto_comun.destroy',
                                                categoria.id_categoria
                                            )
                                        )
                                        " class="inline">
                                        <button type="submit" class="text-red-600 hover:underline">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-4">
                        <Pagination :links="props.categorias.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
