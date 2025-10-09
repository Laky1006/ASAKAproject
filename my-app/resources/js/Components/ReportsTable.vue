<script setup>
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({
  auth: Object,
  reports: Object,
  filters: Object,
})

const search = ref(props.filters?.search || '')
const sort = ref(props.filters?.sort || 'id_desc')

const sortOptions = {
  id_desc: 'ID ↓',
  id_asc: 'ID ↑',
}

let t
const pushQuery = () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get('/admin-panel', {
      tab: 'reports',
      search: search.value || undefined,
      sort: sort.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}

watch(search, pushQuery)
watch(sort, pushQuery)

const deletingId = ref(null)
const reportsList = ref(props.reports.data)
watch(() => props.reports, (newVal) => {
  reportsList.value = newVal.data
})

const confirmDelete = (r) => {
  if (!window.confirm(`Delete report ID: ${r.id}?`)) return
  deletingId.value = r.id

  router.delete(route('admin-panel.reports.destroy', r.id), {
    preserveScroll: true,
    onFinish: () => {
      deletingId.value = null
      reportsList.value = reportsList.value.filter(x => x.id !== r.id)
    },
  })
}
</script>

<template>
  <div class="backdrop-blur-lg bg-white/40 rounded-2xl shadow-2xl border border-white/60 overflow-hidden font-heading">
    
    <!-- Search/Filter Bar -->
    <div class="p-4 flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4 border-b border-white/40 bg-white/20 backdrop-blur-sm">
      <input
        v-model="search"
        type="text"
        placeholder="Search reports..."
        class="border border-white/40 rounded-lg px-3 py-2 text-sm backdrop-blur-sm bg-white/50 focus:bg-white/70 focus:ring-2 focus:ring-[#e4299c] transition-all w-full sm:w-auto"
      />

      <select v-model="sort" class="border border-white/40 rounded-lg px-3 py-2 text-sm backdrop-blur-sm bg-white/50 focus:bg-white/70 focus:ring-2 focus:ring-[#e4299c] transition-all w-full sm:w-auto">
        <option v-for="(label, key) in sortOptions" :key="key" :value="key">
          {{ label }}
        </option>
      </select>
    </div>

    <!-- Responsive Table Wrapper -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-white/30">
        <thead class="bg-white/30 backdrop-blur-sm">
          <tr>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">ID</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">Reporter</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">Reason</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider hidden lg:table-cell">Created</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/20 font-body">
          <tr v-for="r in reportsList" :key="r.id" class="hover:bg-white/30 transition-colors duration-150">
            <td class="px-4 sm:px-6 py-3 text-sm text-[#6b5b73] font-medium">{{ r.id }}</td>
            <td class="px-4 sm:px-6 py-3 text-sm font-semibold text-[#2D1810]">{{ r.reporter?.name || 'Unknown' }}</td>
            <td class="px-4 sm:px-6 py-3 text-sm text-[#6b5b73]">{{ r.reason || '—' }}</td>
            <td class="px-4 sm:px-6 py-3 text-sm text-[#6b5b73] hidden lg:table-cell">{{ new Date(r.created_at).toLocaleString() }}</td>
            <td class="px-4 sm:px-6 py-3">
              <button
                @click="confirmDelete(r)"
                class="px-3 py-1.5 text-xs rounded-lg bg-red-500/80 backdrop-blur-sm text-white hover:bg-red-600 disabled:opacity-50 transition-all duration-200 font-semibold"
                :disabled="deletingId === r.id"
              >
                Delete
              </button>
            </td>
          </tr>

          <tr v-if="!reports?.data?.length">
            <td colspan="5" class="px-6 py-12 text-center text-[#6b5b73] font-body">No reports found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="p-4 flex flex-wrap gap-2 bg-white/20 backdrop-blur-sm border-t border-white/30" v-if="reports?.links">
      <Link
        v-for="link in reports.links"
        :key="(link.url || '') + link.label"
        :href="link.url || ''"
        v-html="link.label"
        :class="[
          'px-3 py-1.5 rounded-lg border text-sm font-semibold transition-all duration-200',
          link.active ? 'bg-[#e4299c] text-white border-[#e4299c] shadow-lg' : 'bg-white/60 hover:bg-white/80 border-white/60 text-[#6b5b73]',
          !link.url ? 'opacity-50 pointer-events-none' : ''
        ]"
        :preserve-state="true"
      />
    </div>
  </div>
</template>
