<script setup>
import Checkbox from '@/Components/basics/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/basics/InputError.vue';
import InputLabel from '@/Components/basics/InputLabel.vue';
import PrimaryButton from '@/Components/basics/PrimaryButton.vue';
import TextInput from '@/Components/basics/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
  canResetPassword: { type: Boolean },
  status: { type: String },
});

const form = useForm({
  login: '',
  password: '',
  remember: false,
});

const showPassword = ref(false);

const togglePassword = () => {
  showPassword.value = !showPassword.value;
};

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Log in" />

    <!-- Success Message -->
    <div v-if="status" class="mb-6 p-4 rounded-xl backdrop-blur-sm bg-green-100/80 border border-green-200 text-sm font-medium text-green-700 font-body">
      {{ status }}
    </div>

    <!-- Login Form -->
    <form @submit.prevent="submit" class="space-y-6">
      
      <!-- Email/Username -->
      <div>
        <InputLabel for="login" value="Email or Username" class="text-[#2D1810] font-heading font-semibold" />
        <TextInput
          id="login"
          type="text"
          class="mt-2 block w-full rounded-xl backdrop-blur-sm bg-white/60 border border-white/80 px-4 py-3 text-[#2D1810] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] focus:bg-white/80 transition-all font-body"
          v-model="form.login"
          required
          autofocus
          autocomplete="username"
        />
        <InputError class="mt-2" :message="form.errors.login" />
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
      autocomplete="current-password"
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


      <!-- Remember Me -->
      <div class="flex items-center justify-between">
        <label class="flex items-center cursor-pointer">
          <Checkbox name="remember" v-model:checked="form.remember" class="rounded text-[#e4299c] focus:ring-[#e4299c]" />
          <span class="ms-2 text-sm text-[#6b5b73] font-body">Remember me</span>
        </label>

        <Link
          v-if="canResetPassword"
          :href="route('password.request')"
          class="text-sm text-[#e4299c] hover:text-[#ff6b9d] font-semibold transition-colors font-body"
        >
          Forgot password?
        </Link>
      </div>

      <!-- Submit Button -->
      <div>
        <PrimaryButton
          class="w-full justify-center py-3 px-6 rounded-xl bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white font-bold text-base shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all duration-200 font-heading"
          :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Logging in...</span>
          <span v-else>Log In</span>
        </PrimaryButton>
      </div>
    </form>

    <!-- Register Link -->
    <div class="mt-6 text-center">
      <p class="text-sm text-[#6b5b73] font-body">
        Don't have an account?
        <Link href="/register" class="text-[#e4299c] hover:text-[#ff6b9d] font-bold transition-colors font-heading ml-1">
          Register here
        </Link>
      </p>
    </div>
  </GuestLayout>
</template>
