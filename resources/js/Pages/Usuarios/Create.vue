<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { computed, ref, watch } from "vue";
// Íconos de Heroicons (24/solid)
import {
    UserIcon,
    UserCircleIcon,
    AtSymbolIcon,
    PhoneIcon,
    KeyIcon,
    CheckCircleIcon,
} from "@heroicons/vue/24/solid";

const {
    auth,
    condominios,
    roles,
    unidades,
    tiposProrrateo,
    documentTypes,
    extenciones,
} = usePage().props;

const userForm = useForm({
    name: "",
    apellidos: "",
    email: "",
    telefono: "",
    rut: "",
    password: "",
    estado: true,
    id_condominio:
        auth.user && auth.user.id_condominio ? auth.user.id_condominio : "",
    // Se asignará mediante la selección dinámica de torre y unidad
    id_unidad: "",
    id_rol: "",
    id_tipo_prorrateo: "",
    // NUEVOS CAMPOS
    tipo_documento: "",
    numero_documento: "",
    // CAMPOS PARA EXTENCIÓN (aplica solo para usuarios con rol Arrendador, ej. id 5)
    tiene_extencion: false,
    extenciones: [], // Array de IDs de extención seleccionadas
});

// ===========================
// SECCIÓN: Selección de Torre y Unidad
// ===========================

// Calculamos las torres disponibles a partir de las unidades.
// Cada unidad tiene la relación "edificio" con { id_edificio, nombre }
const towers = computed(() => {
    const result = [];
    unidades.forEach((u) => {
        if (
            u.edificio &&
            !result.find((t) => t.id_edificio === u.edificio.id_edificio)
        ) {
            result.push(u.edificio);
        }
    });
    return result;
});

// Variable reactiva para la torre seleccionada. Por defecto, la primera.
const selectedTorre = ref(
    towers.value.length ? towers.value[0].id_edificio : null
);

// Filtrar las unidades según la torre seleccionada
const filteredUnits = computed(() => {
    return unidades.filter(
        (u) => u.edificio && u.edificio.id_edificio === selectedTorre.value
    );
});

// Si cambia la torre, reiniciamos la unidad seleccionada
watch(selectedTorre, () => {
    if (filteredUnits.value.length) {
        userForm.id_unidad = filteredUnits.value[0].id_unidad;
    } else {
        userForm.id_unidad = "";
    }
});

// ===========================
// SECCIÓN: Extenciones (solo se mostrará si se marca "tiene extención")
// ===========================

// En este ejemplo, la sección de extenciones se mostrará para cualquier usuario que marque el checkbox.
// Si deseas restringirlo (por ejemplo, solo para rol Arrendador, id 5), puedes usar una condición extra:
// const showExtenciones = computed(() => Number(userForm.id_rol) === 5 && userForm.tiene_extencion);
const showExtenciones = computed(() => userForm.tiene_extencion);

// Para la selección dinámica de extenciones:
const selectedExtencionId = ref("");
const extencionDetails = computed(() => {
    return (
        extenciones.find((e) => e.id_extencion == selectedExtencionId.value) ||
        null
    );
});

// Función para agregar una extención a la lista (si no está ya agregada)
function addExtencion() {
    if (!selectedExtencionId.value) return;
    if (userForm.extenciones.includes(selectedExtencionId.value)) return;
    userForm.extenciones.push(selectedExtencionId.value);
    selectedExtencionId.value = "";
}

// Función para quitar una extención de la lista
function removeExtencion(id) {
    const index = userForm.extenciones.indexOf(id);
    if (index > -1) {
        userForm.extenciones.splice(index, 1);
    }
}

// ===========================
// Envío del formulario
function submitUser() {
    userForm.post(route("users.store"), {
        onSuccess: () => {
            userForm.reset();
        },
    });
}

// ===========================
// Mostrar Unidad y Tipo Prorrateo para ciertos roles (arrendatario o propietario)
const showUnidadYTipo = computed(
    () => userForm.id_rol == 3 || userForm.id_rol == 4
);
watch(
    () => userForm.id_rol,
    (newVal) => {
        if (![3, 4].includes(Number(newVal))) {
            userForm.id_unidad = "";
            userForm.id_tipo_prorrateo = "";
        }
    }
);
</script>

