<script setup>
import { onMounted, ref } from 'vue';



const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input type="file" class=" border-gray-300  focus:border-indigo-500 focus:ring-indigo-500" ref="input" />
</template>
