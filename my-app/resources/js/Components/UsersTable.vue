<script setup>
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'

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
  admin: 'bg-purple-200/60 text-purple-900 backdrop-blur-sm',
  provider: 'bg-amber-200/60 text-amber-900 backdrop-blur-sm',
  reguser: 'bg-blue-200/60 text-blue-900 backdrop-blur-sm',
}[r] || 'bg-gray-200/60 text-gray-800 backdrop-blur-sm')

const deletingId = ref(null)

const usersList = ref(props.users.data)
watch(() => props.users, (newVal) => {
  usersList.value = newVal.data
})

const confirmDelete = (u) => {
  if (u.id === (props.auth?.user?.id || 0)) return
  if (!window.confirm(`Delete user "${u.name}" (ID: ${u.id})? This cannot be undone.`)) return

  deletingId.value = u.id

  router.delete(route('admin-panel.users.destroy', u.id), {
    preserveScroll: true,
    onFinish: () => {
      deletingId.value = null
      usersList.value = usersList.value.filter(user => user.id !== u.id)
    },
  })
}
</script>

<template>
  <div class="backdrop-blur-lg bg-white/40 rounded-2xl shadow-2xl border border-white/60 overflow-hidden font-heading">
    
    <!-- Search/Filter Bar -->
    <div class="p-4 flex flex-col sm:flex-row flex-wrap items-start sm:items-center gap-3 sm:gap-4 border-b border-white/40 bg-white/20 backdrop-blur-sm">
      <input
        v-model="search"
        type="text"
        placeholder="Search users..."
        class="border border-white/40 rounded-lg px-3 py-2 text-sm backdrop-blur-sm bg-white/50 focus:bg-white/70 focus:ring-2 focus:ring-[#e4299c] transition-all w-full sm:w-auto"
      />

      <select v-model="sort" class="border border-white/40 rounded-lg px-3 py-2 text-sm backdrop-blur-sm bg-white/50 focus:bg-white/70 focus:ring-2 focus:ring-[#e4299c] transition-all w-full sm:w-auto">
        <option v-for="(label, key) in sortOptions" :key="key" :value="key">
          {{ label }}
        </option>
      </select>

      <div class="flex flex-wrap gap-3 items-center">
        <label v-for="r in roleFilters" :key="r" class="text-sm flex items-center gap-1.5 cursor-pointer">
          <input type="checkbox" :value="r" v-model="selectedFilters" class="rounded text-[#e4299c] focus:ring-[#e4299c]" />
          <span class="capitalize text-[#2D1810]">{{ r }}</span>
        </label>
      </div>
    </div>

    <!-- Responsive Table Wrapper -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-white/30">
        <thead class="bg-white/30 backdrop-blur-sm">
          <tr>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">ID</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">Name</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">Email</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">Role</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider hidden lg:table-cell">Created</th>
            <th class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-[#2D1810] uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/20 font-body">
          <tr v-for="u in usersList || []" :key="u.id" class="hover:bg-white/30 transition-colors duration-150">
            <td class="px-4 sm:px-6 py-3 text-sm text-[#6b5b73] font-medium">{{ u.id }}</td>
            <td class="px-4 sm:px-6 py-3 text-sm font-semibold text-[#2D1810]">{{ u.name }}</td>
            <td class="px-4 sm:px-6 py-3 text-sm text-[#6b5b73]">{{ u.email }}</td>
            <td class="px-4 sm:px-6 py-3">
              <span v-if="u.role" class="px-2 py-1 rounded-full text-xs font-semibold" :class="roleBadge(u.role)">
                {{ u.role }}
              </span>
              <span v-else class="text-gray-400 text-sm">—</span>
            </td>
            <td class="px-4 sm:px-6 py-3 text-sm text-[#6b5b73] hidden lg:table-cell">{{ new Date(u.created_at).toLocaleString() }}</td>
            <td class="px-4 sm:px-6 py-3">
              <button
                @click="confirmDelete(u)"
                class="px-3 py-1.5 text-xs rounded-lg bg-red-500/80 backdrop-blur-sm text-white hover:bg-red-600 disabled:opacity-50 transition-all duration-200 font-semibold"
                :disabled="deletingId === u.id || u.id === auth?.user?.id"
                :title="u.id === auth?.user?.id ? 'You cannot delete yourself' : 'Delete user'"
              >
                Delete
              </button>
            </td>
          </tr>
          <tr v-if="!users?.data?.length">
            <td colspan="6" class="px-6 py-12 text-center text-[#6b5b73] font-body">No users found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="p-4 flex flex-wrap gap-2 bg-white/20 backdrop-blur-sm border-t border-white/30" v-if="users?.links">
      <Link
        v-for="link in users.links"
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
