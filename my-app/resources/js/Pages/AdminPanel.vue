<script setup>
import { ref, watch, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import UsersTable from '@/Components/UsersTable.vue'
import ReportsTable from '@/Components/ReportsTable.vue'
import ServicesTable from '@/Components/ServicesTable.vue'
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
  { key: 'services', label: 'Services' },
]

// Use fallback defaults to avoid null rendering
const usersData = computed(() => page.props.users ?? { data: [] })
const reportsData = computed(() => page.props.reports ?? { data: [] })
const servicesData = computed(() => page.props.services ?? { data: [] })
const filtersData = computed(() => page.props.filters ?? {})

// Loading flag for transitions
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
    <!-- Background with gradient blobs -->
    <div class="min-h-screen relative overflow-hidden bg-[#FFF8F0] font-heading">
      
      <!-- Animated gradient blobs for glassmorphic effect -->
      <div class="absolute top-0 -left-20 w-96 h-96 bg-gradient-to-br from-[#e4299c]/30 to-[#ff6b9d]/20 rounded-full blur-3xl"></div>
      <div class="absolute top-40 right-0 w-[32rem] h-[32rem] bg-gradient-to-br from-[#febd59]/40 to-[#ffbc59]/30 rounded-full blur-3xl"></div>
      <div class="absolute bottom-20 left-1/3 w-80 h-80 bg-gradient-to-br from-[#e8662c]/20 to-[#ff8c42]/20 rounded-full blur-3xl"></div>
      
      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 lg:py-10 space-y-6 sm:space-y-8">
        
        <!-- Header -->
<div class="text-center sm:text-left">
  <h1 class="text-3xl sm:text-4xl lg:text-5xl font-display font-bold text-[#e8662c] mb-2 uppercase tracking-tight">
    Admin Dashboard
  </h1>
  <p class="text-sm sm:text-base text-[#6b5b73] font-body">
    Manage users, reports, and services
  </p>
</div>



        <!-- Tabs with glassmorphism -->
        <div class="backdrop-blur-lg bg-white/40 rounded-2xl shadow-xl border border-white/60 p-2 sm:p-3">
          <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
            <button
              v-for="tab in tabs"
              :key="tab.key"
              @click="currentTab = tab.key"
              :class="[
                'px-4 sm:px-6 py-3 rounded-xl font-semibold text-sm sm:text-base transition-all duration-200',
                currentTab === tab.key
                  ? 'bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white shadow-lg'
                  : 'text-[#6b5b73] hover:bg-white/60'
              ]"
            >
              {{ tab.label }}
            </button>
          </div>
        </div>

        <!-- Loader -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-12 sm:py-20">
          <div class="w-16 h-16 border-4 border-[#e4299c]/30 border-t-[#e4299c] rounded-full animate-spin"></div>
          <p class="mt-4 text-[#6b5b73] font-body">Loading data...</p>
        </div>

        <!-- Content Area with glass effect -->
        <!-- Content Area - Responsive with glassmorphic background -->
<!-- Content Area - FULLY TRANSPARENT container, glass effect on tables -->
<div 
  v-if="!loading"
  class="overflow-x-auto transition-all duration-300"
>
  <UsersTable
    v-if="currentTab === 'users' && usersData"
    :auth="props.auth"
    :users="usersData"
    :filters="filtersData"
  />

  <ReportsTable
    v-if="currentTab === 'reports' && reportsData"
    :auth="props.auth"
    :reports="reportsData"
    :filters="filtersData"
  />

  <ServicesTable
    v-if="currentTab === 'services' && servicesData"
    :auth="props.auth"
    :services="servicesData"
    :filters="filtersData"
  />
</div>



      </div>
    </div>
  </MainLayout>
</template>

<style scoped>
/* Glassmorphism effects */
.backdrop-blur-md {
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}

.backdrop-blur-lg {
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
}
</style>
