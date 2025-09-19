<script setup>
import { router } from '@inertiajs/vue3'

const goUsers = () => router.get('/admin/users')       // add the route soon
const goServices = () => router.get('/admin/services') // add the route soon
</script>

<template>
  <div class="max-w-6xl mx-auto p-6 space-y-8">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow p-6">
      <h1 class="text-3xl font-bold">Admin Panel — Help</h1>
      <p class="mt-2 text-gray-600">
        Welcome, Admin! This page gives you quick access to core tools and explains what you can do here.
      </p>

      <!-- Quick actions -->
      <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-xl border bg-white p-5 shadow-sm">
          <h2 class="text-xl font-semibold">All Users</h2>
          <p class="mt-1 text-gray-600">
            View every registered user (regusers & providers). Useful for audits and support.
          </p>
          <button
            @click="goUsers"
            class="mt-4 inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 active:scale-[.98]"
          >
            Open Users
          </button>
        </div>

        <div class="rounded-xl border bg-white p-5 shadow-sm">
          <h2 class="text-xl font-semibold">Provider Services</h2>
          <p class="mt-1 text-gray-600">
            See all services created by providers to review activity and quality.
          </p>
          <button
            @click="goServices"
            class="mt-4 inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 active:scale-[.98]"
          >
            Open Services
          </button>
        </div>
      </div>
    </div>

    <!-- What you can do -->
    <div class="bg-white rounded-2xl shadow p-6">
      <h2 class="text-2xl font-bold">What can I do here?</h2>
      <ul class="mt-3 list-disc list-inside text-gray-700 space-y-1">
        <li>Browse all users and their roles (reguser, provider, admin).</li>
        <li>Browse all provider services and check who owns what.</li>
        <li>Prepare for moderation tasks (e.g., removals, warnings) as we add features.</li>
      </ul>
    </div>

    <!-- Access rules -->
    <div class="bg-white rounded-2xl shadow p-6">
      <h2 class="text-2xl font-bold">Access & Roles</h2>
      <p class="mt-2 text-gray-700">
        Only users with the <span class="font-semibold">admin</span> role can open this area. Regular users and providers
        cannot access it. If you need a teammate to become admin, update the database or use your admin seeder.
      </p>
    </div>

    <!-- Safety tips -->
    <div class="bg-white rounded-2xl shadow p-6">
      <h2 class="text-2xl font-bold">Safety Tips</h2>
      <ul class="mt-3 list-disc list-inside text-gray-700 space-y-1">
        <li>Keep one admin account to avoid accidental changes.</li>
        <li>Log out on shared computers after moderation work.</li>
        <li>Before we add destructive actions, consider soft deletes & audit logs.</li>
      </ul>
    </div>

    <!-- Next steps -->
    <div class="bg-white rounded-2xl shadow p-6">
      <h2 class="text-2xl font-bold">Next Steps (when ready)</h2>
      <ol class="mt-3 list-decimal list-inside text-gray-700 space-y-1">
        <li>Add routes/controllers for <span class="font-semibold">/admin/users</span> and <span class="font-semibold">/admin/services</span>.</li>
        <li>Show tables with pagination and simple search/filter.</li>
        <li>Add safe moderation (soft delete, undo, confirmation dialogs).</li>
      </ol>
    </div>
  </div>
</template>
