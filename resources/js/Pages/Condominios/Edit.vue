<script setup>
import { ref, onMounted, nextTick } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import FileInput from '@/Components/FileInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const page = usePage();
const condominio = ref(page.props.condominio);

const awsBaseUrl = import.meta.env.VITE_AWS_URL;
const getImageUrl = (path) => (path ? `${awsBaseUrl}/${path}` : null);

const initialValues = {
    nombre: condominio.value.nombre,
    rut: condominio.value.rut,
    direccion: condominio.value.direccion,
    telefono: condominio.value.telefono,
    email: condominio.value.email,
    logo: null,
    firma: null,
    fecha_contable_inicial: condominio.value.fecha_contable_inicial
        ? condominio.value.fecha_contable_inicial.split('T')[0]
        : '',
    fondo_reserva:
        condominio.value.fondo_reserva !== undefined
            ? String(condominio.value.fondo_reserva)
            : '',
    datos_bancarios: condominio.value.datos_bancarios,
    // Si usas mandato_khipu, descomenta la siguiente línea:
    // mandato_khipu: condominio.value.mandato_khipu,
    // Nuevos campos para la ubicación
    latitude: condominio.value.latitude,
    longitude: condominio.value.longitude,
};

const form = useForm(initialValues);

const onSelectLogo = (e) => {
    const files = e.target.files;
    if (files.length) {
        form.logo = files[0];
    }
};

const onSelectFirma = (e) => {
    const files = e.target.files;
    if (files.length) {
        form.firma = files[0];
    }
};

const submit = () => {
    form.post(route('condominios.update', condominio.value), {
        onSuccess: (page) => {
            condominio.value = page.props.condominio;
        },
    });
};

// Variables para el mapa
const map = ref(null);
const marker = ref(null);
const mapCenter = ref({
    lat: form.latitude ? parseFloat(form.latitude) : -33.4489,
    lng: form.longitude ? parseFloat(form.longitude) : -70.6693,
});

const initMap = () => {
    map.value = new google.maps.Map(document.getElementById('map'), {
        center: mapCenter.value,
        zoom: 15,
    });

    marker.value = new google.maps.Marker({
        position: mapCenter.value,
        map: map.value,
        draggable: true,
    });

    // Actualiza los campos cuando se mueve el marcador
    marker.value.addListener('dragend', (event) => {
        form.latitude = event.latLng.lat();
        form.longitude = event.latLng.lng();
    });

    // Configuración del autocomplete
    const input = document.getElementById('pac-input');
    const autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.bindTo('bounds', map.value);

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.geometry) {
            alert('No se encontraron detalles para: ' + place.name);
            return;
        }
        map.value.setCenter(place.geometry.location);
        marker.value.setPosition(place.geometry.location);
        form.latitude = place.geometry.location.lat();
        form.longitude = place.geometry.location.lng();
    });
};

onMounted(() => {
    nextTick(() => {
        if (window.google && window.google.maps) {
            initMap();
        } else {
            console.error('Google Maps no se ha cargado correctamente');
        }
    });
});
</script>

