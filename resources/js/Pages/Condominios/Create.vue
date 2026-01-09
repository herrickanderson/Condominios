<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import FileInput from '@/Components/FileInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// Valores iniciales con coordenadas por defecto y dirección vacía (se llenará automáticamente)
const initialValues = {
    nombre: "",
    rut: "",
    direccion: "",
    telefono: "",
    email: "",
    logo: null,
    firma: null,
    fecha_contable_inicial: "",
    // Eliminamos fondo_reserva para no enviarlo
    datos_bancarios: "",
    mandato_khipu: "",
    latitude: "-33.4489",    // Ejemplo: Santiago de Chile
    longitude: "-70.6693"
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

// Modal minimalista de confirmación
const locationModalVisible = ref(false);

// Al enviar, mostramos el modal
function submit() {
    locationModalVisible.value = true;
}

// Si confirma, se envía el formulario
function confirmLocation() {
    locationModalVisible.value = false;
    form.post(route('condominios.store'), {
        onSuccess: () => {
            form.reset('nombre', 'rut', 'direccion', 'telefono', 'email', 'logo', 'firma', 'fecha_contable_inicial', 'datos_bancarios', 'mandato_khipu', 'latitude', 'longitude');
        },
    });
}

// Si cancela, se cierra el modal sin enviar
function cancelLocation() {
    locationModalVisible.value = false;
}

const map = ref(null);
const marker = ref(null);
const mapCenter = ref({ lat: parseFloat(form.latitude), lng: parseFloat(form.longitude) });

const initMap = () => {
    if (!document.getElementById("map")) return;
    
    map.value = new google.maps.Map(document.getElementById("map"), {
        center: mapCenter.value,
        zoom: 15,
    });

    marker.value = new google.maps.Marker({
        position: mapCenter.value,
        map: map.value,
        draggable: true,
    });

    const geocoder = new google.maps.Geocoder();

    marker.value.addListener("dragend", (event) => {
        const lat = event.latLng.lat();
        const lng = event.latLng.lng();
        form.latitude = String(lat);
        form.longitude = String(lng);

        // Reverse Geocoding para obtener la dirección al arrastrar
        geocoder.geocode({ location: { lat, lng } }, (results, status) => {
            if (status === "OK" && results[0]) {
                form.direccion = results[0].formatted_address;
            } else {
                console.warn("No se pudo obtener la dirección: " + status);
            }
        });
    });

    // Configuramos el autocomplete para "Buscar dirección"
    const input = document.getElementById("pac-input");
    if (input) {
        input.placeholder = "Buscar dirección";

        const autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo("bounds", map.value);

        autocomplete.addListener("place_changed", () => {
            const place = autocomplete.getPlace();
            if (!place.geometry) {
                alert("No se encontraron detalles para: " + place.name);
                return;
            }
            map.value.setCenter(place.geometry.location);
            marker.value.setPosition(place.geometry.location);
            form.latitude = String(place.geometry.location.lat());
            form.longitude = String(place.geometry.location.lng());
            
            // Actualizamos el campo dirección
            if (place.formatted_address) {
                form.direccion = place.formatted_address;
            } else if (place.name) {
                form.direccion = place.name;
            }
        });
    }
};

onMounted(() => {
    const checkGoogleMaps = () => {
        if (window.google && window.google.maps && typeof google.maps.Map === 'function') {
            initMap();
            return true;
        }
        return false;
    };

    if (!checkGoogleMaps()) {
        // Reintentar carga
        const interval = setInterval(() => {
            if (checkGoogleMaps()) {
                clearInterval(interval);
            }
        }, 200);
        
        // Timeout de seguridad (5 segundos)
        setTimeout(() => clearInterval(interval), 5000);
    }
});
</script>

<template>
    <Head title="Crear Condominios" />
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
                <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">Registrar Condominio</h3>
                <form class="space-y-6" @submit.prevent="submit">
                    <!-- Campos principales en grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="nombre" value="Nombre" />
                            <TextInput id="nombre" type="text" v-model="form.nombre" required autofocus placeholder="Ej: Condominio Los Laureles" class="w-full" />
                            <p class="text-xs text-gray-500 mt-1">Nombre oficial del condominio o edificio</p>
                            <InputError :message="form.errors.nombre" />
                        </div>
                        <div>
                            <InputLabel for="rut" value="Documento" />
                            <TextInput id="rut" type="text" v-model="form.rut" required placeholder="76.123.456-7" class="w-full" />
                            <p class="text-xs text-gray-500 mt-1">RUT o documento de identificación fiscal</p>
                            <InputError :message="form.errors.rut" />
                        </div>
                        <div>
                            <InputLabel for="direccion" value="Dirección" />
                            <!-- Campo dirección se llena desde el mapa y es readonly -->
                            <TextInput id="direccion" type="text" v-model="form.direccion" required readonly class="w-full bg-gray-100 cursor-not-allowed" placeholder="Se autocompletará al seleccionar en el mapa" />
                            <p class="text-xs text-blue-600 mt-1">📍 Busca y arrastra el marcador en el mapa de abajo</p>
                            <InputError :message="form.errors.direccion" />
                        </div>
                        <div>
                            <InputLabel for="telefono" value="Teléfono" />
                            <TextInput id="telefono" type="text" v-model="form.telefono" placeholder="+56 9 1234 5678" class="w-full" />
                            <InputError :message="form.errors.telefono" />
                        </div>
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" type="email" v-model="form.email" placeholder="admin@condominio.com" class="w-full" />
                            <p class="text-xs text-gray-500 mt-1">Correo de contacto principal del condominio</p>
                            <InputError :message="form.errors.email" />
                        </div>
                        <div>
                            <InputLabel for="fecha_contable_inicial" value="Fecha Contable Inicial" />
                            <TextInput id="fecha_contable_inicial" type="date" v-model="form.fecha_contable_inicial" class="w-full" />
                        </div>
                        <!-- Campo fondo_reserva eliminado -->
                    </div>

                    <div>
                        <InputLabel for="datos_bancarios" value="Información Adicional" />
                        <textarea id="datos_bancarios" v-model="form.datos_bancarios" rows="3" class="w-full border-gray-300 rounded-md p-2"></textarea>
                    </div>

                    <!-- Sección para archivos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="logo" value="Logo" />
                            <FileInput name="logo" @change="onSelectLogo" class="w-full" />
                        </div>
                        <div>
                            <InputLabel for="firma" value="Firma" />
                            <FileInput name="firma" @change="onSelectFirma" class="w-full" />
                        </div>
                    </div>

                    <!-- Sección del mapa y búsqueda de dirección -->
                    <div class="mt-6">
                        <InputLabel for="location" value="Buscar dirección" />
                        <input id="pac-input" type="text" placeholder="Buscar dirección" class="w-full border p-2 mb-4" />
                        <div id="map" style="height: 400px; width: 100%;"></div>
                    </div>

                    <!-- Campos de latitud y longitud ocultos -->
                    <div class="hidden">
                        <InputLabel for="latitude" value="Latitud" />
                        <TextInput id="latitude" type="text" v-model="form.latitude" readonly class="w-full" />
                        <InputLabel for="longitude" value="Longitud" />
                        <TextInput id="longitude" type="text" v-model="form.longitude" readonly class="w-full" />
                    </div>

                    <div class="flex justify-center mt-6">
                        <PrimaryButton>Crear Condominio</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal minimalista de confirmación -->
        <transition name="fade">
            <div v-if="locationModalVisible" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white rounded-lg p-6 w-11/12 md:w-1/3 shadow-lg">
                    <h3 class="text-xl font-bold mb-4 text-center">Confirmar Datos</h3>
                    <p class="mb-4 text-center">¿Los datos registrados son correctos?</p>
                    <div class="flex justify-around">
                        <PrimaryButton @click="confirmLocation">Sí</PrimaryButton>
                        <PrimaryButton class="bg-red-600 hover:bg-red-700" @click="cancelLocation">No</PrimaryButton>
                    </div>
                </div>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
