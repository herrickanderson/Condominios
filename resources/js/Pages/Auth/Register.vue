<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    apellidos: '',
    email: '',
    telefono: '',
    rut: '',
    password: '',
    password_confirmation: '',
    estado: 'A', // ✅ Por defecto "Activo (A)"
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>


<template>
    <GuestLayout>

        <Head title="Register" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="apellidos" value="Apellidos" />
                <TextInput id="apellidos" type="text" class="mt-1 block w-full" v-model="form.apellidos" required autocomplete="family-name" />
                <InputError class="mt-2" :message="form.errors.apellidos" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="telefono" value="Teléfono" />
                <TextInput id="telefono" type="text" class="mt-1 block w-full" v-model="form.telefono" autocomplete="tel" />
                <InputError class="mt-2" :message="form.errors.telefono" />
            </div>

            <div class="mt-4">
                <InputLabel for="rut" value="RUT" />
                <TextInput id="rut" type="text" class="mt-1 block w-full" v-model="form.rut" autocomplete="off" />
                <InputError class="mt-2" :message="form.errors.rut" />
            </div>

            <div class="mt-4">
                <InputLabel for="estado" value="Estado" />
                <select id="estado" v-model="form.estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="A">Activo</option>
                    <option value="I">Inactivo</option>
                </select>
                <InputError class="mt-2" :message="form.errors.estado" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Already registered?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