<template>

    <Head title="Crear Usuario" />
    <AuthenticatedLayout>
        <!-- Encabezado -->
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">
                    Estas en: Usuarios / Crear
                </h3>
                <Link :href="route('users.index')" class="text-white hover:underline font-semibold">
                <-Volver al listado </Link>
            </div>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h1 class="text-2xl font-bold mb-6">Nuevo Usuario</h1>
                    <form @submit.prevent="submitUser" class="space-y-6">
                        <!-- Nombre -->
                        <div>
                            <label for="name" class="block text-gray-700 mb-1">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <UserIcon class="h-5 w-5 text-gray-400 absolute left-2 top-2" />
                                <input v-model="userForm.name" id="name" type="text" required
                                    class="w-full pl-8 border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                    placeholder="Ej: Juan" />
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
                                <input v-model="userForm.apellidos" id="apellidos" type="text"
                                    class="w-full pl-8 border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                    placeholder="Ej: Pérez" />
                            </div>
                            <div v-if="userForm.errors.apellidos" class="text-red-600 text-sm mt-1">
                                {{ userForm.errors.apellidos }}
                            </div>
                        </div>
                        <!-- NUEVOS CAMPOS: Tipo y Número de Documento -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Tipo de Documento -->
                            <div>
                                <label for="tipo_documento" class="block text-gray-700 mb-1">
                                    Tipo de Documento
                                </label>
                                <select v-model="userForm.tipo_documento" id="tipo_documento"
                                    class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500">
                                    <option disabled value="">
                                        Seleccione un tipo de documento
                                    </option>
                                    <option v-for="(doc, index) in documentTypes" :key="index" :value="doc">
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
                                <input v-model="userForm.numero_documento" id="numero_documento" type="text"
                                    class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                    placeholder="Ingrese el número" />
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
                                <input v-model="userForm.email" id="email" type="email" required
                                    class="w-full pl-8 border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                    placeholder="correo@ejemplo.com" />
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
                                <input v-model="userForm.telefono" id="telefono" type="text"
                                    class="w-full pl-8 border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                    placeholder="Opcional" />
                            </div>
                            <div v-if="userForm.errors.telefono" class="text-red-600 text-sm mt-1">
                                {{ userForm.errors.telefono }}
                            </div>
                        </div>
                        <!-- Contraseña -->
                        <div>
                            <label for="password" class="block text-gray-700 mb-1">
                                Contraseña <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <KeyIcon class="h-5 w-5 text-gray-400 absolute left-2 top-2" />
                                <input v-model="userForm.password" id="password" type="password" required
                                    class="w-full pl-8 border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500"
                                    placeholder="Mínimo 6 caracteres" />
                            </div>
                            <div v-if="userForm.errors.password" class="text-red-600 text-sm mt-1">
                                {{ userForm.errors.password }}
                            </div>
                        </div>
                        <!-- Estado -->
                        <div class="flex items-center">
                            <input type="checkbox" checked v-model="userForm.estado"
                                class="rounded border-gray-300 text-green-600 focus:ring-green-500" id="estado" />
                            <label for="estado" class="ml-2 text-gray-700">Usuario Activo</label>
                        </div>
                        <!-- Condominio -->
                        <!-- Condominio -->
                        <div v-if="auth.user?.id_condominio === null">
                            <label for="id_condominio" class="block text-gray-700 mb-1">
                                Condominio <span class="text-red-500">*</span>
                            </label>
                            <template v-if="condominios.length">
                                <select v-model="userForm.id_condominio" id="id_condominio" required
                                    class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500">
                                    <option disabled value="">
                                        Seleccione un condominio
                                    </option>
                                    <option v-for="cond in condominios" :key="cond.id" :value="cond.id">
                                        {{ cond.nombre }}
                                    </option>
                                </select>
                                <div v-if="userForm.errors.id_condominio" class="text-red-600 text-sm mt-1">
                                    {{ userForm.errors.id_condominio }}
                                </div>
                            </template>
                            <template v-else>
                                <div class="mt-1">
                                    <Link :href="route('condominios.create')"
                                        class="cursor-pointer bg-blue-500 text-white font-bold py-2 px-4 rounded inline-block border border-transparent hover:bg-white hover:text-blue-500 hover:border-blue-500">
                                    No hay condominios registrados para asignar / Crear
                                    </Link>
                                </div>
                            </template>


                        </div>

                        <!-- Rol -->
                        <div>
                            <label for="id_rol" class="block text-gray-700 mb-1">
                                Rol <span class="text-red-500">*</span>
                            </label>
                            <select v-model="userForm.id_rol" id="id_rol" required
                                class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500">
                                <option disabled value="">
                                    Seleccione un rol
                                </option>
                                <option v-for="rol in roles" :key="rol.id_rol" :value="rol.id_rol">
                                    {{ rol.nombre }}
                                </option>
                            </select>
                            <div v-if="userForm.errors.id_rol" class="text-red-600 text-sm mt-1">
                                {{ userForm.errors.id_rol }}
                            </div>
                        </div>
                        <!-- Campos condicionales: Selección de Torre y Unidad -->
                        <div v-if="showUnidadYTipo" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Selección de Torre -->
                            <div>
                                <label for="torre" class="block text-gray-700 mb-1">
                                    Torre <span class="text-red-500">*</span>
                                </label>
                                <select v-model="selectedTorre" id="torre"
                                    class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500">
                                    <option v-for="torre in towers" :key="torre.id_edificio" :value="torre.id_edificio">
                                        {{ torre.nombre }}
                                    </option>
                                </select>
                            </div>
                            <!-- Selección de Unidad -->
                            <div>
                                <label for="unidad" class="block text-gray-700 mb-1">
                                    Unidad <span class="text-red-500">*</span>
                                </label>
                                <select v-model="userForm.id_unidad" id="unidad" required
                                    class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500">
                                    <option disabled value="">
                                        Seleccione una unidad
                                    </option>
                                    <option v-for="uni in filteredUnits" :key="uni.id_unidad" :value="uni.id_unidad">
                                        {{ uni.nombre_unidad }}
                                    </option>
                                </select>
                                <div v-if="userForm.errors.id_unidad" class="text-red-600 text-sm mt-1">
                                    {{ userForm.errors.id_unidad }}
                                </div>
                            </div>
                        </div>
                        <!-- Sección para Extenciones -->
                        <!-- Primero se muestra el checkbox para activar la sección -->

                        <div v-if="showUnidadYTipo">
                            <div class="mt-6">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" v-model="userForm.tiene_extencion"
                                        class="rounded border-gray-300 text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-gray-700">¿Agregar extenciones?</span>
                                </label>
                            </div>
                            <!-- Solo si se marca el checkbox se muestra la sección de selección -->
                            <div v-if="showExtenciones" class="mt-4 border-t pt-4">
                                <!-- Select para elegir una extención -->
                                <div class="mb-4">
                                    <label for="selectExtencion" class="block text-gray-700 mb-1">
                                        Seleccione Extención (Torre - Nombre)
                                    </label>
                                    <select v-model="selectedExtencionId" id="selectExtencion"
                                        class="w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500">
                                        <option disabled value="">
                                            Seleccione una extención
                                        </option>
                                        <option v-for="ext in extenciones" :key="ext.id_extencion"
                                            :value="ext.id_extencion">
                                            {{
                                                ext.edificio?.nombre || "N/A"
                                            }}
                                            - {{ ext.nombre }}
                                        </option>
                                    </select>
                                </div>
                                <!-- Mostrar detalles de la extención seleccionada -->
                                <div v-if="extencionDetails" class="mb-4 p-3 border rounded bg-white">
                                    <p>
                                        <strong>Torre:</strong>
                                        {{
                                            extencionDetails.edificio?.nombre ||
                                            "N/A"
                                        }}
                                    </p>
                                    <p>
                                        <strong>Nombre:</strong>
                                        {{ extencionDetails.nombre }}
                                    </p>
                                    <p>
                                        <strong>Tipo:</strong>
                                        {{ extencionDetails.tipo_extencion }}
                                        <span v-if="
                                            extencionDetails.cobro_unico ==
                                            1
                                        " class="text-green-600 font-bold">
                                            (Cobro Único)
                                        </span>
                                    </p>
                                    <div v-if="
                                        extencionDetails.servicios_extras &&
                                        extencionDetails.servicios_extras
                                            .length
                                    ">
                                        <p class="font-semibold">Servicios:</p>
                                        <ul class="list-disc ml-4">
                                            <li v-for="(
