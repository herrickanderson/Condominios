<script setup>
import { ref, onMounted, watch, defineProps } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

// Componentes base
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SearchableSelect from "@/Components/SearchableSelect.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

// Íconos de Heroicons (24/solid)
import {
    BuildingOfficeIcon,
    ArrowLeftIcon,
    CheckCircleIcon
} from "@heroicons/vue/24/solid";

const props = defineProps({
    condominios: {
        type: Array,
        default: () => [],
    },
    tipoProrrateo: {
        type: Array,
        default: () => [],
    },
    prorrateoCondominio: {
        type: Object,
        default: () => ({}),
    },
});

const { auth } = usePage().props;

const form = useForm({
    nombre: "",
    id_condominio: auth.user && auth.user.id_condominio ? auth.user.id_condominio : "",
    // Radio: 0 = usar prorrateo del condominio (por defecto), 1 = prorrateo individual
    aplica_prorrateo: 0,
    tipo_prorrateo_id: ""
});

const selectedCondominio = ref(null);

onMounted(() => {
    if (auth.user && auth.user.id_condominio) {
        selectedCondominio.value = props.condominios.find(
            (c) => c.id === auth.user.id_condominio
        );
    } else if (props.condominios.length > 0 && !form.id_condominio) {
        form.id_condominio = props.condominios[0].id;
        selectedCondominio.value = props.condominios[0];
    }
});

watch(selectedCondominio, (nuevoValor) => {
    form.id_condominio = nuevoValor ? nuevoValor.id : "";
});

const submit = () => {
    // Si el condominio seleccionado no tiene prorrateo configurado, no permitimos enviar el formulario
    if (selectedCondominio && !props.prorrateoCondominio[selectedCondominio.value ? selectedCondominio.value.id : form.id_condominio]) {
        return;
    }
    form.post(route("edificios.store"), {
        onSuccess: () => {
            form.reset();
            if (!auth.user.id_condominio && props.condominios.length > 0) {
                form.id_condominio = props.condominios[0].id;
                selectedCondominio.value = props.condominios[0];
            }
        },
    });
};
</script>

<template>

    <Head title="Crear Edificio" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white flex items-center space-x-2">
                    <BuildingOfficeIcon class="h-6 w-6" />
                    <span>Estas en: Edificios / Crear</span>
                </h3>
                <Link :href="route('edificios.index')"
                    class="inline-flex items-center text-white hover:underline font-semibold">
                <ArrowLeftIcon class="h-5 w-5 mr-1" />
                Listar Edificios
                </Link>
            </div>
        </template>

        <div class="py-12 flex justify-center">
            <div class="w-full max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200">
                <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">
                    Registrar Edificio / Lote
                </h3>

                <form v-if="props.condominios.length > 0 || auth.user?.id_condominio" class="space-y-6"
                    @submit.prevent="submit">
                    <div>
                        <InputLabel for="nombre" value="Nombre del Edificio / Lote" />
                        <TextInput id="nombre" type="text" v-model="form.nombre" required class="w-full" />
                        <InputError :message="form.errors.nombre" />
                    </div>

                    <div v-if="!auth.user?.id_condominio" class="mb-4">
                        <SearchableSelect id="id_condominio" label="Selecciona un Condominio"
                            :opciones="props.condominios" v-model="selectedCondominio"
                            :errorMessage="form.errors.id_condominio" />
                    </div>
                    <div v-else class="mb-4">
                        <InputLabel value="Condominio asignado" />
                        <div class="border border-gray-300 rounded-md p-2">
                            {{ selectedCondominio ? selectedCondominio.nombre : 'Sin asignar' }}
                        </div>
                        <input type="hidden" v-model="form.id_condominio" />
                    </div>

                    <!-- Mostrar prorrateo del condominio seleccionado -->
                    <div class="mb-4" v-if="selectedCondominio">
                        <p class="text-sm text-gray-700">
                            Prorrateo de Condominio:
                            <span class="font-semibold">
                                {{ props.prorrateoCondominio[selectedCondominio.id]?.descripcion || "Sin prorrateo" }}
                            </span>
                        </p>
                    </div>

                    <!-- Si no existe prorrateo, se muestra el mensaje y el enlace para configurarlo -->
                    <div v-if="selectedCondominio && !props.prorrateoCondominio[selectedCondominio.id]" class="mb-4">
                        <p class="text-red-600 text-sm mb-2">
                            No existe configuración de prorrateo para el condominio seleccionado. Por favor, configúralo
                            primero.
                        </p>
                        <DropdownLink :href="route('tipo_prorrateo_condominio.index')"
                            class="bg-blue-500 text-white font-bold py-2 px-4 rounded inline-block border border-transparent hover:bg-white hover:text-blue-500 hover:border-blue-500">
                            Conf. de Tipo Prorrateo
                        </DropdownLink>
                    </div>

                    <!-- Radio buttons para la pregunta -->
                    <div class="mb-4">
                        <InputLabel value="¿Desea aplicar el prorrateo del condominio?" />
                        <div class="flex items-center space-x-4 mt-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="aplica_prorrateo" value="0" v-model="form.aplica_prorrateo"
                                    class="mr-2" />
                                Sí
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="aplica_prorrateo" value="1" v-model="form.aplica_prorrateo"
                                    class="mr-2" />
                                No
                            </label>
                        </div>
                        <InputError :message="form.errors.aplica_prorrateo" />
                    </div>

                    <!-- Si se selecciona "No" (prorrateo individual), se muestra el selector -->
                    <div class="mb-4" v-if="parseInt(form.aplica_prorrateo) === 1">
                        <InputLabel for="tipo_prorrateo_id" value="Seleccione el tipo de prorrateo individual" />
                        <select id="tipo_prorrateo_id" v-model="form.tipo_prorrateo_id"
                            class="w-full border rounded p-2">
                            <option value="">Seleccione un tipo</option>
                            <option v-for="tipo in tipoProrrateo" :key="tipo.id" :value="tipo.id">
                                {{ tipo.descripcion }}
                            </option>
                        </select>
                        <InputError :message="form.errors.tipo_prorrateo_id" />
                    </div>

                    <div class="flex justify-center">
                        <PrimaryButton
                            :disabled="selectedCondominio && !props.prorrateoCondominio[selectedCondominio.id]">
                            <div class="flex items-center space-x-1">
                                <CheckCircleIcon class="h-5 w-5" />
                                <span>Crear Edificio</span>
                            </div>
                        </PrimaryButton>
                    </div>
                </form>
                <div v-else class="text-red-600 text-center text-sm font-semibold mt-4">

                    <DropdownLink :href="route('condominios.create')"
                        class="bg-blue-500 text-white font-bold py-2 px-4 rounded inline-block border border-transparent hover:bg-white hover:text-blue-500 hover:border-blue-500">
                        No hay condominios registrados para asignar / Crear
                    </DropdownLink>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
