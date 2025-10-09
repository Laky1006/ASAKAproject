<template>
  <div class="flex flex-wrap items-center gap-4 mb-8 relative font-heading" ref="root">
    <!-- Search - Glassmorphic -->
    <input
      :value="search"
      @input="$emit('update:search', $event.target.value)"
      :placeholder="searchPlaceholder"
      type="text"
      class="px-6 py-4 w-full sm:flex-1 rounded-2xl backdrop-blur-md bg-white/50 border border-white/60 focus:ring-2 focus:ring-[#e4299c] focus:bg-white/70 focus:outline-none shadow-lg transition-all text-[#2D1810] placeholder-[#6b5b73] font-body"
    />

    <!-- Sort Button -->
    <div class="relative" ref="sortBox" data-sort-container>
      <button
        @click="toggleSortDropdown"
        class="flex items-center gap-3 px-6 py-4 rounded-2xl backdrop-blur-md bg-white/50 border border-white/60 shadow-lg hover:bg-white/70 transition-all font-semibold text-[#2D1810]"
        data-sort-toggle
        type="button"
        :aria-expanded="showSortDropdown.toString()"
        aria-haspopup="listbox"
      >
        Sort: {{ sortOptions[sort] ?? 'Select' }}
        <svg class="w-4 h-4 transition-transform" :class="showSortDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>

      <div
        v-if="showSortDropdown"
        class="absolute top-full mt-2 min-w-full backdrop-blur-lg bg-white/90 border border-white/80 shadow-2xl rounded-2xl z-20 overflow-hidden"
        role="listbox"
      >
        <button
          v-for="(label, key) in sortOptions"
          :key="key"
          @click="selectSortOption(key)"
          class="block w-full text-left px-6 py-3 text-sm hover:bg-[#e4299c]/10 transition-colors font-body"
          :class="sort === key ? 'bg-[#e4299c]/20 text-[#e4299c] font-semibold' : 'text-[#2D1810]'"
          :aria-selected="sort === key"
          type="button"
        >
          {{ label }}
        </button>
      </div>
    </div>

    <!-- Filter Button -->
<div class="relative" ref="filterBox">
  <button
    class="flex items-center gap-3 px-6 py-4 rounded-2xl backdrop-blur-md bg-white/50 border border-white/60 shadow-lg hover:bg-white/70 transition-all font-semibold text-[#2D1810]"
    @click="toggleFilterDropdown"
    data-filter-toggle
    type="button"
    :aria-expanded="showFilterDropdown.toString()"
    aria-haspopup="dialog"
  >
    Filter
    <span v-if="selectedFilters.length" class="px-2 py-0.5 rounded-full bg-[#e4299c] text-white text-xs font-bold">
      {{ selectedFilters.length }}
    </span>
    <svg class="w-4 h-4 transition-transform" :class="showFilterDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
    </svg>
  </button>

  <div
    v-if="showFilterDropdown"
    data-filter-container
    class="absolute top-full mt-2 backdrop-blur-lg bg-white/90 border border-white/80 shadow-2xl rounded-2xl z-10 w-80 max-w-[calc(100vw-2rem)] p-5 right-0"
  >
    <p class="text-sm font-semibold text-[#2D1810] mb-3">Select Categories:</p>
    <div v-if="filters.length" class="flex flex-wrap gap-2">
      <button
        v-for="label in filters"
        :key="label"
        @click="toggleLabel(label)"
        :class="[
          'px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200',
          selectedFilters.includes(label)
            ? 'bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white shadow-lg'
            : 'bg-white/60 text-[#6b5b73] hover:bg-white/80 border border-white/60'
        ]"
        type="button"
      >
        {{ label }}
      </button>
    </div>
    <div v-else class="text-sm text-[#6b5b73] px-1 py-2 font-body">
      No filters available
    </div>
  </div>
</div>


    <!-- Selected filter chips -->
    <div v-if="showSelectedChips && selectedFilters.length" class="flex flex-wrap gap-2 w-full mt-2">
      <span
        v-for="label in selectedFilters"
        :key="label"
        class="backdrop-blur-sm bg-[#e4299c]/20 text-[#e4299c] px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 border border-[#e4299c]/30"
      >
        {{ label }}
        <button
          @click="removeLabel(label)"
          class="text-[#e4299c] hover:text-[#ff6b9d] font-bold text-lg transition-colors"
          type="button"
          aria-label="Remove filter"
        >
          ×
        </button>
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  search: { type: String, default: '' },
  sort: { type: String, default: '' },
  selectedFilters: { type: Array, default: () => [] },
  sortOptions: { type: Object, required: true },
  filters: { type: Array, default: () => [] },
  searchPlaceholder: { type: String, default: 'Search…' },
  showSelectedChips: { type: Boolean, default: true },
})

const emit = defineEmits([
  'update:search',
  'update:sort',
  'update:selectedFilters',
])

const showFilterDropdown = ref(false)
const showSortDropdown = ref(false)

const root = ref(null)
const sortBox = ref(null)
const filterBox = ref(null)

const toggleFilterDropdown = () => (showFilterDropdown.value = !showFilterDropdown.value)
const toggleSortDropdown = () => (showSortDropdown.value = !showSortDropdown.value)

const selectSortOption = (key) => {
  emit('update:sort', key)
  showSortDropdown.value = false
}

const toggleLabel = (label) => {
  const next = props.selectedFilters.includes(label)
    ? props.selectedFilters.filter(l => l !== label)
    : [...props.selectedFilters, label]
  emit('update:selectedFilters', next)
}

const removeLabel = (label) => {
  emit('update:selectedFilters', props.selectedFilters.filter(l => l !== label))
}

const handleClickOutside = (event) => {
  const target = event.target
  const inSort = sortBox.value?.contains(target) || target.closest?.('[data-sort-toggle]')
  const inFilter = filterBox.value?.contains(target) || target.closest?.('[data-filter-toggle]')
  if (!inSort) showSortDropdown.value = false
  if (!inFilter) showFilterDropdown.value = false
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
</script>
