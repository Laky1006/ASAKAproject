<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  auth: Object,
  reports: Object,
  filters: Object,
})

const search = ref(props.filters?.search || '')
const sort = ref(props.filters?.sort || 'id_desc')
const type = ref(props.filters?.type || '') // 🆕 Type filter (service/comment)

const sortOptions = {
  id_asc: 'ID ↑',
  id_desc: 'ID ↓',
  created_asc: 'Created ↑',
  created_desc: 'Created ↓',
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
    const params = {
      search: search.value || undefined,
      sort: sort.value || undefined,
      type: type.value || undefined, // 🆕 Include type in query
    }

    router.get(route('admin-panel.dashboard'), { 
      tab: 'reports',
      ...params,
    }, { 
      preserveState: true, 
      replace: true 
    })
  }, 300)
}

watch(search, pushQuery)
watch(sort, pushQuery)
watch(type, pushQuery) // 🆕 Watch type filter

const deletingId = ref(null)

const confirmDelete = (r) => {
  if (!window.confirm(`Delete report #${r.id}? This cannot be undone.`)) return

  deletingId.value = r.id
  router.delete(route('admin-panel.reports.destroy', r.id), {
    preserveScroll: true,
    onFinish: () => {
      deletingId.value = null
      router.reload({ only: ['reports'] }) 
    },
  })
}
</script>

<template>
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <!-- Filters -->
    <div class="p-4 flex flex-wrap items-center gap-4 border-b border-gray-100">
      <input
        v-model="search"
        type="text"
        placeholder="Search reports..."
        class="border rounded px-3 py-1 text-sm"
      />

      <select v-model="sort" class="border rounded px-2 py-1 text-sm">
        <option v-for="(label, key) in sortOptions" :key="key" :value="key">
          {{ label }}
        </option>
      </select>

      <!-- 🆕 Type Filter -->
      <select v-model="type" class="border rounded px-2 py-1 text-sm">
        <option v-for="(label, key) in typeOptions" :key="key" :value="key">
          {{ label }}
        </option>
      </select>
    </div>

    <!-- Table -->
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">ID</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">User</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Target</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Type</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Reason</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Created</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100">
        <tr v-for="r in reports?.data || []" :key="r.id" class="hover:bg-gray-50">
          <td class="px-6 py-3 text-sm text-gray-700">{{ r.id }}</td>
          <td class="px-6 py-3 text-sm text-gray-900">{{ r.user.name }}</td>

          <td class="px-6 py-3 text-sm text-blue-600">
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

          <!-- 🆕 Type Column -->
          <td class="px-6 py-3 text-sm">
            <span
              :class="[
                'px-2 py-1 rounded text-xs font-medium',
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

          <td class="px-6 py-3 text-sm text-gray-700">{{ r.reason }}</td>
          <td class="px-6 py-3 text-sm text-gray-700">{{ new Date(r.created_at).toLocaleString() }}</td>

          <td class="px-6 py-3">
            <button
              @click="confirmDelete(r)"
              class="px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700 disabled:opacity-50"
              :disabled="deletingId === r.id"
              title="Delete report"
            >
              Delete
            </button>
          </td>
        </tr>

        <tr v-if="!reports?.data?.length">
          <td colspan="7" class="px-6 py-8 text-center text-gray-500">No reports found.</td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="p-4 flex flex-wrap gap-2" v-if="reports?.links">
      <Link
        v-for="link in reports.links"
        :key="(link.url || '') + link.label"
        :href="link.url || ''"
        v-html="link.label"
        :class="[
          'px-3 py-1 rounded border text-sm',
          link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50',
          !link.url ? 'opacity-50 pointer-events-none' : ''
        ]"
        :preserve-state="true"
      />
    </div>
  </div>
</template>
