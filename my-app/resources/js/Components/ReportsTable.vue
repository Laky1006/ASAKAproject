<script setup>
import { ref, watch, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import PopupConfirm from '@/Components/PopupConfirm.vue'

const props = defineProps({
  auth: Object,
  reports: Object,
  filters: Object,
})

// Filters
const search = ref(props.filters?.search || '')
const sort = ref(props.filters?.sort || 'id_desc')
const type = ref(props.filters?.type || '')

const sortOptions = {
  id_desc: 'ID ↓',
  id_asc: 'ID ↑',
  created_desc: 'Created ↓',
  created_asc: 'Created ↑',
}

const typeOptions = {
  '': 'All Types',
  service: 'Services',
  comment: 'Comments',
}

let t
const pushQuery = () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(route('admin-panel.dashboard'), {
      tab: 'reports',
      search: search.value || undefined,
      sort: sort.value || undefined,
      type: type.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}

// Watch filters
watch(search, pushQuery)
watch(sort, pushQuery)
watch(type, pushQuery)

const deletingId = ref(null)
const reportsList = ref(props.reports.data)
watch(() => props.reports, (newVal) => {
  reportsList.value = newVal.data
})

/* -------------------- Popup Delete Flow -------------------- */
const showDelete = ref(false)
const reportToDelete = ref(null)

const openDeletePopup = (r) => {
  reportToDelete.value = r
  showDelete.value = true
}

const deleteMessage = computed(() =>
  reportToDelete.value
    ? `Delete report #${reportToDelete.value.id}? This cannot be undone.`
    : ''
)

const performDelete = () => {
  const r = reportToDelete.value
  if (!r) return
  deletingId.value = r.id

  router.delete(route('admin-panel.reports.destroy', r.id), {
    preserveScroll: true,
    onFinish: () => {
      deletingId.value = null
      reportToDelete.value = null
      router.reload({ only: ['reports'] })
    },
  })
}
/* ----------------------------------------------------------- */
</script>

<template>
  <div class="backdrop-blur-lg bg-white/40 rounded-2xl shadow-2xl border border-white/60 overflow-hidden font-heading">

    <!-- Filters -->
    <div class="p-4 flex flex-col sm:flex-row flex-wrap items-start sm:items-center gap-3 sm:gap-4 border-b border-white/40 bg-white/20 backdrop-blur-sm">
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

      <select v-model="type" class="border border-white/40 rounded-lg px-3 py-2 text-sm backdrop-blur-sm bg-white/50 focus:bg-white/70 focus:ring-2 focus:ring-[#e4299c] transition-all w-full sm:w-auto">
        <option v-for="(label, key) in typeOptions" :key="key" :value="key">
          {{ label }}
        </option>
      </select>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-white/30">
        <thead class="bg-white/30 backdrop-blur-sm">
          <tr>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">ID</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">Reporter</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">Target</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">Type</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">Reason</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider hidden lg:table-cell">Created</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">Actions</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-white/20 font-body">
          <tr v-for="r in reportsList" :key="r.id" class="hover:bg-white/30 transition-colors duration-150">
            <td class="px-4 sm:px-6 py-3 text-sm text-[#6b5b73] font-medium">{{ r.id }}</td>
            <td class="px-4 sm:px-6 py-3 text-sm font-semibold text-[#2D1810]">{{ r.user?.name || 'Unknown' }}</td>

            <!-- Target -->
            <td class="px-4 sm:px-6 py-3 text-sm text-[#e4299c] font-medium">
              <Link
                v-if="r.service"
                :href="route('services.show', r.service.id)"
                class="hover:underline"
              >
                {{ r.service.title }}
              </Link>
              <Link
                v-else-if="r.review && r.review.service_id"
                :href="`${route('services.show', r.review.service_id)}#review-${r.review.id}`"
                class="hover:underline"
              >
                Review #{{ r.review.id }}
              </Link>
              <span v-else class="text-gray-400">—</span>
            </td>

            <!-- Type Badge -->
            <td class="px-4 sm:px-6 py-3 text-sm">
              <span
                :class="[
                  'px-2 py-1 rounded-lg text-xs font-semibold',
                  r.type === 'service'
                    ? 'bg-blue-100 text-blue-800'
                    : r.type === 'comment'
                    ? 'bg-amber-100 text-amber-800'
                    : 'bg-gray-100 text-gray-600'
                ]"
              >
                {{ r.type }}
              </span>
            </td>

            <td class="px-4 sm:px-6 py-3 text-sm text-[#6b5b73]">{{ r.reason || '—' }}</td>
            <td class="px-4 sm:px-6 py-3 text-sm text-[#6b5b73] hidden lg:table-cell">
              {{ new Date(r.created_at).toLocaleString() }}
            </td>

            <td class="px-4 sm:px-6 py-3">
              <button
                @click="openDeletePopup(r)"
                class="px-3 py-1.5 text-xs rounded-lg bg-red-500/80 backdrop-blur-sm text-white hover:bg-red-600 disabled:opacity-50 transition-all duration-200 font-semibold"
                :disabled="deletingId === r.id"
              >
                Delete
              </button>
            </td>
          </tr>

          <tr v-if="!reports?.data?.length">
            <td colspan="7" class="px-6 py-12 text-center text-[#6b5b73] font-body">No reports found.</td>
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
          link.active
            ? 'bg-[#e4299c] text-white border-[#e4299c] shadow-lg'
            : 'bg-white/60 hover:bg-white/80 border-white/60 text-[#6b5b73]',
          !link.url ? 'opacity-50 pointer-events-none' : ''
        ]"
        :preserve-state="true"
      />
    </div>
  </div>

  <!-- Popup Confirm -->
  <PopupConfirm
    v-model:show="showDelete"
    title="Please Confirm"
    :message="deleteMessage"
    @confirm="performDelete"
  />
</template>
