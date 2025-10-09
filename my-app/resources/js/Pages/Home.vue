<script setup>
import SearchSortFilter from '@/Components/SearchSortFilter.vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  services: Array,
  auth: Object,
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
</script>

<template>
  <MainLayout :user="auth.user">
    <section class="py-10 px-6 max-w-7xl mx-auto">
      <h2 class="text-3xl font-bold mb-6">Available Services</h2>

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
                    'text-yellow-500': service.rating >= n,
                    'text-gray-300': service.rating < n
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
    </section>
  </MainLayout>
</template>
