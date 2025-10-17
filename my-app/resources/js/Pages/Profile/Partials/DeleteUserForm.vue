<script setup>
import DangerButton from '@/Components/basics/DangerButton.vue';
import InputError from '@/Components/basics/InputError.vue';
import InputLabel from '@/Components/basics/InputLabel.vue';
import Modal from '@/Components/basics/Modal.vue';
import SecondaryButton from '@/Components/basics/SecondaryButton.vue';
import TextInput from '@/Components/basics/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <!-- aaa -->
    <section class="backdrop-blur-xl bg-white/50 rounded-3xl shadow-2xl border border-white/70 p-8 sm:p-10">
        <header class="mb-6">
            <h2 class="text-3xl font-bold text-red-600 font-display">
                Delete Account
            </h2>
            <p class="mt-2 text-[#6b5b73] font-body">
                Once your account is deleted, all of its resources and data will be permanently deleted. 
                Before deleting your account, please download any data or information that you wish to retain.
            </p>
        </header>

        <DangerButton 
            @click="confirmUserDeletion"
            class="px-6 py-3 rounded-xl bg-red-500 hover:bg-red-600 text-white font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200 font-heading"
        >
            Delete Account
        </DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="backdrop-blur-xl bg-white/95 rounded-3xl p-8">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-[#2D1810] font-display">
                        Are you sure?
                    </h2>
                </div>

                <p class="text-[#6b5b73] font-body mb-6">
                    Once your account is deleted, all of its resources and data will be permanently deleted. 
                    Please enter your password to confirm you would like to permanently delete your account.
                </p>

                <div class="mb-6">
                    <InputLabel
                        for="password"
                        value="Password"
                        class="text-[#2D1810] font-heading font-semibold mb-2"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="block w-full rounded-xl backdrop-blur-sm bg-white/60 border border-white/80 px-4 py-3 text-[#2D1810] focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:bg-white/80 transition-all font-body"
                        placeholder="Enter your password"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="flex gap-3 justify-end">
                    <SecondaryButton 
                        @click="closeModal"
                        class="px-6 py-3 rounded-xl backdrop-blur-sm bg-white/60 border border-white/80 text-[#6b5b73] hover:bg-white/80 font-semibold transition-all shadow-md hover:shadow-lg font-heading"
                    >
                        Cancel
                    </SecondaryButton>

                    <DangerButton
                        class="px-6 py-3 rounded-xl bg-red-500 hover:bg-red-600 text-white font-bold shadow-lg hover:shadow-xl transition-all duration-200 font-heading disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        <span v-if="form.processing">Deleting...</span>
                        <span v-else>Delete Account</span>
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
