<script setup>
import { computed, ref } from 'vue';
import { usePage, Link, router as Inertia } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

// Extraemos la información de la página actual
const { props } = usePage();

// Obtenemos el primer rol del usuario
const userRole = computed(() => props.auth.user?.roles?.[0] ?? null);
const userRoleId = computed(() => userRole.value?.id_rol ?? null);

// Determinamos si es superadmin (rol no asignado => userRole == null)
const isSuperAdmin = computed(() => userRole.value === null);

// Control para menú móvil “hamburguesa”
const showingNavigationDropdown = ref(false);

/**
 * Determina la ruta al hacer clic en el logo.
 */
const getRedirectRoute = () => {
    if (userRoleId.value == 3) {
        return route('pagos.pendientes');
    } else if (userRoleId.value == 4) {
        return route('propietario.index');
    } else if (userRoleId.value == 5) {
        return route('vigilancia.index');
    } else if (userRoleId.value == 6) {
        return route('mediciones.index');
    } else {
        return route('dashboard');
    }
};

// Descripción de rol
const roleDescription = computed(() => userRole.value?.nombre || 'Sin descripción');

// =====================
// Toggle en menú móvil
// =====================
const showAdminMobile = ref(false);
const showConfigMobile = ref(false);
const showOpsMobile = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navbar principal (fondo blanco) -->
        <nav class="border-b border-gray-100 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <!-- Sección izquierda: Logo y menús de escritorio -->
                    <div class="flex items-center">
                        <!-- Logo + nombre condominio -->
                        <div class="flex flex-col items-center">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 sm:mr-6">
                                <!-- Logo -->
                                <Link :href="getRedirectRoute()">
                                <ApplicationLogo class="block h-16 w-auto fill-current text-green-600" />
                                </Link>
                                <!-- Nombre del condominio -->
                                <span class="mt-1 mb-2 sm:mt-0 sm:mb-0 text-base font-semibold text-gray-600">
                                    {{ props.auth.user.condominio
                                        ? props.auth.user.condominio.nombre
                                        : 'Sin condominio'
                                    }}
                                </span>
                            </div>
                        </div>

                        <!-- Menú de escritorio -->
                        <div class="hidden sm:flex items-center space-x-8 ml-6">

                            <!-- Dropdown de Administración (escritorio) -->
                            <Dropdown v-if="isSuperAdmin || userRoleId === 1 || userRoleId === 2" align="right"
                                width="48">
                                <template #trigger>
                                    <span
                                        class="inline-flex items-center cursor-pointer text-gray-700 hover:text-green-600">
                                        <!-- Ícono de config -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-1"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.75 3.25a1 1 0 00-.95.684l-.44 1.324a8.967 8.967 0 00-1.022.593l-1.359-.27a1 1 0 00-1.09.43l-1 1.732a1 1 0 00.23 1.311l1.153.817a8.78 8.78 0 000 1.186l-1.153.817a1 1 0 00-.23 1.311l1 1.732a1 1 0 001.09.43l1.359-.27c.316.249.657.463 1.022.593l.44 1.324a1 1 0 00.95.684h2a1 1 0 00.95-.684l.44-1.324c.365-.13.706-.344 1.022-.593l1.359.27a1 1 0 001.09-.43l1-1.732a1 1 0 00-.23-1.311l-1.153-.817a8.78 8.78 0 000-1.186l1.153-.817a1 1 0 00.23-1.311l-1-1.732a1 1 0 00-1.09-.43l-1.359.27a8.967 8.967 0 00-1.022-.593l-.44-1.324A1 1 0 0011.75 3h-2z" />
                                        </svg>
                                        Administración
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </template>
                                <template #content>
                                    <DropdownLink v-if="userRoleId === 1 || isSuperAdmin"
                                        :href="route('condominios.index')">
                                        Condominios
                                    </DropdownLink>
                                    <DropdownLink :href="route('edificios.index')">
                                        Torres / Zonas
                                    </DropdownLink>
                                    <DropdownLink :href="route('extenciones.index')">
                                        Extenciones
                                    </DropdownLink>
                                    <DropdownLink :href="route('unidades.index')">
                                        Unidades / Lotes
                                    </DropdownLink>
                                    <DropdownLink :href="route('users.index')">
                                        Usuarios
                                    </DropdownLink>
                                </template>
                            </Dropdown>

                            <!-- Dropdown de Configuraciones (periodos, prorrateo...) (escritorio) -->
                            <Dropdown v-if="isSuperAdmin || userRoleId === 1 || userRoleId === 2" align="right"
                                width="48">
                                <template #trigger>
                                    <span
                                        class="inline-flex items-center cursor-pointer text-gray-700 hover:text-green-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-1"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.75 3.25a1 1 0 00-.95.684l-.44 1.324a8.967 8.967 0 00-1.022.593l-1.359-.27a1 1 0 00-1.09.43l-1 1.732a1 1 0 00.23 1.311l1.153.817a8.78 8.78 0 000 1.186l-1.153.817a1 1 0 00-.23 1.311l1 1.732a1 1 0 001.09.43l1.359-.27c.365-.13.706-.344 1.022-.593l.44 1.324a1 1 0 00.95.684h2a1 1 0 00.95-.684l.44-1.324c.365-.13.706-.344 1.022-.593l1.359.27a1 1 0 00.95-.43l1-1.732a1 1 0 00-.23-1.311l-1.153-.817a8.78 8.78 0 000-1.186l1.153-.817" />
                                        </svg>
                                        Configuraciones
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('periodos.index')">
                                        Conf. de Periodo
                                    </DropdownLink>
                                    <DropdownLink v-if="isSuperAdmin || userRoleId === 1" :href="route('tipos.index')">
                                        Conf. de Tipo Prorrateo General
                                    </DropdownLink>
                                    <DropdownLink :href="route('tipo_prorrateo_condominio.index')">
                                        Conf. de Tipo Prorrateo
                                    </DropdownLink>
                                    <DropdownLink :href="route('config_servicios.index')">
                                        Monto de Servicio
                                    </DropdownLink>

                                    <DropdownLink :href="route('DatosAdministrador.index')">
                                        Conf. de pagos (bancarios)
                                    </DropdownLink>
                                    <DropdownLink :href="route('ConfMora.index')">
                                        Conf. de Mora
                                    </DropdownLink>
                                </template>
                            </Dropdown>

                            <!-- Dropdown de Operaciones (escritorio) -->
                            <Dropdown
                                v-if="isSuperAdmin || (userRoleId !== 3 && userRoleId !== 4 && userRoleId !== 5 && userRoleId !== 6)"
                                align="right" width="48">
                                <template #trigger>
                                    <span
                                        class="inline-flex items-center cursor-pointer text-gray-700 hover:text-green-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-1"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 7c2.28 0 4.39.7 6.06 1.88a9 9 0 110 10.24A8.96 8.96 0 0112 17a8.96 8.96 0 01-6.06-2.88 9 9 0 010-10.24A8.96 8.96 0 0112 7z" />
                                        </svg>
                                        Operaciones
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('categoria_gasto_comun.index')">
                                        Categorías Gasto Común
                                    </DropdownLink>
                                    <DropdownLink :href="route('tipo_gasto_comun.index')">
                                        Tipo Gasto Común
                                    </DropdownLink>
                                    <DropdownLink :href="route('gastos_comunes.index')">
                                        Gastos Comunes
                                    </DropdownLink>
                                    <DropdownLink :href="route('mediciones.index')">
                                        Mediciones
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                            <Dropdown v-if="isSuperAdmin || userRoleId === 1" align="right" width="48">
                                <template #trigger>
                                    <span
                                        class="inline-flex items-center cursor-pointer text-gray-700 hover:text-green-600">
                                        <!-- Ícono de config -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-1"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.75 3.25a1 1 0 00-.95.684l-.44 1.324a8.967 8.967 0 00-1.022.593l-1.359-.27a1 1 0 00-1.09.43l-1 1.732a1 1 0 00.23 1.311l1.153.817a8.78 8.78 0 000 1.186l-1.153.817a1 1 0 00-.23 1.311l1 1.732a1 1 0 001.09.43l1.359-.27c.316.249.657.463 1.022.593l.44 1.324a1 1 0 00.95.684h2a1 1 0 00.95-.684l.44-1.324c.365-.13.706-.344 1.022-.593l1.359.27a1 1 0 001.09-.43l1-1.732a1 1 0 00-.23-1.311l-1.153-.817a8.78 8.78 0 000-1.186l1.153-.817a1 1 0 00.23-1.311l-1-1.732a1 1 0 00-1.09-.43l-1.359.27a8.967 8.967 0 00-1.022-.593l-.44-1.324A1 1 0 0011.75 3h-2z" />
                                        </svg>
                                        Reportes
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </template>
                                <template #content>
                                    <DropdownLink v-if="userRoleId === 1 || isSuperAdmin"
                                        :href="route('reporte.ingresos')">
                                        Ingresos
                                    </DropdownLink>

                                </template>
                            </Dropdown>
                            <!-- Menú de Pagos (rol 3 o superadmin) (escritorio) -->
                            <div v-if="isSuperAdmin || userRoleId === 3" class="flex space-x-4">
                                <NavLink :href="route('pagos.pendientes')" :active="route().current('pagos.pendientes')"
                                    class="text-gray-700 hover:text-green-600 flex items-center gap-1"
                                    class-active="text-green-700 border-b-2 border-green-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 7h18M3 10h18M8 16h8m-4-4v4" />
                                    </svg>
                                    Mis Gastos Pendientes
                                </NavLink>
                                <NavLink :href="route('pagos.index')" :active="route().current('pagos.index')"
                                    class="text-gray-700 hover:text-green-600 flex items-center gap-1"
                                    class-active="text-green-700 border-b-2 border-green-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2l4 -4M7 20h10a2 2 0 002 -2v-6a2 2 0 00-2 -2h-6l-2 -2H7a2 2 0 00-2 2v8a2 2 0 002 2h10a2 2 0 002 -2v-6a2 2 0 00-2 -2h-6l-2 -2H7a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Pagos Realizados
                                </NavLink>
                            </div>

                            <!-- Menú Propietario (rol 4) (escritorio) -->
                            <div v-if="userRoleId === 4" class="flex space-x-4">
                                <NavLink :href="route('propietario.index')"
                                    :active="route().current('propietario.index')"
                                    class="text-gray-700 hover:text-green-600 flex items-center gap-1"
                                    class-active="text-green-700 border-b-2 border-green-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.75 17a2.25 2.25 0 01-2.25-2.25V9.75a2.25 2.25 0 012.25-2.25h4.5a2.25 2.25 0 012.25 2.25v5a2.25 2.25 0 01-2.25 2.25h-4.5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.75 8.25V6a3 3 0 013-3h1.5a3 3 0 013 3v2.25" />
                                    </svg>
                                    Dashboard - Propietario
                                </NavLink>
                                <NavLink :href="route('propietario.pagosRealizados')"
                                    :active="route().current('propietario.pagosRealizados')"
                                    class="text-gray-700 hover:text-green-600 flex items-center gap-1"
                                    class-active="text-green-700 border-b-2 border-green-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2l4 -4M7 20h10a2 2 0 002 -2v-6a2 2 0 00-2 -2h-6l-2 -2H7a2 2 0 00-2 2v8a2 2 0 002 2h10a2 2 0 002 -2v-6a2 2 0 00-2 -2h-6l-2 -2H7a2 2 0 00-2 2v8a2 2 0 002 2h10a2 2 0 002 -2v-6a2 2 0 00-2 -2h-6l-2 -2H7a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Pagos Realizados Arrendatario
                                </NavLink>
                            </div>

                            <!-- Menú Vigilancia (rol 5) (escritorio) -->
                            <div v-if="userRoleId === 5" class="flex space-x-4">
                                <NavLink :href="route('vigilancia.index')" :active="route().current('vigilancia.index')"
                                    class="text-gray-700 hover:text-green-600 flex items-center gap-1"
                                    class-active="text-green-700 border-b-2 border-green-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 2c.87 0 1.73.25 2.46.7l6.46 4.3c.82.55 1.32 1.47 1.32 2.45v5.1c0 2.28-1.12 4.4-2.99 5.68l-4.52 3.01c-.93.62-2.11.62-3.04 0l-4.52-3.01A6.797 6.797 0 013 14.55v-5.1c0-.98.5-1.9 1.32-2.45l6.46-4.3C10.27 2.25 11.13 2 12 2z" />
                                    </svg>
                                    Vigilancia
                                </NavLink>
                            </div>
                        </div>
                    </div>

                    <!-- Sección derecha: Menú de usuario (escritorio) -->
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <div class="relative ml-3">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button type="button"
                                            class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium text-green-600 hover:bg-gray-50 hover:text-green-700 focus:outline-none">
                                            {{ props.auth.user.name }} - {{ roleDescription }}
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" class="ml-2 h-5 w-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5.121 17.804A7 7 0 0112 10c1.933 0 3.68.784 4.879 2.05M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')" class="hover:bg-gray-50">
                                        Mi Perfil
                                    </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button"
                                        class="hover:bg-gray-50">
                                        Cerrar sesión
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Botón hamburguesa para móvil -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center rounded-md p-2 text-green-600 hover:bg-gray-50 hover:text-green-700 focus:bg-gray-50 focus:text-green-700 focus:outline-none">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <!-- Icono menu normal -->
                                <path :class="{
                                    'hidden': showingNavigationDropdown,
                                    'inline-flex': !showingNavigationDropdown
                                }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                                <!-- Icono X -->
                                <path :class="{
                                    'hidden': !showingNavigationDropdown,
                                    'inline-flex': showingNavigationDropdown
                                }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Menú responsive para móvil -->
            <Transition name="slide-fade">
                <div v-if="showingNavigationDropdown" class="sm:hidden">
                    <div class="space-y-1 pb-3 pt-2">
                        <!-- Menú Pagos (rol 3 o superadmin) -->
                        <div v-if="isSuperAdmin || userRoleId === 3" class="border-t border-gray-200 pt-2 px-4">
                            <p class="text-xs font-semibold text-gray-500 mb-1">Pagos</p>
                            <ResponsiveNavLink :href="route('pagos.pendientes')"
                                :active="route().current('pagos.pendientes')">
                                Mis Gastos Pendientes
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('pagos.index')" :active="route().current('pagos.index')">
                                Pagos Realizados
                            </ResponsiveNavLink>
                        </div>

                        <!-- Menú Propietario (rol 4) -->
                        <div v-if="userRoleId === 4" class="border-t border-gray-200 pt-2 px-4">
                            <p class="text-xs font-semibold text-gray-500 mb-1">Propietario</p>
                            <ResponsiveNavLink :href="route('propietario.index')"
                                :active="route().current('propietario.index')">
                                Dashboard - Propietario
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('propietario.pagosRealizados')"
                                :active="route().current('propietario.pagosRealizados')">
                                Pagos Realizados Arrendatario
                            </ResponsiveNavLink>
                        </div>

                        <!-- Menú Vigilancia (rol 5) -->
                        <div v-if="userRoleId === 5" class="border-t border-gray-200 pt-2 px-4">
                            <p class="text-xs font-semibold text-gray-500 mb-1">Vigilancia</p>
                            <ResponsiveNavLink :href="route('vigilancia.index')"
                                :active="route().current('vigilancia.index')">
                                Vigilancia
                            </ResponsiveNavLink>
                        </div>

                        <!-- Sección Administración (móvil) -->
                        <div v-if="isSuperAdmin || userRoleId === 1 || userRoleId === 2"
                            class="border-t border-gray-200 pt-2">
                            <button @click="showAdminMobile = !showAdminMobile"
                                class="flex w-full items-center px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <span class="flex-1 text-left">Administración</span>
                                <svg :class="{ 'transform rotate-180': showAdminMobile }"
                                    class="h-4 w-4 ml-2 transition-transform duration-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <Transition name="slide-fade">
                                <div v-if="showAdminMobile" class="mt-1 space-y-1 pl-6">
                                    <ResponsiveNavLink v-if="userRoleId === 1 || isSuperAdmin"
                                        :href="route('condominios.index')">
                                        Condominios
                                    </ResponsiveNavLink>
                                    <ResponsiveNavLink :href="route('edificios.index')">
                                        Torres / Zonas
                                    </ResponsiveNavLink>
                                    <ResponsiveNavLink :href="route('extenciones.index')">
                                        Extenciones
                                    </ResponsiveNavLink>
                                    <ResponsiveNavLink :href="route('unidades.index')">
                                        Unidades / Lotes
                                    </ResponsiveNavLink>
                                    <ResponsiveNavLink :href="route('users.index')">
                                        Usuarios
                                    </ResponsiveNavLink>
                                </div>
                            </Transition>
                        </div>

                        <!-- Sección Configuraciones (móvil) -->
                        <div v-if="isSuperAdmin || userRoleId === 1 || userRoleId === 2"
                            class="border-t border-gray-200 pt-2">
                            <button @click="showConfigMobile = !showConfigMobile"
                                class="flex w-full items-center px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <span class="flex-1 text-left">Configuraciones</span>
                                <svg :class="{ 'transform rotate-180': showConfigMobile }"
                                    class="h-4 w-4 ml-2 transition-transform duration-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <Transition name="slide-fade">
                                <div v-if="showConfigMobile" class="mt-1 space-y-1 pl-6">
                                    <ResponsiveNavLink :href="route('periodos.index')">
                                        Conf. de Periodo
                                    </ResponsiveNavLink>
                                    <ResponsiveNavLink v-if="isSuperAdmin || userRoleId === 1"
                                        :href="route('tipos.index')">
                                        Conf. de Tipo Prorrateo General
                                    </ResponsiveNavLink>
                                    <ResponsiveNavLink :href="route('tipo_prorrateo_condominio.index')">
                                        Conf. de Tipo Prorrateo
                                    </ResponsiveNavLink>
                                    <ResponsiveNavLink :href="route('config_servicios.index')">
                                        Monto de Servicio
                                    </ResponsiveNavLink>
                                    <ResponsiveNavLink :href="route('DatosAdministrador.index')">
                                        Conf. de pagos (bancarios)
                                    </ResponsiveNavLink>
                                    <ResponsiveNavLink :href="route('ConfMora.index')">
                                        Conf. de Mora
                                    </ResponsiveNavLink>
                                </div>
                            </Transition>
                        </div>

                        <!-- Sección Operaciones (móvil) -->
                        <div v-if="isSuperAdmin || (userRoleId !== 3 && userRoleId !== 4 && userRoleId !== 5 && userRoleId !== 6)"
                            class="border-t border-gray-200 pt-2">
                            <button @click="showOpsMobile = !showOpsMobile"
                                class="flex w-full items-center px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <span class="flex-1 text-left">Operaciones</span>
                                <svg :class="{ 'transform rotate-180': showOpsMobile }"
                                    class="h-4 w-4 ml-2 transition-transform duration-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <Transition name="slide-fade">
                                <div v-if="showOpsMobile" class="mt-1 space-y-1 pl-6">
                                    <ResponsiveNavLink :href="route('categoria_gasto_comun.index')">
                                        Categorías Gasto Común
                                    </ResponsiveNavLink>
                                    <ResponsiveNavLink :href="route('tipo_gasto_comun.index')">
                                        Tipo Gasto Común
                                    </ResponsiveNavLink>
                                    <ResponsiveNavLink :href="route('gastos_comunes.index')">
                                        Gastos Comunes
                                    </ResponsiveNavLink>
                                    <ResponsiveNavLink :href="route('mediciones.index')">
                                        Mediciones
                                    </ResponsiveNavLink>
                                </div>
                            </Transition>
                        </div>

                    <!-- Sección Reportes (móvil) -->
                    <div v-if="isSuperAdmin || userRoleId === 1" class="border-t border-gray-200 pt-2 px-4">
                        <p class="text-xs font-semibold text-gray-500 mb-1">Reportes</p>
                        <ResponsiveNavLink :href="route('reporte.ingresos')">
                            Ingresos
                        </ResponsiveNavLink>
                    </div>
                </div>

                    <!-- Sección de perfil (móvil) -->
                    <div class="border-t border-gray-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-700">
                                {{ props.auth.user.name }} - {{ roleDescription }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ props.auth.user.email }}
                            </div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')" class="hover:bg-gray-100">
                                Mi Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="hover:bg-gray-100">
                                Cerrar sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </Transition>
            <!-- FIN Menú móvil -->

        </nav>

        <!-- Encabezado de la página (slot) -->
        <header class="bg-white shadow" v-if="$slots.header">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Contenido principal -->
        <main>
            <slot />
        </main>
    </div>
</template>

<style scoped>
/* Transición suave para expansión/contracción */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.3s ease-out;
    max-height: 500px; /* Altura máxima aproximada para permitir la animación */
    overflow: hidden;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    max-height: 0;
    opacity: 0;
    transform: translateY(-10px);
}
</style>
