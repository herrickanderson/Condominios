<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    categoria: {
        type: Object,
        required: true,
    },
    condominios: {
        type: Array,
        default: () => [],
    }
});

const form = useForm({
    nombre: props.categoria.nombre,
    id_condominio: props.categoria.id_condominio,
});

const submit = () => {
    form.put(route('categoria_gasto_comun.update', props.categoria.id_categoria), {
        onSuccess: () => {
            // Opcional: acciones después de actualizar
        }
    });
};
</script>

<template>
  <Head title="Editar Categoría de Gasto Común" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
        <h3 class="text-xl font-semibold text-white">Estas en: Categorias de gasto comun / Editar</h3>
        <Link :href="route('categoria_gasto_comun.index')" class="text-white hover:underline font-semibold">
          <-Listado de Categorías
        </Link>
      </div>
    </template>
    <div class="py-12 flex justify-center">
      <div class="w-full max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200">
        <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">Actualizar Categoría</h3>
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <InputLabel for="nombre" value="Nombre" />
            <TextInput id="nombre" type="text" v-model="form.nombre" required class="w-full" />
            <InputError :message="form.errors.nombre" />
          </div>
          <!-- Si es SuperAdmin, mostrar condominio -->
          <div v-if="condominios.length > 0">
            <InputLabel for="id_condominio" value="Condominio" />
            <select id="id_condominio" v-model="form.id_condominio" required class="w-full border-gray-300 rounded-md shadow-sm">
              <option disabled value="">Seleccione un condominio</option>
              <option v-for="cond in condominios" :key="cond.id" :value="cond.id">
                {{ cond.nombre }}
              </option>
            </select>
            <InputError :message="form.errors.id_condominio" />
          </div>
          <div class="flex justify-center">
            <PrimaryButton>Actualizar Categoría</PrimaryButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
