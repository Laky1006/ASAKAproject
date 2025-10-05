<template>
  <Head title="Profile" />

  <MainLayout :user="auth.user">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Profile</h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Two-column layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- LEFT: Profile info form -->
          <div class="bg-white p-4 sm:p-8 shadow sm:rounded-lg">
            <!-- remove max-w-xl so it can breathe -->
            <UpdateProfileInformationForm
              :must-verify-email="mustVerifyEmail"
              :status="status"
            />
          </div>

          <!-- RIGHT: two stacked cards -->
          <div class="space-y-6">
            <div class="bg-white p-4 sm:p-8 shadow sm:rounded-lg">
              <UpdatePasswordForm />
            </div>

            <div class="bg-white p-4 sm:p-8 shadow sm:rounded-lg">
              <DeleteUserForm />
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import { Head } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import DeleteUserForm from './Partials/DeleteUserForm.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'

export default {
  props: {
    mustVerifyEmail: Boolean,
    status: String,
    auth: Object,
  },
  components: {
    Head,
    MainLayout,
    DeleteUserForm,
    UpdatePasswordForm,
    UpdateProfileInformationForm,
  },
}
</script>
