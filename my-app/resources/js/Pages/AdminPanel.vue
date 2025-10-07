<script setup>
import { ref, watch, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import UsersTable from '@/Components/UsersTable.vue'
import ReportsTable from '@/Components/ReportsTable.vue'
import MainLayout from '@/Layouts/MainLayout.vue'

const props = defineProps({
  auth: Object,
  tab: String,
})

const page = usePage()

// Tabs
const currentTab = ref(props.tab || 'users')
const tabs = [
  { key: 'users', label: 'Users' },
  { key: 'reports', label: 'Reports' },
]

// ✅ use fallback defaults to avoid `null` rendering
const usersData = computed(() => page.props.users ?? { data: [] })
const reportsData = computed(() => page.props.reports ?? { data: [] })
const filtersData = computed(() => page.props.filters ?? {})

// ✅ add a loading flag for transitions
const loading = ref(false)

watch(currentTab, (tab) => {
  loading.value = true
  router.get(route('admin-panel.dashboard'), { tab }, {
    preserveScroll: true,
    preserveState: false,
    replace: true,
    onFinish: () => (loading.value = false),
  })
})
</script>


<template>
  <MainLayout :user="props.auth?.user">
    <div class="max-w-7xl mx-auto p-6 space-y-6">
      <div>
        <h1 class="text-3xl font-bold">Admin Dashboard</h1>
        <p class="text-gray-600">Manage users and reports.</p>
      </div>

      <!-- Tabs -->
      <div class="flex gap-4 border-b border-gray-200 mb-4">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="currentTab = tab.key"
          :class="[ 'px-4 py-2',
            currentTab === tab.key
              ? 'border-b-2 border-blue-600 font-semibold'
              : 'text-gray-600 hover:text-black' ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Loader -->
      <div v-if="loading" class="text-gray-500">Loading...</div>

      <!-- Users -->
      <UsersTable
        v-if="currentTab === 'users' && usersData && !loading"
        :auth="props.auth"
        :users="usersData"
        :filters="filtersData"
      />

      <!-- Reports -->
      <ReportsTable
        v-if="currentTab === 'reports' && reportsData && !loading"
        :auth="props.auth"
        :reports="reportsData"
        :filters="filtersData"
      />
    </div>
  </MainLayout>
</template>
