<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import UsersTable from '@/Components/UsersTable.vue'
import ReportsTable from '@/Components/ReportsTable.vue'
import MainLayout from '@/Layouts/MainLayout.vue'

const props = defineProps({
  auth: Object,
  users: Object,    // initially empty or first load
  reports: Object,  // initially empty
  filters: Object,
})

// Tabs: "users" or "reports"
const currentTab = ref('users')
const tabs = [
  { key: 'users', label: 'Users' },
  { key: 'reports', label: 'Reports' },
]

// State to store fetched data
const usersData = ref(props.users)
const reportsData = ref(props.reports)
const filtersData = ref(props.filters || {})

// Watch tab changes and fetch data dynamically
watch(currentTab, (tab) => {
  if (tab === 'users') {
    router.get(route('admin-panel.dashboard'), {}, {
      preserveState: true,
      onSuccess: page => { usersData.value = page.props.users }
    })
  } else if (tab === 'reports') {
    router.get(route('admin-panel.reports.index'), {}, {
      preserveState: true,
      onSuccess: page => { reportsData.value = page.props.reports }
    })
  }
})
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
        :users="usersData"
        :filters="filtersData"
      />
      <ReportsTable
        v-if="currentTab === 'reports'"
        :auth="auth"
        :reports="reportsData"
        :filters="filtersData"
      />
    </div>
  </MainLayout>
</template>
