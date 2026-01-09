<script setup>
import { useForm, usePage, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

// Layout
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Íconos Heroicons (ejemplo)
import {
    UserIcon,
    UserCircleIcon,
    AtSymbolIcon,
    PhoneIcon,
    KeyIcon,
    CheckCircleIcon,
} from "@heroicons/vue/24/solid";

// ======================================================
// Props y datos que vienen desde la página (UsersController@edit)
// ======================================================
const page = usePage();
const userProps = defineProps({
    user: {
        type: Object,
        required: true
    },
});

const {
    auth,
    condominios,
    roles,
    unidades,
    tiposProrrateo,   // Si lo usas en el form
    documentTypes,
    extenciones
} = page.props;

// ======================================================
// userForm - Inicializamos el formulario con los datos del usuario
// ======================================================
const userForm = useForm({
    name: userProps.user.name || "",
    apellidos: userProps.user.apellidos || "",
    email: userProps.user.email || "",
    telefono: userProps.user.telefono || "",
    rut: userProps.user.rut || "",
    password: "", // Está vacío para no forzar cambio de pass si no se requiere
    estado: userProps.user.estado ? true : false, // asumiendo que 'estado' es "1"/true para Activo
    id_condominio: userProps.user.id_condominio || "",
    id_unidad: userProps.user.id_unidad || "",
    id_rol: "",
    id_tipo_prorrateo: userProps.user.id_tipo_prorrateo || "",
    // NUEVOS CAMPOS
    tipo_documento: userProps.user.tipo_documento || "",
    numero_documento: userProps.user.numero_documento || "",
    // Extenciones
    tiene_extencion: false,  // Se actualizará luego con base en user.extenciones o user.tiene_extencion
    extenciones: []          // Se llenará con los IDs que el usuario ya tenga
});

// ======================================================
// Roles: tomamos el primero que tenga el usuario
// ======================================================
if (Array.isArray(userProps.user.roles) && userProps.user.roles.length > 0) {
    userForm.id_rol = userProps.user.roles[0].id_rol;
}

// ======================================================
// Manejo de “tiene_extencion” y carga de extenciones actuales
// ======================================================
// Si en la BD guardaste un campo "tiene_extencion" (0/1) o
// si prefieres determinarlo si user.extenciones.length > 0,
// ajusta la lógica según tus necesidades:
userForm.tiene_extencion = (userProps.user.tiene_extencion == 1) ||
                           (userProps.user.extenciones && userProps.user.extenciones.length > 0);

// Si el usuario ya tiene extenciones asociadas, cargamos sus IDs
if (userProps.user.extenciones && userProps.user.extenciones.length > 0) {
    userForm.extenciones = userProps.user.extenciones.map(e => e.id_extencion);
}

// ======================================================
// Lógica para mostrar u ocultar la selección de Unidad/TipoProrrateo
// ======================================================
const showUnidadYTipo = computed(() => {
    // Ejemplo: rol 3 = arrendatario, rol 4 = propietario
    return userForm.id_rol == 3 || userForm.id_rol == 4;
});

// Si el rol cambia y no es 3 ni 4, reseteamos id_unidad y prorrateo
watch(() => userForm.id_rol, (newVal) => {
    if (![3, 4].includes(Number(newVal))) {
        userForm.id_unidad = "";
        userForm.id_tipo_prorrateo = "";
    }
});

// ======================================================
// Sección: Selección de Torre (edificio) y Unidad
// Similar a create.vue, pero aquí partimos de lo que el usuario ya tiene
// ======================================================
const towers = computed(() => {
    const result = [];
    unidades.forEach(u => {
        if (u.edificio && !result.find(t => t.id_edificio === u.edificio.id_edificio)) {
            result.push(u.edificio);
        }
    });
    return result;
});

// Averiguamos qué torre tiene actualmente el usuario (por su unidad)
const selectedTorre = ref(null);
if (userProps.user.unidad && userProps.user.unidad.edificio) {
    selectedTorre.value = userProps.user.unidad.edificio.id_edificio;
} else if (towers.value.length) {
    // Si no existe una torre en la unidad, selecciona la primera
    selectedTorre.value = towers.value[0].id_edificio;
}

// Filtrar unidades según la torre
const filteredUnits = computed(() => {
    return unidades.filter(u => u.edificio && u.edificio.id_edificio === selectedTorre.value);
});

// Si se cambia la torre, reseteamos la unidad si no está dentro de la nueva lista
watch(selectedTorre, () => {
    // Si la unidad actual no se encuentra en filteredUnits, la reiniciamos.
    if (!filteredUnits.value.some(u => u.id_unidad === Number(userForm.id_unidad))) {
        userForm.id_unidad = filteredUnits.value.length ? filteredUnits.value[0].id_unidad : "";
    }
});

// ======================================================
// Sección de extenciones
// ======================================================
const showExtenciones = computed(() => userForm.tiene_extencion);

// Select para extenciones
const selectedExtencionId = ref("");

// Computed para mostrar detalles de la extención seleccionada
const extencionDetails = computed(() => {
    return extenciones.find(e => e.id_extencion == selectedExtencionId.value) || null;
});

// Agregar extención a la lista
function addExtencion() {
    if (!selectedExtencionId.value) return;
    // Evitar duplicados
    if (userForm.extenciones.includes(selectedExtencionId.value)) return;
    userForm.extenciones.push(selectedExtencionId.value);
    selectedExtencionId.value = "";
}

// Quitar extención de la lista
function removeExtencion(id) {
    const index = userForm.extenciones.indexOf(id);
    if (index > -1) {
        userForm.extenciones.splice(index, 1);
    }
}

// ======================================================
// selectedTipoProrrateo (solo si usas prorrateo en el formulario)
// ======================================================
const selectedTipoProrrateo = computed(() => {
    if (!tiposProrrateo) return null;
    return tiposProrrateo.find(tp => tp.id === Number(userForm.id_tipo_prorrateo));
});

// ======================================================
// Función para actualizar (PUT)
// ======================================================
function submitUser() {
    userForm.put(route('users.update', userProps.user.id), {
        onSuccess: () => {
            // Resetea la contraseña para no sobreescribirla en caso de recarga
            userForm.reset('password');
        },
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <!-- Encabezado -->
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Estas en: Usuarios / Editar</h3>
                <Link :href="route('users.index')" class="text-white hover:underline font-semibold">
                   <-Volver al listado
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h1 class="text-2xl font-bold mb-6">Editar Usuario</h1>

                    <!-- FORMULARIO -->
                    <form @submit.prevent="submitUser" class="space-y-6">
                        <!-- Nombre -->
                        <div>
                            <label for="name" class="block text-gray-700 mb-1">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <UserIcon class="h-5 w-5 text-gray-400 absolute left-2 top-2" />
                                <input
                                    v-model="userForm.name"
                                    id="name"
                                    type="text"
                                    required
                                    class="w-full pl-8 border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                    placeholder="Ej: Juan"
                                />
                            </div>
                            <div v-if="userForm.errors.name" class="text-red-600 text-sm mt-1">
                                {{ userForm.errors.name }}
                            </div>
                        </div>

                        <!-- Apellidos -->
                        <div>
                            <label for="apellidos" class="block text-gray-700 mb-1">Apellidos</label>
                            <div class="relative">
                                <UserCircleIcon class="h-5 w-5 text-gray-400 absolute left-2 top-2" />
                                <input
                                    v-model="userForm.apellidos"
                                    id="apellidos"
                                    type="text"
                                    class="w-full pl-8 border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                    placeholder="Ej: Pérez"
                                />
                            </div>
                            <div v-if="userForm.errors.apellidos" class="text-red-600 text-sm mt-1">
                                {{ userForm.errors.apellidos }}
                            </div>
                        </div>

                        <!-- Tipo de Documento y Número de Documento -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Tipo de Documento -->
                            <div>
                                <label for="tipo_documento" class="block text-gray-700 mb-1">
                                    Tipo de Documento
                                </label>
                                <select
                                    v-model="userForm.tipo_documento"
                                    id="tipo_documento"
                                    class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                >
                                    <option disabled value="">Seleccione un tipo de documento</option>
                                    <option
                                        v-for="(doc, index) in documentTypes"
                                        :key="index"
                                        :value="doc"
                                    >
                                        {{ doc }}
                                    </option>
                                </select>
                                <div v-if="userForm.errors.tipo_documento" class="text-red-600 text-sm mt-1">
                                    {{ userForm.errors.tipo_documento }}
                                </div>
                            </div>

                            <!-- Número de Documento -->
                            <div>
                                <label for="numero_documento" class="block text-gray-700 mb-1">
                                    Número de Documento
                                </label>
                                <input
                                    v-model="userForm.numero_documento"
                                    id="numero_documento"
                                    type="text"
                                    class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                    placeholder="Ingrese el número"
                                />
                                <div v-if="userForm.errors.numero_documento" class="text-red-600 text-sm mt-1">
                                    {{ userForm.errors.numero_documento }}
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <AtSymbolIcon class="h-5 w-5 text-gray-400 absolute left-2 top-2" />
                                <input
                                    v-model="userForm.email"
                                    id="email"
                                    type="email"
                                    required
                                    class="w-full pl-8 border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                    placeholder="correo@ejemplo.com"
                                />
                            </div>
                            <div v-if="userForm.errors.email" class="text-red-600 text-sm mt-1">
                                {{ userForm.errors.email }}
                            </div>
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label for="telefono" class="block text-gray-700 mb-1">Teléfono</label>
                            <div class="relative">
                                <PhoneIcon class="h-5 w-5 text-gray-400 absolute left-2 top-2" />
                                <input
                                    v-model="userForm.telefono"
                                    id="telefono"
                                    type="text"
                                    class="w-full pl-8 border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                    placeholder="Opcional"
                                />
                            </div>
                            <div v-if="userForm.errors.telefono" class="text-red-600 text-sm mt-1">
                                {{ userForm.errors.telefono }}
                            </div>
                        </div>

                        <!-- Contraseña -->
                        <div>
                            <label for="password" class="block text-gray-700 mb-1">
                                Contraseña (dejar en blanco para no cambiar)
                            </label>
                            <div class="relative">
                                <KeyIcon class="h-5 w-5 text-gray-400 absolute left-2 top-2" />
                                <input
                                    v-model="userForm.password"
                                    id="password"
                                    type="password"
                                    class="w-full pl-8 border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                    placeholder="Mínimo 6 caracteres"
                                />
                            </div>
                            <div v-if="userForm.errors.password" class="text-red-600 text-sm mt-1">
                                {{ userForm.errors.password }}
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="userForm.estado"
                                class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                id="estado"
                            />
                            <label for="estado" class="ml-2 text-gray-700">Usuario Activo</label>
                        </div>

                        <!-- Condominio (opcional si tu usuario logueado es superadmin) -->
                        <div v-if="auth.user?.id_condominio === null">
                            <label for="id_condominio" class="block text-gray-700 mb-1">
                                Condominio <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="userForm.id_condominio"
                                id="id_condominio"
                                required
                                class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                            >
                                <option disabled value="">Seleccione un condominio</option>
                                <option
                                    v-for="cond in condominios"
                                    :key="cond.id"
                                    :value="cond.id"
                                >
                                    {{ cond.nombre }}
                                </option>
                            </select>
                            <div v-if="userForm.errors.id_condominio" class="text-red-600 text-sm mt-1">
                                {{ userForm.errors.id_condominio }}
                            </div>
                        </div>

                        <!-- Rol -->
                        <div>
                            <label for="id_rol" class="block text-gray-700 mb-1">
                                Rol <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="userForm.id_rol"
                                id="id_rol"
                                required
                                class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                            >
                                <option disabled value="">Seleccione un rol</option>
                                <option
                                    v-for="rol in roles"
                                    :key="rol.id_rol"
                                    :value="rol.id_rol"
                                >
                                    {{ rol.nombre }}
                                </option>
                            </select>
                            <div v-if="userForm.errors.id_rol" class="text-red-600 text-sm mt-1">
                                {{ userForm.errors.id_rol }}
                            </div>
                        </div>

                        <!-- Campos condicionales (Unidad y Tipo Prorrateo) -->
                        <div v-if="showUnidadYTipo" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Torre -->
                            <div>
                                <label for="torre" class="block text-gray-700 mb-1">
                                    Torre <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="selectedTorre"
                                    id="torre"
                                    class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                >
                                    <option
                                        v-for="torre in towers"
                                        :key="torre.id_edificio"
                                        :value="torre.id_edificio"
                                    >
                                        {{ torre.nombre }}
                                    </option>
                                </select>
                            </div>

                            <!-- Unidad -->
                            <div>
                                <label for="id_unidad" class="block text-gray-700 mb-1">
                                    Unidad <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="userForm.id_unidad"
                                    id="id_unidad"
                                    required
                                    class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                >
                                    <option disabled value="">Seleccione una unidad</option>
                                    <option
                                        v-for="uni in filteredUnits"
                                        :key="uni.id_unidad"
                                        :value="uni.id_unidad"
                                    >
                                        {{ uni.nombre_unidad }}
                                    </option>
                                </select>
                                <div v-if="userForm.errors.id_unidad" class="text-red-600 text-sm mt-1">
                                    {{ userForm.errors.id_unidad }}
                                </div>
                            </div>
                        </div>

                        <!-- Si manejas Tipo Prorrateo directamente -->
                        <!--
                        <div v-if="showUnidadYTipo">
                            <label for="id_tipo_prorrateo" class="block text-gray-700 mb-1">
                                Tipo Prorrateo
                            </label>
                            <select
                                v-model="userForm.id_tipo_prorrateo"
                                id="id_tipo_prorrateo"
                                required
                                class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                            >
                                <option disabled value="">Seleccione un tipo de prorrateo</option>
                                <option
                                    v-for="tipo in tiposProrrateo"
                                    :key="tipo.id"
                                    :value="tipo.id"
                                >
                                    {{ tipo.descripcion }}
                                </option>
                            </select>
                            <div v-if="userForm.errors.id_tipo_prorrateo" class="text-red-600 text-sm">
                                {{ userForm.errors.id_tipo_prorrateo }}
                            </div>

                            <div v-if="selectedTipoProrrateo" class="mt-2 p-2 bg-gray-100 rounded">
                                <p class="font-semibold">Detalles del Tipo Prorrateo:</p>
                                <div
                                    v-if="selectedTipoProrrateo.prorrateoValores && selectedTipoProrrateo.prorrateoValores.length"
                                >
                                    <div
                                        v-for="valor in selectedTipoProrrateo.prorrateoValores"
                                        :key="valor.id"
                                    >
                                        <strong>{{ valor.criterio }}:</strong> {{ valor.valor_criterio }}
                                    </div>
                                </div>
                                <div v-else>
                                    <span>No hay detalles disponibles.</span>
                                </div>
                            </div>
                        </div>
                        -->

                        <!-- SECCIÓN EXTENCIONES -->
                        <div v-if="showUnidadYTipo">
                            <!-- Checkbox para activar/desactivar extenciones -->
                            <div class="mt-6">
                                <label class="inline-flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="userForm.tiene_extencion"
                                        class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                    />
                                    <span class="ml-2 text-gray-700">¿Agregar/Editar extenciones?</span>
                                </label>
                            </div>

                            <!-- Sección de selección de extenciones -->
                            <div v-if="showExtenciones" class="mt-4 border-t pt-4">
                                <!-- Select para elegir una extención -->
                                <div class="mb-4">
                                    <label for="selectExtencion" class="block text-gray-700 mb-1">
                                        Seleccione Extención (Torre - Nombre)
                                    </label>
                                    <select
                                        v-model="selectedExtencionId"
                                        id="selectExtencion"
                                        class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                    >
                                        <option disabled value="">Seleccione una extención</option>
                                        <option
                                            v-for="ext in extenciones"
                                            :key="ext.id_extencion"
                                            :value="ext.id_extencion"
                                        >
                                            {{ ext.edificio?.nombre || 'N/A' }} - {{ ext.nombre }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Mostrar detalles de la extención seleccionada -->
                                <div v-if="extencionDetails" class="mb-4 p-3 border rounded bg-white">
                                    <p>
                                        <strong>Torre:</strong>
                                        {{ extencionDetails.edificio?.nombre || 'N/A' }}
                                    </p>
                                    <p>
                                        <strong>Nombre:</strong>
                                        {{ extencionDetails.nombre }}
                                    </p>
                                    <p>
                                        <strong>Tipo:</strong>
                                        {{ extencionDetails.tipo_extencion }}
                                        <span
                                            v-if="extencionDetails.cobro_unico == 1"
                                            class="text-green-600 font-bold"
                                        >
                                            (Cobro Único)
                                        </span>
                                    </p>
                                    <div
                                        v-if="
                                            extencionDetails.servicios_extras &&
                                            extencionDetails.servicios_extras.length
                                        "
                                    >
                                        <p class="font-semibold">Servicios:</p>
                                        <ul class="list-disc ml-4">
                                            <li
                                                v-for="(s, i) in extencionDetails.servicios_extras"
                                                :key="i"
                                            >
                                                ID: {{ s.id_tipo_gasto }} – {{ s.porcentaje_extra }}%
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Botón para agregar la extención a la lista -->
                                <div class="mb-4">
                                    <button
                                        type="button"
                                        @click="addExtencion"
                                        class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-800"
                                    >
                                        Agregar Extención
                                    </button>
                                </div>

                                <!-- Lista de extenciones agregadas -->
                                <div v-if="userForm.extenciones.length">
                                    <h3 class="text-lg font-bold mb-2">Extenciones Agregadas</h3>
                                    <table class="min-w-full border">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-2 py-1 text-left">Torre</th>
                                                <th class="px-2 py-1 text-left">Nombre</th>
                                                <th class="px-2 py-1 text-left">Tipo</th>
                                                <th class="px-2 py-1 text-left">Servicios</th>
                                                <th class="px-2 py-1 text-left">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(extId, index) in userForm.extenciones"
                                                :key="extId"
                                                class="border-t"
                                            >
                                                <td class="px-2 py-1">
                                                    {{
                                                        (extenciones.find(e => e.id_extencion == extId)?.edificio?.nombre)
                                                        || 'N/A'
                                                    }}
                                                </td>
                                                <td class="px-2 py-1">
                                                    {{
                                                        extenciones.find(e => e.id_extencion == extId)?.nombre || ''
                                                    }}
                                                </td>
                                                <td class="px-2 py-1">
                                                    {{
                                                        extenciones.find(e => e.id_extencion == extId)?.tipo_extencion || ''
                                                    }}
                                                    <span
                                                        v-if="extenciones.find(e => e.id_extencion == extId)?.cobro_unico == 1"
                                                        class="text-green-600 font-bold"
                                                    >
                                                        (Cobro Único)
                                                    </span>
                                                </td>
                                                <td class="px-2 py-1">
                                                    <div
                                                        v-if="
                                                            extenciones.find(e => e.id_extencion == extId)?.servicios_extras &&
                                                            extenciones.find(e => e.id_extencion == extId)?.servicios_extras.length
                                                        "
                                                    >
                                                        <ul class="list-disc ml-4">
                                                            <li
                                                                v-for="(s, i) in extenciones.find(e => e.id_extencion == extId)?.servicios_extras"
                                                                :key="i"
                                                            >
                                                                ID: {{ s.id_tipo_gasto }} – {{ s.porcentaje_extra }}%
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div v-else>
                                                        No hay servicios
                                                    </div>
                                                </td>
                                                <td class="px-2 py-1">
                                                    <button
                                                        type="button"
                                                        @click="removeExtencion(extId)"
                                                        class="text-red-600 hover:underline"
                                                    >
                                                        Quitar
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Botón Actualizar -->
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-800 transition-colors"
                        >
                            <CheckCircleIcon class="h-5 w-5 mr-1" />
                            Actualizar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Opcional: estilos locales */
</style>
