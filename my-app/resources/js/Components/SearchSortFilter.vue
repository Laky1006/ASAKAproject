<template>
  <div class="flex flex-wrap items-center gap-4 mb-4 relative" ref="root">
    <!-- Search -->
    <input
      :value="search"
      @input="$emit('update:search', $event.target.value)"
      :placeholder="searchPlaceholder"
      type="text"
      class="px-4 py-2 w-full sm:w-1/3 rounded-full border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-sm"
    />

    <!-- Sort -->
    <div class="relative" ref="sortBox" data-sort-container>
      <button
        @click="toggleSortDropdown"
        class="flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-gray-300 shadow-sm hover:bg-blue-200 transition"
        data-sort-toggle
        type="button"
        :aria-expanded="showSortDropdown.toString()"
        aria-haspopup="listbox"
      >
        &#128218; Sort by: {{ sortOptions[sort] ?? '—' }}
      </button>

      <div
        v-if="showSortDropdown"
        class="absolute top-full mt-2 w-48 bg-white border border-gray-200 shadow-lg rounded-lg z-20"
        role="listbox"
      >
        <button
          v-for="(label, key) in sortOptions"
          :key="key"
          @click="selectSortOption(key)"
          class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
          :aria-selected="sort === key"
          type="button"
        >
          {{ label }}
        </button>
      </div>
    </div>

    <!-- Filter -->
    <div class="relative" ref="filterBox">
      <button
        class="flex items-center gap-2 px-4 py-2 rounded-full bg-white text-black hover:bg-blue-200 border border-gray-300 shadow-sm transition"
        @click="toggleFilterDropdown"
        data-filter-toggle
        type="button"
        :aria-expanded="showFilterDropdown.toString()"
        aria-haspopup="dialog"
      >
        <span>&#128317;</span> Filter
      </button>

      <div
        v-if="showFilterDropdown"
        data-filter-container
        class="absolute top-full mt-2 bg-white border border-gray-200 shadow-lg rounded-lg z-10 w-72 p-3 left-0"
      >
        <div v-if="filters.length" class="flex flex-wrap gap-2">
          <button
            v-for="label in filters"
            :key="label"
            @click="toggleLabel(label)"
            :class="[
              'px-3 py-1 rounded-full text-sm',
              selectedFilters.includes(label)
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-800 hover:bg-gray-300'
            ]"
            type="button"
          >
            {{ label }}
          </button>
        </div>
        <div v-else class="text-sm text-gray-500 px-1 py-0.5">
          No filters available
        </div>
      </div>
    </div>

    <!-- Selected filter chips -->
    <div v-if="showSelectedChips && selectedFilters.length" class="flex flex-wrap gap-2 w-full">
      <span
        v-for="label in selectedFilters"
        :key="label"
        class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm flex items-center"
      >
        {{ label }}
        <button
          @click="removeLabel(label)"
          class="ml-2 text-red-600 hover:text-red-800"
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

/**
 * Props
 * - search: string (v-model:search)
 * - sort: string (v-model:sort) — key from sortOptions
 * - selectedFilters: string[] (v-model:selectedFilters)
 * - sortOptions: Record<string, string> {key -> label}
 * - filters: string[] — available filter options
 * - searchPlaceholder?: string
 * - showSelectedChips?: boolean (default true)
 */
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