s, i
                                                ) in extencionDetails.servicios_extras" :key="i">
                                                ID: {{ s.id_tipo_gasto }} –
                                                {{ s.porcentaje_extra }}%
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- Botón para agregar la extención a la lista -->
                                <div class="mb-4">
                                    <button type="button" @click="addExtencion"
                                        class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-800">
                                        Agregar Extención
                                    </button>
                                </div>
                                <!-- Mostrar lista de extenciones agregadas -->
                                <div v-if="userForm.extenciones.length">
                                    <h3 class="text-lg font-bold mb-2">
                                        Extenciones Agregadas
                                    </h3>
                                    <table class="min-w-full border">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-2 py-1 text-left">
                                                    Torre
                                                </th>
                                                <th class="px-2 py-1 text-left">
                                                    Nombre
                                                </th>
                                                <th class="px-2 py-1 text-left">
                                                    Tipo
                                                </th>
                                                <th class="px-2 py-1 text-left">
                                                    Servicios
                                                </th>
                                                <th class="px-2 py-1 text-left">
                                                    Acciones
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(
extId, index
                                                ) in userForm.extenciones" :key="extId" class="border-t">
                                                <td class="px-2 py-1">
                                                    {{
                                                        extenciones.find(
                                                            (e) =>
                                                                e.id_extencion ==
                                                                extId
                                                        )?.edificio?.nombre ||
                                                        "N/A"
                                                    }}
                                                </td>
                                                <td class="px-2 py-1">
                                                    {{
                                                        extenciones.find(
                                                            (e) =>
                                                                e.id_extencion ==
                                                                extId
                                                        )?.nombre || ""
                                                    }}
                                                </td>
                                                <td class="px-2 py-1">
                                                    {{
                                                        extenciones.find(
                                                            (e) =>
                                                                e.id_extencion ==
                                                                extId
                                                        )?.tipo_extencion || ""
                                                    }}
                                                    <span v-if="
                                                        extenciones.find(
                                                            (e) =>
                                                                e.id_extencion ==
                                                                extId
                                                        )?.cobro_unico == 1
                                                    " class="text-green-600 font-bold">
                                                        (Cobro Único)
                                                    </span>
                                                </td>
                                                <td class="px-2 py-1">
                                                    <div v-if="
                                                        extenciones.find(
                                                            (e) =>
                                                                e.id_extencion ==
                                                                extId
                                                        )
                                                            ?.servicios_extras &&
                                                        extenciones.find(
                                                            (e) =>
                                                                e.id_extencion ==
                                                                extId
                                                        )?.servicios_extras
                                                            .length
                                                    ">
                                                        <ul class="list-disc ml-4">
                                                            <li v-for="(
s, i
                                                                ) in extenciones.find(
    (e) =>
        e.id_extencion ==
        extId
)
        ?.servicios_extras" :key="i">
                                                                ID:
                                                                {{
                                                                    s.id_tipo_gasto
                                                                }}
                                                                –
                                                                {{
                                                                    s.porcentaje_extra
                                                                }}%
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div v-else>
                                                        No hay servicios
                                                    </div>
                                                </td>
                                                <td class="px-2 py-1">
                                                    <button type="button" @click="
                                                        removeExtencion(
                                                            extId
                                                        )
                                                        " class="text-red-600 hover:underline">
                                                        Quitar
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- Botón Guardar -->
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-800 transition-colors">
                            <CheckCircleIcon class="h-5 w-5 mr-1" />
                            Guardar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
