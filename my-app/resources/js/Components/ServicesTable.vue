<script setup>
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({
  auth: Object,
  services: Object,
  filters: Object,
})

const search = ref(props.filters?.search || '')
const sort = ref(props.filters?.sort || 'id_desc')

const sortOptions = {
  id_desc: 'ID ↓',
  id_asc: 'ID ↑',
  title_asc: 'Title A–Z',
  title_desc: 'Title Z–A',
}

let t
const pushQuery = () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get('/admin-panel', {
      tab: 'services',
      search: search.value || undefined,
      sort: sort.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}

watch(search, pushQuery)
watch(sort, pushQuery)

const deletingId = ref(null)
const servicesList = ref(props.services.data)
watch(() => props.services, (newVal) => {
  servicesList.value = newVal.data
})

const confirmDelete = (s) => {
  if (!window.confirm(`Delete service "${s.title}" (ID: ${s.id})?`)) return
  deletingId.value = s.id

  router.delete(route('admin-panel.services.destroy', s.id), {
    preserveScroll: true,
    onFinish: () => {
      deletingId.value = null
      servicesList.value = servicesList.value.filter(x => x.id !== s.id)
    },
  })
}
</script>

<template>
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="p-4 flex flex-wrap items-center gap-4 border-b border-gray-100">
      <input
        v-model="search"
        type="text"
        placeholder="Search services..."
        class="border rounded px-3 py-1 text-sm"
      />

      <select v-model="sort" class="border rounded px-2 py-1 text-sm">
        <option v-for="(label, key) in sortOptions" :key="key" :value="key">
          {{ label }}
        </option>
      </select>
    </div>

    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">ID</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Title</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Provider</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Rating</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Created</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="s in servicesList" :key="s.id" class="hover:bg-gray-50">
          <td class="px-6 py-3 text-sm text-gray-700">{{ s.id }}</td>
          <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ s.title }}</td>
          <td class="px-6 py-3 text-sm text-gray-700">{{ s.provider?.name || 'Unknown' }}</td>
          <td class="px-6 py-3 text-sm text-gray-700">{{ s.rating ?? '—' }}</td>
          <td class="px-6 py-3 text-sm text-gray-700">{{ new Date(s.created_at).toLocaleString() }}</td>
          <td class="px-6 py-3">
            <button
              @click="confirmDelete(s)"
              class="px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700 disabled:opacity-50"
              :disabled="deletingId === s.id"
            >
              Delete
            </button>
          </td>
        </tr>

        <tr v-if="!services?.data?.length">
          <td colspan="6" class="px-6 py-8 text-center text-gray-500">No services found.</td>
        </tr>
      </tbody>
    </table>

    <div class="p-4 flex flex-wrap gap-2" v-if="services?.links">
      <Link
        v-for="link in services.links"
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
