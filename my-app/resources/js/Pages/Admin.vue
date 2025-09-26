<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'

// imported component
import SearchFindSort from '@/Components/SearchSortFilter.vue'

const props = defineProps({
  auth: Object,
  users: Object,          // paginator
  filters: Object,        // { search?: string, sort?: string, roles?: string[] }
})

// local state synced with query
const search = ref(props.filters?.search || '')
const sort = ref(props.filters?.sort || 'name_asc')
const selectedFilters = ref(Array.isArray(props.filters?.roles) ? props.filters.roles : [])

// options for the component
const sortOptions = {
  name_asc: 'Name A–Z',
  name_desc: 'Name Z–A',
  id_asc: 'ID ↑ (lowest first)',
  id_desc: 'ID ↓ (highest first)',
}
const roleFilters = ['admin', 'reguser', 'provider']

// single debouncer for any change
let t
const pushQuery = () => {
  clearTimeout(t)
  t = setTimeout(() => {
    const params = {
      search: search.value || undefined,
      sort: sort.value || undefined,
    }
    // only send roles[] if at least one is selected
    if (selectedFilters.value.length) {
      // Inertia will serialize arrays like roles[]=admin&roles[]=provider
      params.roles = selectedFilters.value
    }
    router.get('/admin', params, {
      preserveState: true,
      replace: true,
    })
  }, 300)
}

// watch all 3 inputs
watch(search, pushQuery)
watch(sort, pushQuery)
watch(selectedFilters, pushQuery, { deep: true })

const roleBadge = (r) => ({
  admin: 'bg-purple-100 text-purple-800',
  provider: 'bg-amber-100 text-amber-800',
  reguser: 'bg-blue-100 text-blue-800',
}[r] || 'bg-gray-100 text-gray-700')
</script>

<template>
  <MainLayout :user="auth?.user">
    <div class="max-w-7xl mx-auto p-6 space-y-6">
      <div class="flex flex-col gap-3">
        <div>
          <h1 class="text-3xl font-bold">Admin — All Users</h1>
          <p class="text-gray-600">Browse every registered user on the site.</p>
        </div>

        <!-- ⬇️ Drop in your Search/Sort/Filter component -->
        <SearchFindSort
          v-model:search="search"
          v-model:sort="sort"
          v-model:selectedFilters="selectedFilters"
          :sortOptions="sortOptions"
          :filters="roleFilters"
          searchPlaceholder="Search name or email…"
          :showSelectedChips="true"
        />
      </div>

      <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">ID</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Name</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Email</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Role</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Created</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="u in users?.data || []" :key="u.id" class="hover:bg-gray-50">
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
            </tr>
            <tr v-if="!users || !users.data || !users.data.length">
              <td colspan="5" class="px-6 py-8 text-center text-gray-500">No users found.</td>
            </tr>
          </tbody>
        </table>

        <div class="p-4 flex flex-wrap gap-2" v-if="users?.links">
          <Link
            v-for="link in users.links"
            :key="(link.url || '') + link.label"
            :href="link.url || ''"
            :preserve-state="true"
            v-html="link.label"
            :class="[
              'px-3 py-1 rounded border text-sm',
              link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50',
              !link.url ? 'opacity-50 pointer-events-none' : ''
            ]"
          />
        </div>
      </div>
    </div>
  </MainLayout>
</template>
