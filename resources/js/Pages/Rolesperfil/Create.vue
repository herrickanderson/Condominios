<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { defineProps, ref, watch, onMounted } from "vue";

const props = defineProps({
    usuarios: Array,
    roles: Array,
    errorDuplicado: {
        type: Boolean,
        default: false,
    },
});

// Estado local para mostrar/ocultar el mensaje
const showErrorDuplicado = ref(false);

// Cuando `errorDuplicado` cambie, activamos showErrorDuplicado
watch(
    () => props.errorDuplicado,
    (val) => {
        if (val) {
            showErrorDuplicado.value = true;
            // Desaparece tras 3 segundos (ajusta el tiempo a tu gusto)
            setTimeout(() => {
                showErrorDuplicado.value = false;
            }, 3000);
        }
    },
    { immediate: true } // Para que se ejecute la primera vez
);

// Formulario para crear un nuevo Role
const roleForm = useForm({
    nombre: "",
    descripcion: "",
});
function submitRole() {
    roleForm.post(route("roles.store"), {
        onSuccess: () => {
            roleForm.reset();
        },
    });
}

// Formulario para asignar un Role a un Usuario
const usuarioRoleForm = useForm({
    id_usuario: "",
    id_rol: "",
});
function submitUsuarioRole() {
    usuarioRoleForm.post(route("usuarioRoles.store"), {
        onSuccess: () => {
            usuarioRoleForm.reset();
        },
    });
}
</script>

<template>

    <Head title="Crear Perfiles / Roles" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Crear Perfiles / Roles</h3>
                <Link :href="route('rolesperfil.index')" class="text-white hover:underline font-semibold">
                Volver al listado
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                    <h1 class="text-2xl font-bold mb-6">Nuevo Registro</h1>

                    <!-- Formulario para crear un nuevo Role -->
                    <section class="mb-12">
                        <h2 class="text-xl font-semibold mb-4">Crear Role</h2>
                        <form @submit.prevent="submitRole">
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2" for="nombre">Nombre</label>
                                <input v-model="roleForm.nombre" type="text" id="nombre"
                                    class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Nombre del role"
                                    required />
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2" for="descripcion">Descripción</label>
                                <input v-model="roleForm.descripcion" type="text" id="descripcion"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                    placeholder="Descripción del role" />
                            </div>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-800 transition-colors">
                                Crear Role
                            </button>
                        </form>
                    </section>

                    <!-- Formulario para asignar un Role a un Usuario
                    <section>
                        <h2 class="text-xl font-semibold mb-4">Asignar Role a Usuario</h2>
                        <form @submit.prevent="submitUsuarioRole">

                            <Transition enter-active-class="transition ease-out duration-300"
                                enter-from-class="opacity-0 transform -translate-y-1"
                                enter-to-class="opacity-100 transform translate-y-0"
                                leave-active-class="transition ease-in duration-300"
                                leave-from-class="opacity-100 transform translate-y-0"
                                leave-to-class="opacity-0 transform -translate-y-1">
                                <div v-if="showErrorDuplicado"
                                    class="flex items-center p-3 mb-4 text-red-700 bg-red-100 border border-red-200 rounded-md shadow">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-4h2v2H9v-2zm0-8h2v6H9V6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <p class="text-sm font-medium">No se puede asignar un rol duplicado.</p>
                                </div>
                            </Transition>

                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2" for="id_usuario">Usuario</label>
                                <select v-model="usuarioRoleForm.id_usuario" id="id_usuario"
                                    class="w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Seleccione un usuario</option>
                                    <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                                        {{ usuario.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2" for="id_rol">Role</label>
                                <select v-model="usuarioRoleForm.id_rol" id="id_rol"
                                    class="w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Seleccione un role</option>
                                    <option v-for="role in roles" :key="role.id_rol" :value="role.id_rol">
                                        {{ role.nombre }}
                                    </option>
                                </select>
                            </div>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-800 transition-colors">
                                Asignar Role
                            </button>
                        </form>
                    </section>

                -->

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos adicionales si los consideras necesarios */
</style>
