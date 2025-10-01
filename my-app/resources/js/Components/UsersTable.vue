<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  auth: Object,
  users: Object,
  filters: Object,
})

const search = ref(props.filters?.search || '')
const sort = ref(props.filters?.sort || 'name_asc')
const selectedFilters = ref(Array.isArray(props.filters?.roles) ? props.filters.roles : [])

const sortOptions = {
  name_asc: 'Name A–Z',
  name_desc: 'Name Z–A',
  id_asc: 'ID ↑',
  id_desc: 'ID ↓',
}
const roleFilters = ['admin', 'reguser', 'provider']

let t
const pushQuery = () => {
  clearTimeout(t)
  t = setTimeout(() => {
    const params = {
      search: search.value || undefined,
      sort: sort.value || undefined,
    }
    if (selectedFilters.value.length) params.roles = selectedFilters.value

    router.get('/admin-panel', params, { preserveState: true, replace: true })
  }, 300)
}

watch(search, pushQuery)
watch(sort, pushQuery)
watch(selectedFilters, pushQuery, { deep: true })

const roleBadge = (r) => ({
  admin: 'bg-purple-100 text-purple-800',
  provider: 'bg-amber-100 text-amber-800',
  reguser: 'bg-blue-100 text-blue-800',
}[r] || 'bg-gray-100 text-gray-700')

const deletingId = ref(null)

const usersList = ref(props.users.data)

const confirmDelete = (u) => {
  if (u.id === (props.auth?.user?.id || 0)) return
  if (!window.confirm(`Delete user "${u.name}" (ID: ${u.id})? This cannot be undone.`)) return

  deletingId.value = u.id

  router.delete(route('admin-panel.users.destroy', u.id), {
    preserveScroll: true,
    onFinish: () => {
      deletingId.value = null
      // remove deleted user locally
      usersList.value = usersList.value.filter(user => user.id !== u.id)
    },
  })
}

</script>

<template>
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">ID</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Name</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Email</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Role</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Created</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="u in usersList || []" :key="u.id" class="hover:bg-gray-50">
          <td class="px-6 py-3 text-sm text-gray-700">{{ u.id }}</td>
          <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ u.name }}</td>
          <td class="px-6 py-3 text-sm text-gray-700">{{ u.email }}</td>
          <td class="px-6 py-3">
            <span v-if="u.role" class="px-2 py-1 rounded-full text-xs font-semibold" :class="roleBadge(u.role)">
              {{ u.role }}
            </span>
            <span v-else class="text-gray-400 text-sm">—</span>
          </td>
          <td class="px-6 py-3 text-sm text-gray-700">{{ new Date(u.created_at).toLocaleString() }}</td>
          <td class="px-6 py-3">
            <button
              @click="confirmDelete(u)"
              class="px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700 disabled:opacity-50"
              :disabled="deletingId === u.id || u.id === auth?.user?.id"
              :title="u.id === auth?.user?.id ? 'You cannot delete yourself' : 'Delete user'"
            >
              Delete
            </button>
          </td>
        </tr>
        <tr v-if="!users?.data?.length">
          <td colspan="6" class="px-6 py-8 text-center text-gray-500">No users found.</td>
        </tr>
      </tbody>
    </table>

    <div class="p-4 flex flex-wrap gap-2" v-if="users?.links">
      <Link
        v-for="link in users.links"
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
