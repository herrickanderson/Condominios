<template>
    <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-lg overflow-hidden shadow-xl w-11/12 md:w-3/4 lg:w-1/2 max-h-full">
        <!-- Cabecera del modal -->
        <div class="flex justify-between items-center p-4 border-b">
          <h3 class="text-lg font-semibold">Visualizar Archivo</h3>
          <button @click="closeModal" class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>
        <!-- Contenido del modal -->
        <div class="p-4 overflow-auto" style="max-height: 80vh;">
          <!-- Si es PDF, se muestra en un iframe -->
          <iframe
            v-if="isPdf"
            :src="archivoUrl"
            class="w-full h-96"
            frameborder="0"
          ></iframe>
  
          <!-- Si es imagen, se muestra con <img> -->
          <img
            v-else-if="isImage"
            :src="archivoUrl"
            alt="Archivo"
            class="mx-auto max-w-full max-h-full object-contain"
          />
  
          <!-- Si es otro tipo de archivo, se muestra mensaje con opción de abrir en nueva pestaña -->
          <div v-else class="text-center">
            <p>Tipo de archivo no soportado.</p>
            <a
              :href="archivoUrl"
              target="_blank"
              class="text-blue-600 underline"
            >
              Abrir en nueva pestaña
            </a>
          </div>
        </div>
        <!-- Pie del modal -->
        <div class="flex justify-end p-4 border-t">
          <button
            @click="closeModal"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { computed } from 'vue';
  
  const props = defineProps({
    visible: { type: Boolean, default: false },
    archivoUrl: { type: String, required: true }
  });
  
  const emit = defineEmits(['update:visible']);
  
  function closeModal() {
    emit('update:visible', false);
  }
  
  const fileExtension = computed(() => {
    if (!props.archivoUrl) return '';
    return props.archivoUrl.split('.').pop().toLowerCase();
  });
  
  const isPdf = computed(() => fileExtension.value === 'pdf');
  const isImage = computed(() => ['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension.value));
  </script>
  
  <style scoped>
  /* Ajusta estilos según tus necesidades */
  </style>
  