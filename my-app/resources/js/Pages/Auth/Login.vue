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
    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="login" value="Email or Username" />

        <TextInput
          id="login"
          type="text"                     
          class="mt-1 block w-full"
          v-model="form.login"            
          required
          autofocus
          autocomplete="username"
        />

        <InputError class="mt-2" :message="form.errors.login" />
      </div>

      <div class="mt-4">
        <InputLabel for="password" value="Password" />
        <div class="relative">
            <TextInput ss
                id="password"
                :type="showPassword ? 'text' : 'password'"
                class="mt-1 block w-full pr-10"    
                v-model="form.password"
                required
                autocomplete="current-password"
            />

            
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

      <div class="mt-4 block">
        <label class="flex items-center">
          <Checkbox name="remember" v-model:checked="form.remember" />
          <span class="ms-2 text-sm text-gray-600">Remember me</span>
        </label>
      </div>

      <div class="mt-4 flex items-center justify-end">
        <Link
          v-if="canResetPassword"
          :href="route('password.request')"
          class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
          Forgot your password?
        </Link>

        <PrimaryButton
          class="ms-4"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Log in
        </PrimaryButton>
      </div>
    </form>

    <div class="mt-4 text-center">
      <p class="text-sm">
        Don't have an account?
        <a href="/register" class="text-blue-600 hover:underline font-medium">
          Register here
        </a>
      </p>
    </div>
  </GuestLayout>
</template>
