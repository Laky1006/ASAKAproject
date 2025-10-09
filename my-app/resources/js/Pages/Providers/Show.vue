<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  provider: Object,
  auth: Object,
})
</script>

<template>
  <MainLayout :user="auth.user">
    <section class="max-w-6xl mx-auto py-10 px-6">

      <Link
  :href="route('home', { tab: 'providers' })"
  class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 mb-6 font-medium transition"
>
  ← Back to Providers
</Link>



      <!-- 🧍 Provider profile header -->
<div class="rounded-2xl overflow-hidden shadow-md border border-gray-100 mb-10">
  <!-- banner -->
  <div class="h-28 sm:h-36 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>

  <div class="p-6 sm:p-8 bg-white">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
      <!-- left: avatar + basics -->
      <div class="flex items-start gap-4">
        <!-- avatar -->
        <img
          :src="provider.user?.profile_photo ? `/storage/${provider.user.profile_photo}` : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(provider.user?.name || 'P') + '&background=E5E7EB&color=111827&size=128'"
          alt="Provider photo"
          class="w-20 h-20 sm:w-24 sm:h-24 rounded-full ring-4 ring-white -mt-12 shadow"
        />
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900">
            {{ provider.user?.name }}
          </h1>
          <p class="text-gray-600 mt-1 flex items-center gap-2">
            <span>📍</span>
            <span>{{ provider.location || 'Location not provided' }}</span>
          </p>

          <!-- mini chips (customize) -->
          <div class="mt-3 flex flex-wrap gap-2">
            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
              Provider
            </span>
            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
              Services: {{ provider.services?.length || 0 }}
            </span>
          </div>
        </div>
      </div>

      <!-- right: quick actions -->
      <div class="flex items-center gap-3">
        <!-- <a
          v-if="provider.user?.email"
          :href="`mailto:${provider.user.email}`"
          class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 transition"
        >✉️ Contact</a> -->

        <a
          v-if="provider.location"
          :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(provider.location)}`"
          target="_blank" rel="noopener"
          class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition"
        >🗺️ View on Maps</a>
      </div>
    </div>

    <!-- bio -->
    <p v-if="provider.bio" class="mt-6 text-gray-700 leading-relaxed">
      {{ provider.bio }}
    </p>
    <p v-else class="mt-6 text-gray-500 italic">No bio provided.</p>
  </div>
</div>


      <!-- 💅 Services Section -->
      <h2 class="text-2xl font-semibold mb-6 text-gray-800">
        Services by {{ provider.user?.name }}
      </h2>

      <div
        v-if="provider.services?.length"
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6"
      >
        <Link
          v-for="service in provider.services"
          :key="service.id"
          :href="route('services.show', service.id)"
          class="block overflow-hidden rounded-lg shadow transition duration-300 transform hover:scale-[1.02] hover:shadow-lg cursor-pointer bg-white"
        >
          <img
            :src="service.banner ? `/storage/${service.banner}` : '/images/default-banner.jpg'"
            alt="Service Banner"
            class="w-full h-40 object-cover"
          />
          <div class="p-4">
            <h3 class="text-xl font-semibold mb-2">{{ service.title }}</h3>

            <p class="text-sm text-gray-600 mb-1">
              Provider: {{ service.provider?.user?.name || 'Unknown' }}
            </p>

            <div class="flex items-center text-sm">
              <div class="flex">
                <span
                  v-for="n in 5"
                  :key="n"
                  class="text-sm"
                  :class="{
                    'text-yellow-500': (service.rating || 0) >= n,
                    'text-gray-300': (service.rating || 0) < n
                  }"
                >★</span>
              </div>
              <span class="ml-2 text-gray-600">
                {{ service.rating ? `${service.rating}/5` : 'Not rated' }}
              </span>
            </div>

            <p class="text-sm text-gray-700 mt-2">
              Price: € {{ service.price ?? 'Free' }}
            </p>

            <div v-if="service.labels?.length" class="flex flex-wrap gap-2 mt-2">
              <span
                v-for="(label, i) in service.labels"
                :key="i"
                class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs transition-opacity duration-300 hover:opacity-80"
              >
                {{ label }}
              </span>
            </div>
          </div>
        </Link>
      </div>

      <div v-else class="text-gray-500 text-center mt-10">
        This provider hasn’t added any services yet.
      </div>
    </section>
  </MainLayout>
</template>
