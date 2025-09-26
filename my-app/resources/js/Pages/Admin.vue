<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'

const props = defineProps({
  auth: Object,
  users: Object,     // paginator
  filters: Object,   // { search }
})

const search = ref(props.filters?.search || '')
let t
watch(search, () => {
  clearTimeout(t)
  t = setTimeout(() => {
    // avoid Ziggy until it's set up; use plain path
    router.get('/admin', { search: search.value || undefined }, {
      preserveState: true,
      replace: true,
    })
  }, 300)
})

const roleBadge = (r) => ({
  admin: 'bg-purple-100 text-purple-800',
  provider: 'bg-amber-100 text-amber-800',
  reguser: 'bg-blue-100 text-blue-800',
}[r] || 'bg-gray-100 text-gray-700')
</script>

<template>
  <MainLayout :user="auth?.user">
    <div class="max-w-7xl mx-auto p-6 space-y-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
          <h1 class="text-3xl font-bold">Admin — All Users</h1>
          <p class="text-gray-600">Browse every registered user on the site.</p>
        </div>
        <input
          v-model="search"
          placeholder="Search name or email…"
          class="rounded-lg border px-3 py-2 w-full md:w-80"
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
