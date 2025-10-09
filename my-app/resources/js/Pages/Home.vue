<script setup>
import SearchSortFilter from '@/Components/SearchSortFilter.vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  services: Array,
  auth: Object,
  providers: { type: Array, default: () => [] },
  initialTab: { type: String, default: 'services' },
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

const activeTab = ref(props.initialTab)
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
    <section class="py-10 px-6 max-w-7xl mx-auto font-heading">

      <!-- Tab Switcher - Glassmorphic -->
      <div class="flex gap-3 mb-8 backdrop-blur-md bg-white/40 rounded-2xl p-2 border border-white/60 shadow-xl">
        <button
          class="flex-1 px-6 py-3 rounded-xl font-semibold text-base transition-all duration-200"
          :class="activeTab==='services' 
            ? 'bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white shadow-lg' 
            : 'text-[#6b5b73] hover:bg-white/60'"
          @click="activeTab='services'">
          Services
        </button>
        <button
          class="flex-1 px-6 py-3 rounded-xl font-semibold text-base transition-all duration-200"
          :class="activeTab==='providers' 
            ? 'bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white shadow-lg' 
            : 'text-[#6b5b73] hover:bg-white/60'"
          @click="activeTab='providers'">
          Providers
        </button>
      </div>

      <!-- PROVIDERS TAB -->
      <div v-if="activeTab==='providers'">
        <!-- Search bar - Glassmorphic -->
        <div class="mb-8">
          <input
            v-model="providerQuery"
            type="text"
            placeholder="Search providers by name or location..."
            class="w-full rounded-2xl backdrop-blur-md bg-white/50 border border-white/60 px-6 py-4 text-[#2D1810] placeholder-[#6b5b73] focus:ring-2 focus:ring-[#e4299c] focus:bg-white/70 outline-none transition-all shadow-lg font-body"
          />
        </div>

        <!-- Provider cards grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <Link
            v-for="prov in filteredProviders"
            :key="prov.id"
            :href="route('providers.show', prov.id)"
            class="block group rounded-2xl backdrop-blur-lg bg-white/40 shadow-xl border border-white/60 overflow-hidden hover:shadow-2xl hover:scale-105 transition-all duration-300"
          >
            <div class="p-6 flex flex-col items-start">
              <!-- Avatar with gradient -->
              <div
                class="w-16 h-16 flex items-center justify-center rounded-full bg-gradient-to-br from-[#e4299c] to-[#ff6b9d] text-white font-bold text-xl mb-4 shadow-lg"
              >
                {{ prov.user?.name?.charAt(0).toUpperCase() }}
              </div>

              <!-- Name -->
              <h3
                class="text-lg font-bold text-[#2D1810] group-hover:text-[#e4299c] transition-colors mb-2"
              >
                {{ prov.user?.name ?? 'Provider' }}
              </h3>

              <!-- Location -->
              <p class="text-sm text-[#6b5b73] mb-3 flex items-center gap-1 font-body">
                📍 {{ prov.location ?? 'No location' }}
              </p>

              <!-- Bio -->
              <p
                v-if="prov.bio"
                class="text-sm text-[#6b5b73] mb-4 line-clamp-2 font-body"
              >
                {{ prov.bio }}
              </p>

              <!-- Stats -->
              <div
                class="mt-auto pt-4 flex items-center justify-between w-full text-sm border-t border-white/40"
              >
                <span class="text-[#6b5b73] font-body">🛠️ {{ (prov.services ?? []).length }} services</span>
                <span class="text-[#e4299c] font-semibold group-hover:underline">
                  View →
                </span>
              </div>
            </div>
          </Link>
        </div>
      </div>

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
            class="block overflow-hidden rounded-2xl backdrop-blur-lg bg-white/40 shadow-xl border border-white/60 hover:shadow-2xl hover:scale-105 transition-all duration-300"
          >
            <!-- Image with gradient overlay -->
            <div class="relative overflow-hidden">
              <img
                :src="service.banner ? `/storage/${service.banner}` : '/images/default-banner.jpg'"
                alt="Service Banner"
                class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-110"
              />
              <!-- Gradient overlay -->
              <div class="absolute inset-0 bg-gradient-to-br from-[#e4299c]/30 to-[#e8662c]/30 mix-blend-multiply"></div>
            </div>

            <div class="p-5">
              <h3 class="text-xl font-bold mb-2 text-[#2D1810]">{{ service.title }}</h3>
              <p class="text-sm text-[#6b5b73] mb-2 font-body">
                Provider: <span class="font-semibold">{{ service.provider?.user?.name || 'Unknown' }}</span>
              </p>

              <!-- Rating -->
              <div class="flex items-center text-sm mb-2">
                <div class="flex">
                  <span
                    v-for="n in 5"
                    :key="n"
                    class="text-lg"
                    :class="{
                      'text-yellow-500': (service.rating || 0) >= n,
                      'text-gray-300': (service.rating || 0) < n
                    }"
                  >★</span>
                </div>
                <span class="ml-2 text-[#6b5b73] font-body">
                  {{ service.rating ? `${service.rating}/5` : 'Not rated' }}
                </span>
              </div>

              <!-- Price -->
              <p class="text-sm text-[#e8662c] font-bold mb-3 font-body">
                Price: € {{ service.price ?? 'Free' }}
              </p>

              <!-- Labels -->
              <div v-if="service.labels?.length" class="flex flex-wrap gap-2">
                <span
                  v-for="(label, i) in service.labels"
                  :key="i"
                  class="backdrop-blur-sm bg-[#e4299c]/20 text-[#e4299c] px-3 py-1 rounded-full text-xs font-semibold border border-[#e4299c]/30"
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