<template>

    <Head title="Actualizar Condominios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h2 class="text-xl font-semibold text-white">Condominios</h2>
                <Link :href="route('condominios.index')" class="text-white hover:underline font-semibold">
                Lista de Condominios
                </Link>
            </div>
        </template>

        <div class="py-12 flex justify-center">
            <div class="w-full max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200">
                <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">
                    Actualizar Condominio
                </h3>
                <form class="space-y-6" @submit.prevent="submit">
                    <!-- Mensaje de éxito -->
                    <transition enter-active-class="transition ease-out duration-300"
                        enter-from-class="opacity-0 transform -translate-y-1"
                        enter-to-class="opacity-100 transform translate-y-0"
                        leave-active-class="transition ease-in duration-300"
                        leave-from-class="opacity-100 transform translate-y-0"
                        leave-to-class="opacity-0 transform -translate-y-1">
                        <div v-if="form.recentlySuccessful"
                            class="flex items-center p-3 mb-4 text-green-700 bg-green-100 border border-green-200 rounded-md shadow">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414L9 14.414l-3.707-3.707a1 1 0 011.414-1.414L9 11.586l6.293-6.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-sm font-medium">¡Condominio actualizado correctamente!</p>
                        </div>
                    </transition>

                    <!-- Campos generales -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="nombre" value="Nombre" />
                            <TextInput id="nombre" type="text" v-model="form.nombre" required autofocus
                                placeholder="Ejemplo: Residencial Los Álamos" class="w-full" />
                            <InputError :message="form.errors.nombre" />
                        </div>
                        <div>
                            <InputLabel for="rut" value="Documento" />
                            <TextInput id="rut" type="text" v-model="form.rut" required class="w-full" />
                            <InputError :message="form.errors.rut" />
                        </div>
                        <div>
                            <InputLabel for="direccion" value="Dirección" />
                            <TextInput id="direccion" type="text" v-model="form.direccion" required
                                placeholder="Av. Principal #123" class="w-full" />
                            <InputError :message="form.errors.direccion" />
                        </div>
                        <div>
                            <InputLabel for="telefono" value="Teléfono" />
                            <TextInput id="telefono" type="text" v-model="form.telefono" placeholder="+56 9 1234 5678"
                                class="w-full" />
                            <InputError :message="form.errors.telefono" />
                        </div>
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" type="email" v-model="form.email" placeholder="correo@ejemplo.com"
                                class="w-full" />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div>
                            <InputLabel for="fecha_contable_inicial" value="Fecha Contable Inicial" />
                            <TextInput id="fecha_contable_inicial" type="date" v-model="form.fecha_contable_inicial"
                                class="w-full" />
                        </div>
                        <div>
                            <InputLabel for="fondo_reserva" value="Fondo de Reserva" />
                            <TextInput id="fondo_reserva" type="number" step="0.01" v-model="form.fondo_reserva"
                                class="w-full" @input="form.fondo_reserva = String(form.fondo_reserva)" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="datos_bancarios" value="Información Adicional" />
                        <textarea id="datos_bancarios" v-model="form.datos_bancarios" rows="3"
                            class="w-full border-gray-300 rounded-md p-2"></textarea>
                    </div>

                    <!-- Sección de archivos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Card para Logo -->
                        <div class="flex flex-col items-center p-4 border rounded-lg shadow-sm">
                            <div class="mb-2">
                                <img class="h-20 w-auto object-contain" :src="condominio.logo_url" alt="Logo" />
                            </div>
                            <InputLabel for="logo" value="Logo" class="mb-1" />
                            <FileInput name="logo" @change="onSelectLogo" class="w-full" />
                        </div>
                        <!-- Card para Firma -->
                        <div class="flex flex-col items-center p-4 border rounded-lg shadow-sm">
                            <div class="mb-2">
                                <img class="h-20 w-auto object-contain" :src="condominio.firma_url" alt="Firma" />
                            </div>
                            <InputLabel for="firma" value="Firma" class="mb-1" />
                            <FileInput name="firma" @change="onSelectFirma" class="w-full" />
                        </div>
                    </div>

                    <!-- Sección de ubicación -->
                    <div class="mt-6">
                        <InputLabel for="location" value="Buscar ubicación" />
                        <input id="pac-input" type="text" placeholder="Buscar ubicación"
                            class="w-full border p-2 mb-4" />
                        <div id="map" style="height: 400px; width: 100%;"></div>
                    </div>

                    <!-- Campos para latitud y longitud -->
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="latitude" value="Latitud" />
                            <TextInput id="latitude" type="text" v-model="form.latitude" readonly class="w-full" />
                        </div>
                        <div>
                            <InputLabel for="longitude" value="Longitud" />
                            <TextInput id="longitude" type="text" v-model="form.longitude" readonly class="w-full" />
                        </div>
                    </div>

                    <div class="flex justify-center mt-6">
                        <PrimaryButton>Actualizar Condominio</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
