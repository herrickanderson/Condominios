<template>
    <div class="relative">
        <InputLabel :for="id" :value="label" />
        <TextInput :id="id" type="text" v-model="searchTerm" @focus="mostrarOpciones = true" @blur="ocultarOpciones"
            placeholder="Busca..." class="w-full" />
        <div v-if="mostrarOpciones && opcionesFiltradas.length"
            class="absolute z-10 w-full bg-white border rounded mt-1 max-h-60 overflow-auto">
            <div v-for="opcion in opcionesFiltradas" :key="opcion.id" class="cursor-pointer p-2 hover:bg-gray-200"
                @mousedown.prevent="seleccionarOpcion(opcion)">
                {{ opcion.nombre }}
            </div>
        </div>
        <InputError :message="errorMessage" />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    id: { type: String, default: 'searchable-select' },
    label: { type: String, default: 'Selecciona una opción' },
    opciones: { type: Array, default: () => [] },
    modelValue: { type: [String, Number, Object], default: null },
    errorMessage: { type: String, default: '' }
});

const emit = defineEmits(['update:modelValue']);

const searchTerm = ref('');
const mostrarOpciones = ref(false);

// Filtra las opciones basadas en el término de búsqueda
const opcionesFiltradas = computed(() => {
    if (!searchTerm.value) return props.opciones;
    return props.opciones.filter(opcion =>
        opcion.nombre.toLowerCase().includes(searchTerm.value.toLowerCase())
    );
});

// Cuando se selecciona una opción, se actualiza el modelo y se ocultan las opciones
const seleccionarOpcion = (opcion) => {
    emit('update:modelValue', opcion);
    searchTerm.value = opcion.nombre;
    mostrarOpciones.value = false;
};

// Para manejar el blur sin que se cierre antes del click
const ocultarOpciones = () => {
    // Pequeño retardo para permitir el click en la opción
    setTimeout(() => {
        mostrarOpciones.value = false;
    }, 100);
};

// Si el modelo ya tiene un valor, muestra su nombre
if (props.modelValue && typeof props.modelValue === 'object' && props.modelValue.nombre) {
    searchTerm.value = props.modelValue.nombre;
}
</script>

<style scoped>
/* Puedes agregar estilos personalizados aquí */
</style>
