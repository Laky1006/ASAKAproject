<script setup>
import InputError from '@/Components/basics/InputError.vue';
import InputLabel from '@/Components/basics/InputLabel.vue';
import PrimaryButton from '@/Components/basics/PrimaryButton.vue';
import TextInput from '@/Components/basics/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    _method: 'PATCH',
    name: user.name,
    email: user.email,
    username: user.username,
    profile_photo: null,
    grade: user.reguser?.grade || '',
    location: user.provider?.location || '',
    bio: user.provider?.bio || '',
});

const fileSelected = ref(false);
const selectedFileName = ref('');

function handlePhotoChange(event) {
    form.profile_photo = event.target.files[0];
    
    if (event.target.files[0]) {
        fileSelected.value = true;
        selectedFileName.value = event.target.files[0].name;
        
        setTimeout(() => {
            fileSelected.value = false;
        }, 3000);
    }
}


function submitForm() {
    form.post(route('profile.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset('profile_photo');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        },
    });
}
</script>

<template>
    <section class="backdrop-blur-xl bg-white/50 rounded-3xl shadow-2xl border border-white/70 p-8 sm:p-10">
        <header class="mb-8">
            <h2 class="text-3xl font-bold text-[#2D1810] font-display">
                Profile Information
            </h2>
            <p class="mt-2 text-[#6b5b73] font-body">
                Update your account's profile information and email address.
            </p>
        </header>

        <form @submit.prevent="submitForm" class="space-y-6">
            
            <!-- Profile Photo -->
            <div>
                <InputLabel for="profile_photo" value="Profile Photo" class="text-[#2D1810] font-heading font-semibold mb-3" />
                
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <img
                            :src="user.profile_photo ? `/storage/${user.profile_photo}` : '/images/default-profile.jpg'"
                            alt="Profile Photo"
                            class="w-24 h-24 rounded-full object-cover border-4 border-[#e4299c] shadow-lg"
                        />
                        <div class="absolute bottom-0 right-0 w-8 h-8 bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="flex-1">
                        <label for="profile_photo" class="cursor-pointer inline-block px-6 py-3 rounded-xl bg-white/60 border border-white/80 text-[#6b5b73] hover:bg-white/80 transition-all font-semibold font-heading shadow-md hover:shadow-lg">
                            Choose Photo
                        </label>
                        <input
                            id="profile_photo"
                            type="file"
                            class="hidden"
                            accept="image/*"
                            @change="handlePhotoChange"
                        />
                        <p class="mt-2 text-xs text-[#6b5b73] font-body">JPG, PNG or GIF (Max 2MB)</p>
                    </div>
                </div>

                <InputError :message="form.errors.profile_photo" class="mt-2" />
            </div>

            <!-- File Selection Confirmation Message -->
            <Transition
                enter-active-class="transition ease-in-out duration-300"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in-out duration-300"
                leave-to-class="opacity-0 translate-y-2"
            >
                <div
                    v-if="fileSelected"
                    class="backdrop-blur-sm bg-green-100/80 border border-green-200 rounded-xl p-4 mb-4"
                >
                    <p class="text-sm text-green-800 font-body flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        File "{{ selectedFileName }}" selected successfully! Ready to upload.
                    </p>
                </div>
            </Transition>

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

            <!-- Email Verification Notice -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="backdrop-blur-sm bg-yellow-100/80 border border-yellow-200 rounded-xl p-4">
                <p class="text-sm text-yellow-800 font-body">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="text-[#e4299c] hover:text-[#ff6b9d] font-semibold underline transition-colors"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-700 font-body"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <!-- Provider-specific fields -->
            <div v-if="user.role === 'provider'" class="space-y-6 pt-6 border-t border-white/40">
                <h3 class="text-xl font-bold text-[#2D1810] font-display">Provider Information</h3>
                
                <div>
                    <InputLabel for="location" value="Business Location" class="text-[#2D1810] font-heading font-semibold" />
                    <TextInput
                        id="location"
                        type="text"
                        v-model="form.location"
                        class="mt-2 block w-full rounded-xl backdrop-blur-sm bg-white/60 border border-white/80 px-4 py-3 text-[#2D1810] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] focus:bg-white/80 transition-all font-body"
                        placeholder="e.g., New York, NY"
                    />
                    <InputError class="mt-2" :message="form.errors.location" />
                </div>

                <div>
                    <InputLabel for="bio" value="Bio" class="text-[#2D1810] font-heading font-semibold" />
                    <textarea
                        id="bio"
                        v-model="form.bio"
                        class="mt-2 block w-full rounded-xl backdrop-blur-sm bg-white/60 border border-white/80 px-4 py-3 text-[#2D1810] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] focus:bg-white/80 transition-all font-body resize-none"
                        rows="4"
                        placeholder="Tell clients about your experience and specialties..."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.bio" />
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center gap-4 pt-4">
                <PrimaryButton 
                    :disabled="form.processing"
                    class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white font-bold text-base shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200 font-heading disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>Save Changes</span>
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
                        Saved successfully!
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
