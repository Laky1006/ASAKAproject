<script setup>
import { ref } from 'vue'
import UsersTable from '@/Components/UsersTable.vue'
import ReportsTable from '@/Components/ReportsTable.vue'
import MainLayout from '@/Layouts/MainLayout.vue'

const props = defineProps({
  auth: Object,
  users: Object,
  reports: Object,
  filters: Object,
})

// Tabs: "users" or "reports"
const currentTab = ref('users')
const tabs = [
  { key: 'users', label: 'Users' },
  { key: 'reports', label: 'Reports' },
]
</script>

<template>
  <MainLayout :user="auth?.user">
    <div class="max-w-7xl mx-auto p-6 space-y-6">
      <div>
        <h1 class="text-3xl font-bold">Admin Dashboard</h1>
        <p class="text-gray-600">Manage users and reports on the site.</p>
      </div>

      <!-- Tabs -->
      <div class="flex gap-4 border-b border-gray-200 mb-4">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="currentTab = tab.key"
          :class="currentTab === tab.key ? 'border-b-2 border-blue-600 font-semibold' : 'text-gray-600'"
          class="px-4 py-2"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Tables -->
      <UsersTable
        v-if="currentTab === 'users'"
        :auth="auth"
        :users="users"
        :filters="filters"
      />
      <ReportsTable
        v-if="currentTab === 'reports'"
        :auth="auth"
        :reports="reports"
        :filters="filters"
      />
    </div>
  </MainLayout>
</template>
