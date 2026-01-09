<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <div class="flex items-center justify-center min-h-screen bg-gray-50">
        <!-- Contenedor principal del formulario -->
        <div class="w-full max-w-md p-8 space-y-6 bg-white border border-gray-200 rounded-lg shadow">
            <!-- Logo y título -->
            <div class="flex justify-center mb-4">
                <ApplicationLogo class="h-20 w-20 fill-current text-green-600" />
            </div>
            <h2 class="text-2xl font-bold text-center text-gray-700">
                Bienvenido
            </h2>

            <!-- Mensaje de estado (ej. 'Tu sesión ha expirado', etc.) -->
            <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
                {{ status }}
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Campo de Email -->
                <div>
                    <InputLabel for="email" value="Correo electrónico" class="block mb-1 text-gray-700" />
                    <TextInput id="email" type="email"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        v-model="form.email" required autocomplete="username" />
                    <InputError class="mt-1 text-red-500" :message="form.errors.email" />
                </div>

                <!-- Campo de Password -->
                <div>
                    <InputLabel for="password" value="Contraseña" class="block mb-1 text-gray-700" />
                    <TextInput id="password" type="password"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        v-model="form.password" required autocomplete="current-password" />
                    <InputError class="mt-1 text-red-500" :message="form.errors.password" />
                </div>

                <!-- Remember me y Forgot password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember"
                            class="text-green-600 focus:ring-green-500" />
                        <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                    </label>

                    <Link v-if="canResetPassword" :href="route('password.request')"
                        class="text-sm text-green-600 hover:text-green-700">
                    ¿Olvidaste tu contraseña?
                    </Link>
                </div>

                <!-- Botón de Login -->
                <div>
                    <PrimaryButton
                        class="w-full flex justify-center bg-green-600 text-white hover:bg-green-700 focus:bg-green-700 focus:ring-green-500"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Iniciar sesión
                    </PrimaryButton>
                </div>
                <div>
                    <!-- <Link :href="route('register')"
                        class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                    Register
                    </Link>-->
                </div>
            </form>
        </div>
    </div>
</template>
