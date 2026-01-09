<script setup>
import { ref, watch, defineProps } from "vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

// Componentes base
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SearchableSelect from "@/Components/SearchableSelect.vue";

// Íconos de Heroicons (24/solid)
import {
    PencilSquareIcon,
    ArrowLeftIcon,
    BuildingOfficeIcon
} from "@heroicons/vue/24/solid";

const props = defineProps({
    edificio: {
        type: Object,
        required: true,
    },
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
    nombre: props.edificio.nombre || "",
    id_condominio: props.edificio.id_condominio || (auth.user && auth.user.id_condominio ? auth.user.id_condominio : ""),
    // Pre-cargar según lo guardado:
    // 0 = aplicar prorrateo del condominio, 1 = prorrateo individual
    aplica_prorrateo: props.edificio.aplica_prorrateo ?? 0,
    tipo_prorrateo_id: props.edificio.tipo_prorrateo_id || ""
});

const selectedCondominio = ref(null);

if (auth.user && auth.user.id_condominio) {
    selectedCondominio.value = props.condominios.find(
        (c) => c.id === auth.user.id_condominio
    );
} else if (props.condominios.length > 0 && !form.id_condominio) {
    form.id_condominio = props.condominios[0].id;
    selectedCondominio.value = props.condominios[0];
}

watch(selectedCondominio, (nuevoValor) => {
    form.id_condominio = nuevoValor ? nuevoValor.id : "";
});

function updateEdificio() {
    form.put(route("edificios.update", props.edificio.id_edificio), {
        onSuccess: () => {
            // Lógica adicional si se requiere
        },
    });
}
</script>

<template>

    <Head title="Editar Edificio" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between bg-green-600 p-4 rounded-md">
                <h3 class="text-xl font-semibold text-white flex items-center space-x-2">
                    <BuildingOfficeIcon class="h-6 w-6" />
                    <span>Estas en: Edificios / Editar</span>
                </h3>
                <Link :href="route('edificios.index')"
                    class="inline-flex items-center text-white hover:underline font-semibold">
                <ArrowLeftIcon class="h-5 w-5 mr-1" />
                Volver
                </Link>
            </div>
        </template>

        <div class="py-12 flex justify-center">
            <div class="w-full max-w-4xl bg-white shadow-lg rounded-xl p-8 border border-gray-200">
                <h3 class="text-2xl font-bold text-gray-700 mb-6 text-center">
                    Editar Edificio / Lote
                </h3>

                <form @submit.prevent="updateEdificio" class="space-y-6">
                    <div>
                        <InputLabel for="nombre" value="Nombre del Edificio / Lote" />
                        <TextInput id="nombre" type="text" v-model="form.nombre" required class="w-full" />
                        <InputError :message="form.errors.nombre" />
                    </div>

                    <div v-if="!auth.user?.id_condominio" class="mb-4">
                        <SearchableSelect id="id_condominio" label="Selecciona un Condominio" :opciones="condominios"
                            v-model="selectedCondominio" :errorMessage="form.errors.id_condominio" />
                    </div>
                    <div v-else class="mb-4">
                        <InputLabel value="Condominio asignado" />
                        <div class="border border-gray-300 rounded-md p-2">
                            {{ selectedCondominio ? selectedCondominio.nombre : 'N/A' }}
                        </div>
                        <input type="hidden" v-model="form.id_condominio" />
                    </div>

                    <!-- Mostrar prorrateo del condominio asignado -->
                    <div class="mb-4" v-if="selectedCondominio">
                        <p class="text-sm text-gray-700">
                            Prorrateo de Condominio:
                            <span class="font-semibold">
                                {{ prorrateoCondominio[selectedCondominio.id]?.descripcion || "Sin prorrateo" }}
                            </span>
                        </p>
                    </div>

                    <!-- Radio buttons para seleccionar la respuesta -->
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

                    <!-- Si se selecciona "No" (prorrateo individual), mostrar el selector -->
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
                        <PrimaryButton>
                            <div class="flex items-center space-x-1">
                                <PencilSquareIcon class="h-5 w-5" />
                                <span>Actualizar</span>
                            </div>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
