<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { defineProps } from "vue";

const props = defineProps({
    roles: {
        type: Array,
        required: true,
    },
    usuarioRoles: {
        type: Array,
        required: true,
    },
});
</script>

<template>

    <Head title="Perfiles / Roles" />

    <AuthenticatedLayout>
        <!-- Encabezado similar al de edificios -->
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Perfiles / Roles</h3>
                <!-- Ajusta la ruta según corresponda (por ejemplo, a un formulario de creación) -->
                <Link :href="route('rolesperfil.create')" class="text-white hover:underline font-semibold">
                Crear Perfiles / Roles
                </Link>

            </div>
        </template>

        <!-- Contenedor principal con estilos similares a edificios -->
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-6">Gestión de Roles y Asignaciones</h1>

                        <!-- Sección de Roles -->
                        <section class="mb-8">
                            <h2 class="text-xl font-semibold mb-4">Roles</h2>
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3">ID</th>
                                        <th class="px-6 py-3">Nombre</th>
                                        <th class="px-6 py-3">Descripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="role in roles" :key="role.id_rol" class="bg-white border-b">
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ role.id_rol }}</td>
                                        <td class="px-6 py-4">{{ role.nombre }}</td>
                                        <td class="px-6 py-4">{{ role.descripcion }}</td>
                                    </tr>
                                    <tr v-if="roles.length === 0">
                                        <td colspan="3" class="px-6 py-4 text-center">No hay roles registrados.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        <!-- Sección de Asignaciones de Roles a Usuarios
                        <section>
                            <h2 class="text-xl font-semibold mb-4">Asignaciones de Roles a Usuarios</h2>
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3">ID Usuario</th>
                                        <th class="px-6 py-3">Nombre Usuario</th>
                                        <th class="px-6 py-3">ID Rol</th>
                                        <th class="px-6 py-3">Nombre Rol</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ur in usuarioRoles" :key="`${ur.id_usuario}-${ur.id_rol}`"
                                        class="bg-white border-b">
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ ur.usuario.id }}</td>
                                        <td class="px-6 py-4">{{ ur.usuario.name }}</td>
                                        <td class="px-6 py-4">{{ ur.role.id_rol }}</td>
                                        <td class="px-6 py-4">{{ ur.role.nombre }}</td>
                                    </tr>
                                    <tr v-if="usuarioRoles.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center">
                                            No hay asignaciones registradas.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>
                    -->


                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<!-- Puedes seguir utilizando TailwindCSS para estilos. Si requieres estilos adicionales, puedes agregarlos aquí -->
<style scoped>
/* Aquí puedes agregar estilos personalizados en caso de ser necesario */
</style>
