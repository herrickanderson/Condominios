<template>
    <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Fondo Oscurecido -->
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>

        <!-- Modal -->
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full z-50">
            <div class="px-4 py-5">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ isEditing ? 'Corregir Comprobante' : 'Subir Comprobante' }}
                </h3>

                <!-- Mostramos errores si los hubiera -->
                <div v-if="form.errors" class="text-red-600 text-sm mb-4">
                    <ul>
                        <li v-for="(errorMsg, key) in form.errors" :key="key">
                            {{ errorMsg }}
                        </li>
                    </ul>
                </div>

                <form @submit.prevent="submitForm">
                    <!-- ID Gasto (oculto) -->
                    <input type="hidden" v-model="form.id_gasto" />

                    <!-- Monto -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="monto">
                            Monto
                        </label>
                        <input v-model="form.monto" id="monto" type="number" step="0.01" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" />
                    </div>

                    <!-- Fecha de Pago -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="fecha_pago">
                            Fecha de Pago
                        </label>
                        <input v-model="form.fecha_pago" id="fecha_pago" type="date" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" />
                    </div>

                    <!-- Archivo -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="archivo">
                            Archivo (Comprobante)
                        </label>
                        <input id="archivo" type="file" accept=".pdf, .jpg, .jpeg, .png" :required="!isEditing"
                            @change="handleFileChange"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" />
                        <p v-if="fileName" class="text-sm text-gray-500 mt-1">
                            Archivo seleccionado: {{ fileName }}
                        </p>
                    </div>

                    <!-- Observación -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="observacion">
                            Observación
                        </label>
                        <textarea v-model="form.observacion" id="observacion"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                            placeholder="Opcional"></textarea>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end">
                        <button type="button" @click="cancel"
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            {{ isEditing ? 'Actualizar' : 'Enviar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia'; // <-- IMPORTAMOS INERTIA

const props = defineProps({
    visible: { type: Boolean, default: false },
    idGasto: { type: [String, Number], default: null },
    paymentId: { type: [String, Number, null], default: null },
    monto: { type: [String, Number], default: '' },
    fechaPago: { type: String, default: '' },
});

const emits = defineEmits(['update:visible']);

// isEditing: si existe paymentId => estamos corrigiendo (update)
const isEditing = computed(() => props.paymentId != null);

// Inertia form
const form = useForm({
    id_gasto: props.idGasto,
    monto: props.monto,
    fecha_pago: props.fechaPago,
    archivo: null,
    observacion: ''
});

// Nombre del archivo seleccionado
const fileName = ref('');

// Reaccionar a cambios en las props
watch(() => props.idGasto, (val) => { form.id_gasto = val; });
watch(() => props.monto, (val) => { form.monto = val; });
watch(() => props.fechaPago, (val) => { form.fecha_pago = val; });

// Manejar cambio de archivo
function handleFileChange(e) {
    const file = e.target.files[0];
    form.archivo = file;
    fileName.value = file ? file.name : '';
}

// Enviar formulario
function submitForm() {
    // 1) Convertir manualmente a FormData para multipart/form-data
    form.transform((data) => {
        const formData = new FormData();
        for (const key in data) {
            formData.append(key, data[key]);
        }
        return formData;
    });

    if (isEditing.value && props.paymentId) {
        // POST => actualizar pago existente
        form.submit('post', route('pagos.update', props.paymentId), {
            onSuccess: () => {
                // Limpiamos el form
                form.reset();
                // Cerramos el modal
                emits('update:visible', false);
                // 2) Recargamos solo las props "pendientes" y "userPayments"
                Inertia.reload({ only: ['pendientes', 'userPayments'] });
            },
            onError: (errors) => {
                console.error('Error al actualizar pago', errors);
            }
        });
    } else {
        // POST => nuevo pago
        form.submit('post', route('pagos.store'), {
            onSuccess: () => {
                form.reset();
                emits('update:visible', false);
                // Recargamos las props para ver el nuevo estado
                Inertia.reload({ only: ['pendientes', 'userPayments'] });
            },
            onError: (errors) => {
                console.error('Error al crear pago', errors);
            }
        });
    }
}

// Cancelar => simplemente cerramos el modal
function cancel() {
    emits('update:visible', false);
}
</script>