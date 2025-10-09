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

        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-[#2D1810] font-display">Create Account</h2>
            <p class="mt-2 text-[#6b5b73] font-body text-sm">Join BeauHive today!</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">

            <!-- Role Selection -->
            <div>
                <InputLabel for="role" value="Register as" class="text-[#2D1810] font-heading font-semibold mb-3" />

                <div class="flex items-center gap-6">
                    <label class="flex items-center cursor-pointer group">
                        <input
                            type="radio"
                            name="role"
                            value="reguser"
                            v-model="form.role"
                            class="w-4 h-4 text-[#e4299c] border-gray-300 focus:ring-[#e4299c]"
                        />
                        <span class="ml-2 text-sm text-[#6b5b73] group-hover:text-[#e4299c] transition-colors font-body">Client</span>
                    </label>

                    <label class="flex items-center cursor-pointer group">
                        <input
                            type="radio"
                            name="role"
                            value="provider"
                            v-model="form.role"
                            class="w-4 h-4 text-[#e4299c] border-gray-300 focus:ring-[#e4299c]"
                        />
                        <span class="ml-2 text-sm text-[#6b5b73] group-hover:text-[#e4299c] transition-colors font-body">Provider</span>
                    </label>
                </div>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <!-- Name -->
            <div>
                <InputLabel for="name" value="Full Name" class="text-[#2D1810] font-heading font-semibold" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-2 block w-full rounded-xl backdrop-blur-sm bg-white/60 border border-white/80 px-4 py-3 text-[#2D1810] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] focus:bg-white/80 transition-all font-body"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Username -->
            <div>
                <InputLabel for="username" value="Username" class="text-[#2D1810] font-heading font-semibold" />
                <TextInput
                    id="username"
                    type="text"
                    class="mt-2 block w-full rounded-xl backdrop-blur-sm bg-white/60 border border-white/80 px-4 py-3 text-[#2D1810] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] focus:bg-white/80 transition-all font-body"
                    v-model="form.username"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.username" />
            </div>

            <!-- Email -->
            <div>
                <InputLabel for="email" value="Email Address" class="text-[#2D1810] font-heading font-semibold" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full rounded-xl backdrop-blur-sm bg-white/60 border border-white/80 px-4 py-3 text-[#2D1810] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] focus:bg-white/80 transition-all font-body"
                    v-model="form.email"
                    required
                    autocomplete="email"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div>
                <InputLabel for="password" value="Password" class="text-[#2D1810] font-heading font-semibold" />
                <div class="relative mt-2">
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full rounded-xl backdrop-blur-sm bg-white/60 border border-white/80 px-4 py-3 pr-12 text-[#2D1810] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] focus:bg-white/80 transition-all font-body"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                    />
                    <button
                        type="button"
                        @click="togglePassword"
                        class="absolute inset-y-0 right-0 px-4 flex items-center focus:outline-none group"
                    >
                        <!-- Eye Icon (Visible) -->
                        <svg v-if="!showPassword" class="w-5 h-5 text-[#6b5b73] group-hover:text-[#e4299c] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <!-- Eye Slash Icon (Hidden) -->
                        <svg v-else class="w-5 h-5 text-[#6b5b73] group-hover:text-[#e4299c] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    </button>
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Confirm Password -->
            <div>
                <InputLabel for="password_confirmation" value="Confirm Password" class="text-[#2D1810] font-heading font-semibold" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-2 block w-full rounded-xl backdrop-blur-sm bg-white/60 border border-white/80 px-4 py-3 text-[#2D1810] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] focus:bg-white/80 transition-all font-body"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <!-- Submit Section -->
            <div class="flex flex-col gap-4">
                <PrimaryButton
                    class="w-full justify-center py-3 px-6 rounded-xl bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white font-bold text-base shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all duration-200 font-heading"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Creating account...</span>
                    <span v-else>Register</span>
                </PrimaryButton>

                <div class="text-center">
                    <Link
                        :href="route('login')"
                        class="text-sm text-[#e4299c] hover:text-[#ff6b9d] font-semibold transition-colors font-body"
                    >
                        Already have an account? Log in
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
