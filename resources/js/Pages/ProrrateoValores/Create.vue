<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    // Lista de tipos de prorrateo (filtrados por condominio si el usuario es administrador)
    tipos: {
        type: Array,
        default: () => [],
    },
    // Para SuperAdmin, se envían los condominios
    condominios: {
        type: Array,
        default: () => [],
    },
});

// Configuramos el formulario sin campo de usuario, ya que la relación se hace a través del usuario asignado en el controlador.
const form = useForm({
    tipo_prorrateo_id: '',
    id_condominio: '',
    criterio: '',
    valor_criterio: '',
});

// Si el usuario autenticado ya tiene un condominio asignado, en el controlador se forzará ese valor.
// Para SuperAdmin (cuando se envían condominios) se asigna por defecto el primer condominio.
if (!form.id_condominio && props.condominios.length > 0) {
    form.id_condominio = props.condominios[0].id;
}

const submit = () => {
    form.post(route('prorrateos.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>

    <Head title="Crear Prorrateo" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Crear Prorrateo</h3>
                <Link :href="route('prorrateos.index')" class="text-white hover:underline font-semibold">
                Listado de Prorrateos
                </Link>
            </div>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200">
                <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">Registrar Prorrateo</h3>
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="criterio" value="Criterio" />
                        <TextInput id="criterio" type="text" v-model="form.criterio" required class="w-full" />
                        <InputError :message="form.errors.criterio" />
                    </div>
                    <div>
                        <InputLabel for="valor_criterio" value="Valor" />
                        <TextInput id="valor_criterio" type="number" v-model="form.valor_criterio" required
                            class="w-full" />
                        <InputError :message="form.errors.valor_criterio" />
                    </div>
                    <div>
                        <InputLabel for="tipo_prorrateo_id" value="Tipo Prorrateo" />
                        <select id="tipo_prorrateo_id" v-model="form.tipo_prorrateo_id" required
                            class="w-full border-gray-300 rounded-md shadow-sm">
                            <option disabled value="">Seleccione un tipo</option>
                            <option v-for="tipo in props.tipos" :key="tipo.id" :value="tipo.id">
                                {{ tipo.descripcion }}
                            </option>
                        </select>
                        <InputError :message="form.errors.tipo_prorrateo_id" />
                    </div>
                    <!-- Mostrar el select de condominio solo para SuperAdmin -->
                    <div v-if="props.condominios.length > 0">
                        <InputLabel for="id_condominio" value="Condominio" />
                        <select id="id_condominio" v-model="form.id_condominio" required
                            class="w-full border-gray-300 rounded-md shadow-sm">
                            <option disabled value="">Seleccione un condominio</option>
                            <option v-for="cond in props.condominios" :key="cond.id" :value="cond.id">
                                {{ cond.nombre }}
                            </option>
                        </select>
                        <InputError :message="form.errors.id_condominio" />
                    </div>
                    <div class="flex justify-center">
                        <PrimaryButton>Registrar Prorrateo</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
