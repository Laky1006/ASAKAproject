<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  auth: Object,
  reports: Object,
  filters: Object,
})

const search = ref(props.filters?.search || '')
const sort = ref(props.filters?.sort || 'id_desc')

let t
const pushQuery = () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get('/admin/reports', { search: search.value, sort: sort.value }, {
      preserveState: true,
      replace: true
    })
  }, 300)
}

watch(search, pushQuery)
watch(sort, pushQuery)

const deletingId = ref(null)
const confirmDelete = (r) => {
  if (!window.confirm(`Delete report #${r.id}? This cannot be undone.`)) return

  deletingId.value = r.id
  router.delete(route('admin.reports.destroy', r.id), {
    preserveScroll: true,
    onFinish: () => (deletingId.value = null),
  })
}
</script>

<template>
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">ID</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">User</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Service</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Review</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Reason</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Created</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="r in reports?.data || []" :key="r.id" class="hover:bg-gray-50">
          <td class="px-6 py-3 text-sm text-gray-700">{{ r.id }}</td>
          <td class="px-6 py-3 text-sm">{{ r.user?.name || '—' }}</td>
          <td class="px-6 py-3 text-sm">{{ r.service?.name || '—' }}</td>
          <td class="px-6 py-3 text-sm">{{ r.review_id || '—' }}</td>
          <td class="px-6 py-3 text-sm">{{ r.reason }}</td>
          <td class="px-6 py-3 text-sm">{{ new Date(r.created_at).toLocaleString() }}</td>
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
