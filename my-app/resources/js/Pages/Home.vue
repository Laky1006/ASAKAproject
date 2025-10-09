<script setup>
import SearchSortFilter from '@/Components/SearchSortFilter.vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  services: Array,
  auth: Object,
  providers: { type: Array, default: () => [] }, // 👇 added
  initialTab: { type: String, default: 'services' }, // 👈 NEW
})

const searchQuery = ref('')
const sortOption = ref('az')
const selectedLabels = ref([])

const availableLabels = [
  'Nails', 'Hair', 'Skincare', 'Lashes', 'Body', 'Makeup', 'Wellness',
]

const sortOptions = {
  az: 'A-Z',
  za: 'Z-A',
  ratingHigh: 'Highest Rating',
  ratingLow: 'Lowest Rating',
}

const filteredServices = computed(() => {
  return props.services
    .filter(s =>
      (s.title ?? '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (s.provider?.user?.name ?? '').toLowerCase().includes(searchQuery.value.toLowerCase())
    )
    .filter(s =>
      selectedLabels.value.length === 0 ||
      selectedLabels.value.every(lbl => (s.labels || []).includes(lbl))
    )
    .sort((a, b) => {
      if (sortOption.value === 'az') return a.title.localeCompare(b.title)
      if (sortOption.value === 'za') return b.title.localeCompare(a.title)
      if (sortOption.value === 'ratingHigh') return (b.rating || 0) - (a.rating || 0)
      if (sortOption.value === 'ratingLow') return (a.rating || 0) - (b.rating || 0)
      return 0
    })
})

// 👇 added
const activeTab = ref(props.initialTab) // 👈 was 'services'
const providerQuery = ref('')
const filteredProviders = computed(() => {
  return (props.providers || []).filter(p => {
    const name = (p.user?.name || '').toLowerCase()
    const loc = (p.location || '').toLowerCase()
    const q = providerQuery.value.toLowerCase()
    return name.includes(q) || loc.includes(q)
  })
})
</script>

<template>
  <MainLayout :user="auth.user">
    <section class="py-10 px-6 max-w-7xl mx-auto">
    <div v-if="activeTab==='services'"></div>

      <!-- 👇 added -->
      <div class="flex gap-2 mb-6">
        <button
          class="px-4 py-2 rounded-lg"
          :class="activeTab==='services' ? 'bg-gray-900 text-white' : 'bg-gray-100'"
          @click="activeTab='services'">Services</button>
        <button
          class="px-4 py-2 rounded-lg"
          :class="activeTab==='providers' ? 'bg-gray-900 text-white' : 'bg-gray-100'"
          @click="activeTab='providers'">Providers</button>
      </div>

      <!-- PROVIDERS TAB -->
<div v-if="activeTab==='providers'">
  <!-- search bar -->
  <div class="mb-6">
    <input
      v-model="providerQuery"
      type="text"
      placeholder="Search providers by name or location..."
      class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
    />
  </div>

  <!-- provider cards grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    <Link
      v-for="prov in filteredProviders"
      :key="prov.id"
      :href="route('providers.show', prov.id)"
      class="block group rounded-2xl bg-white shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
    >
      <div class="p-5 flex flex-col items-start">
        <!-- optional avatar -->
        <div
          class="w-14 h-14 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-semibold text-lg mb-3"
        >
          {{ prov.user?.name?.charAt(0).toUpperCase() }}
        </div>

        <!-- name -->
        <h3
          class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors"
        >
          {{ prov.user?.name ?? 'Provider' }}
        </h3>

        <!-- location -->
        <p class="text-sm text-gray-600 mt-1 flex items-center gap-1">
          📍 {{ prov.location ?? 'No location provided' }}
        </p>

        <!-- bio -->
        <p
          v-if="prov.bio"
          class="text-sm text-gray-700 mt-2 line-clamp-2"
        >
          {{ prov.bio }}
        </p>

        <!-- stats -->
        <div
          class="mt-4 flex items-center justify-between w-full text-sm text-gray-500 border-t pt-3"
        >
          <span>🛠️ {{ (prov.services ?? []).length }} services</span>
          <span class="text-indigo-600 font-medium group-hover:underline">
            View →
          </span>
        </div>
      </div>
    </Link>
  </div>
</div>


      <!-- your existing service list remains untouched -->
      <!-- SERVICES TAB -->
<div v-if="activeTab==='services'">
  <SearchSortFilter
    v-model:search="searchQuery"
    v-model:sort="sortOption"
    v-model:selectedFilters="selectedLabels"
    :sort-options="sortOptions"
    :filters="availableLabels"
    search-placeholder="Search services..."
    :show-selected-chips="true"
  />

  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
    <Link
      v-for="service in filteredServices"
      :key="service.id"
      :href="route('services.show', service.id)"
      class="block overflow-hidden rounded-lg shadow transition duration-300 transform hover:scale-[1.02] hover:shadow-lg cursor-pointer bg-white"
    >
      <img
        :src="service.banner ? `/storage/${service.banner}` : '/images/default-banner.jpg'"
        alt="Service Banner"
        class="w-full h-40 object-cover"
      />
      <div class="p-4">
        <h3 class="text-xl font-semibold mb-2">{{ service.title }}</h3>
        <p class="text-sm text-gray-600 mb-1">
          Provider: {{ service.provider?.user?.name || 'Unknown' }}
        </p>

        <div class="flex items-center text-sm">
          <div class="flex">
            <span
              v-for="n in 5"
              :key="n"
              class="text-sm"
              :class="{
                'text-yellow-500': (service.rating || 0) >= n,
                'text-gray-300': (service.rating || 0) < n
              }"
            >★</span>
          </div>
          <span class="ml-2 text-gray-600">
            {{ service.rating ? `${service.rating}/5` : 'Not rated' }}
          </span>
        </div>

        <p class="text-sm text-gray-700 mt-2">
          Price: € {{ service.price ?? 'Free' }}
        </p>

        <div v-if="service.labels?.length" class="flex flex-wrap gap-2 mt-2">
          <span
            v-for="(label, i) in service.labels"
            :key="i"
            class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs transition-opacity duration-300 hover:opacity-80"
          >
            {{ label }}
          </span>
        </div>
      </div>
    </Link>
  </div>
</div>
    </section>
  </MainLayout>
</template>
