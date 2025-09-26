<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/basics/InputError.vue';
import InputLabel from '@/Components/basics/InputLabel.vue';
import PrimaryButton from '@/Components/basics/PrimaryButton.vue';
import TextInput from '@/Components/basics/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
});

const showPassword = ref(false);

const togglePassword = () => {
  showPassword.value = !showPassword.value;
};

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

            <div class="mt-4">

                <!-- Lomas izvēle -->
                <InputLabel for="role" value="Register as" />

                <div class="flex items-center mt-2 space-x-6">

                    <!-- Skolēns -->
                    <label class="flex items-center">
                        <input
                            type="radio"
                            name="role"
                            value="reguser"
                            v-model="form.role"
                            class="text-indigo-600 border-gray-300 focus:ring-indigo-500"
                        />
                        <span class="ml-2 text-sm text-gray-700">Reguser</span>
                    </label>

                    <!-- Skolotājs -->
                    <label class="flex items-center">
                        <input
                            type="radio"
                            name="role"
                            value="provider"
                            v-model="form.role"
                            class="text-indigo-600 border-gray-300 focus:ring-indigo-500"
                        />
                        <span class="ml-2 text-sm text-gray-700">Provider</span>
                    </label>
                </div>
                    <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <!-- Vārds -->
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Username -->
            <div class="mt-4">
                <InputLabel for="username" value="Username" />

                <TextInput
                    id="username"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.username"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.username" />
            </div>

            <!-- Email -->
            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <div class="relative">
                    <TextInput
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="mt-1 block w-full"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                        />

                    <!-- Eye toggle button -->
                    <button
                        type="button"
                        @click="togglePassword"
                        class="absolute inset-y-0 right-0 px-3 flex items-center focus:outline-none"
                        >
                        <span
                            class="material-symbols-outlined transition-colors duration-200"
                            :class="showPassword ? 'text-gray-300' : 'text-gray-600'"
                        >
                            visibility
                        </span>
                    </button>
                </div>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Password Confirm-->
            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
