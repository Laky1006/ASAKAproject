<script setup>
import InputError from '@/Components/basics/InputError.vue';
import InputLabel from '@/Components/basics/InputLabel.vue';
import PrimaryButton from '@/Components/basics/PrimaryButton.vue';
import TextInput from '@/Components/basics/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="backdrop-blur-xl bg-white/50 rounded-3xl shadow-2xl border border-white/70 p-8 sm:p-10">
        <header class="mb-8">
            <h2 class="text-3xl font-bold text-[#2D1810] font-display">
                Update Password
            </h2>
            <p class="mt-2 text-[#6b5b73] font-body">
                Ensure your account is using a long, random password to stay secure.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="space-y-6">
            
            <!-- Current Password -->
            <div>
                <InputLabel for="current_password" value="Current Password" class="text-[#2D1810] font-heading font-semibold" />
                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-2 block w-full rounded-xl backdrop-blur-sm bg-white/60 border border-white/80 px-4 py-3 text-[#2D1810] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] focus:bg-white/80 transition-all font-body"
                    autocomplete="current-password"
                />
                <InputError
                    :message="form.errors.current_password"
                    class="mt-2"
                />
            </div>

            <!-- New Password -->
            <div>
                <InputLabel for="password" value="New Password" class="text-[#2D1810] font-heading font-semibold" />
                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-2 block w-full rounded-xl backdrop-blur-sm bg-white/60 border border-white/80 px-4 py-3 text-[#2D1810] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] focus:bg-white/80 transition-all font-body"
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirm New Password"
                    class="text-[#2D1810] font-heading font-semibold"
                />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-2 block w-full rounded-xl backdrop-blur-sm bg-white/60 border border-white/80 px-4 py-3 text-[#2D1810] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] focus:bg-white/80 transition-all font-body"
                    autocomplete="new-password"
                />
                <InputError
                    :message="form.errors.password_confirmation"
                    class="mt-2"
                />
            </div>

            <!-- Submit Button -->
            <div class="flex items-center gap-4 pt-4">
                <PrimaryButton 
                    :disabled="form.processing"
                    class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white font-bold text-base shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200 font-heading disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="form.processing">Updating...</span>
                    <span v-else>Update Password</span>
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-x-2"
                    enter-to-class="opacity-100 translate-x-0"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0 translate-x-2"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-green-600 font-semibold font-body flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Password updated!
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
