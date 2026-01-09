<template>
    <transition name="modal-fade">
        <div v-if="visible" class="fixed inset-0 flex items-center justify-center z-50">
            <!-- Fondo semitransparente con desenfoque -->
            <div class="fixed inset-0 bg-black opacity-50 backdrop-blur-sm"></div>
            <!-- Contenedor del modal -->
            <div class="bg-white rounded-lg shadow-lg z-10 w-11/12 max-w-md p-6">
                <h3 class="text-xl font-semibold text-center mb-4">Confirmación</h3>
                <p class="text-gray-700 text-center mb-6">{{ message }}</p>
                <div class="flex justify-center space-x-4">
                    <button @click="$emit('cancel')"
                        class="px-4 py-2 bg-gray-200 text-gray-800 font-semibold rounded hover:bg-gray-300 transition duration-200">
                        Cancelar
                    </button>
                    <button @click="$emit('confirm')"
                        class="px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition duration-200">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    message: {
        type: String,
        default: "¿Está seguro?"
    },
    visible: {
        type: Boolean,
        default: false
    }
});
</script>

<style>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.5s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
</style>