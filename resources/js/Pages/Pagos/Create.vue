<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

// Obtenemos el id_gasto de los parámetros de la URL (si se envía como query)
const urlParams = new URLSearchParams(window.location.search);
const idGasto = urlParams.get('id_gasto');

const form = useForm({
    id_gasto: idGasto || null,
    monto: '',
    fecha_pago: '',
    archivo: null,
    observacion: ''
});

const fileName = ref('');
const showModal = ref(false);

// Función para manejar el cambio en el input file
function handleFileChange(e) {
    const file = e.target.files[0];
    form.archivo = file;
    fileName.value = file ? file.name : '';
}

// Al hacer clic en el botón de enviar, se muestra el modal de confirmación
function submitForm() {
    if (!form.archivo) {
        alert('Debes seleccionar un archivo.');
        return;
    }
    showModal.value = true;
}

// Cuando se confirma en el modal, se envía el formulario
function confirmSubmit() {
    form.post(route('pagos.store'), {
        onSuccess: () => {
            // Puedes agregar una notificación o redirigir a la lista de pagos
            // El controlador asigna el estado "Enviado", por lo que en la vista de pendientes se verá como pendiente de aceptación
        }
    });
}

// Cancelar el envío (cerrar el modal)
function cancelSubmit() {
    showModal.value = false;
}
</script>

<template>

    <Head title="Subir Comprobante" />

    <div class="max-w-3xl mx-auto py-8">
        <h1 class="text-2xl font-bold mb-4">Subir Comprobante</h1>
        <form @submit.prevent="submitForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <!-- Campo oculto para id_gasto -->
            <input type="hidden" v-model="form.id_gasto">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="monto">
                    Monto
                </label>
                <input v-model="form.monto" id="monto" type="number" step="0.01" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="fecha_pago">
                    Fecha de Pago
                </label>
                <input v-model="form.fecha_pago" id="fecha_pago" type="date" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="archivo">
                    Archivo (Comprobante)
                </label>
                <input @change="handleFileChange" id="archivo" type="file" accept=".pdf, .jpg, .jpeg, .png" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <p v-if="fileName" class="text-sm text-gray-500 mt-1">
                    Archivo seleccionado: {{ fileName }}
                </p>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="observacion">
                    Observación
                </label>
                <textarea v-model="form.observacion" id="observacion"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Opcional"></textarea>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Revisar Comprobante
                </button>
                <Link :href="route('pagos.index')"
                    class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Cancelar
                </Link>
            </div>
        </form>

        <!-- Modal de confirmación -->
        <div v-if="showModal" class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen">
                <div class="fixed inset-0 bg-gray-500 opacity-75"></div>
                <div
                    class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full z-20">
                    <div class="px-4 py-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Confirmar envío
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Revisa el comprobante seleccionado: <strong>{{ fileName }}</strong>. ¿Deseas enviarlo?
                            </p>
                        </div>
                    </div>
                    <div class="px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="confirmSubmit" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Enviar
                        </button>
                        <button @click="cancelSubmit" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
