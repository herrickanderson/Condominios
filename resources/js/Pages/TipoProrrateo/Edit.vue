<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    tipo: {
        type: Object,
        required: true,
    },
    condominios: {
        type: Array,
        default: () => [],
    },
});

// Extraemos el rol del usuario
const { props: pageProps } = usePage();
const userRole = computed(() => pageProps.auth.user?.roles?.[0] ?? null);
const userRoleId = computed(() => userRole.value?.id_rol ?? null);

const form = useForm({
    descripcion: props.tipo.descripcion,
    id_condominio: props.tipo.id_condominio,
});

const submit = () => {
    form.put(route('tipos.update', props.tipo.id), {
        onSuccess: () => {
            // Opcional: acciones después de actualizar
        },
    });
};
</script>

<template>
    <Head title="Editar Tipo Prorrateo" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white">Editar Tipo Prorrateo</h3>
                <Link :href="route('tipos.index')" class="text-white hover:underline font-semibold">
                    Listado de Tipos
                </Link>
            </div>
        </template>
        <div class="py-12">
            <!-- Mostrar formulario solo si el usuario es superadmin -->
            <div v-if="userRoleId === 1 || !userRoleId" class="mx-auto max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200">
                <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">Editar Tipo Prorrateo</h3>
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="descripcion" value="Descripción" />
                        <TextInput id="descripcion" type="text" v-model="form.descripcion" required class="w-full" />
                        <InputError :message="form.errors.descripcion" />
                    </div>
                    <div v-if="props.condominios.length > 0">
                        <InputLabel for="id_condominio" value="Condominio" />
                        <select id="id_condominio" v-model="form.id_condominio" required class="w-full border-gray-300 rounded-md shadow-sm">
                            <option disabled value="">Seleccione un condominio</option>
                            <option v-for="cond in props.condominios" :key="cond.id" :value="cond.id">
                                {{ cond.nombre }}
                            </option>
                        </select>
                        <InputError :message="form.errors.id_condominio" />
                    </div>
                    <div class="flex justify-center">
                        <PrimaryButton>Actualizar Tipo</PrimaryButton>
                    </div>
                </form>
            </div>
            <!-- Mensaje si no tiene permisos -->
            <div v-else class="mx-auto max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200 text-center">
                <h3 class="text-2xl font-bold text-gray-700 mb-6">No tienes permisos para editar este tipo de prorrateo</h3>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
