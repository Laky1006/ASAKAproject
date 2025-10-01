<template>
  <div class="flex flex-col min-h-svh bg-[#FFF8F0]">

    <!-- Mobile Sidebar Overlay -->
    <div
      v-if="showMobileMenu"
      @click="showMobileMenu = false"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 sm:hidden"
    ></div>

    <!-- Mobile Sidebar -->
    <nav
      :class="[
        'fixed top-0 left-0 h-full w-80 bg-white shadow-2xl transform transition-transform duration-300 ease-in-out z-50 sm:hidden',
        showMobileMenu ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <div class="flex items-center justify-between p-6 border-b border-[#febd59]">
        <div class="flex items-center gap-3">
          <img src="/images/B (1).png" class="h-12 w-12 object-contain" />
        </div>
        <button
          @click="showMobileMenu = false"
          class="p-2 text-[#2D1810] hover:text-[#e4299c] transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div class="px-6 py-4 space-y-2">
        <Link href="/" @click="showMobileMenu = false"
          class="block py-3 px-4 text-[#2D1810] hover:text-[#e4299c] hover:bg-[#FFF8F0] rounded-lg">
          Home
        </Link>
        <Link :href="route('about')" @click="showMobileMenu = false"
          class="block py-3 px-4 text-[#2D1810] hover:text-[#e4299c] hover:bg-[#FFF8F0] rounded-lg">
          About
        </Link>

        <!-- Mobile User Section -->
        <div v-if="user" class="border-t border-[#febd59] pt-4 mt-4">
          <div class="flex items-center gap-3 py-3 px-4 mb-2">
            <img
              v-if="user.profile_photo"
              :src="`/storage/${user.profile_photo}`"
              class="w-10 h-10 rounded-full object-cover border-2 border-[#e8662c]"
            />
            <img
              v-else
              src="/images/default-profile.jpg"
              class="w-10 h-10 rounded-full object-cover border-2 border-[#e8662c]"
            />
            <span class="text-[#2D1810] font-medium">{{ user.username }}</span>
          </div>

          <Link :href="route('profile.edit')" @click="showMobileMenu = false"
            class="block py-3 px-4 hover:text-[#e4299c] hover:bg-[#FFF8F0] rounded-lg">
            Profile
          </Link>
          <Link v-if="user.role !== 'admin'" :href="route('my-services')" @click="showMobileMenu = false"
            class="block py-3 px-4 hover:text-[#e4299c] hover:bg-[#FFF8F0] rounded-lg">
            My Services
          </Link>
          <Link v-if="user.role === 'admin'" :href="route('admin.dashboard')" @click="showMobileMenu = false"
            class="block py-3 px-4 hover:text-[#e4299c] hover:bg-[#FFF8F0] rounded-lg">
            Admin
          </Link>
          <Link :href="route('notifications.index')" @click="showMobileMenu = false"
            class="block py-3 px-4 hover:text-[#e4299c] hover:bg-[#FFF8F0] rounded-lg">
            Notifications
          </Link>

          <form @submit.prevent="logout" class="block">
            <button type="submit"
              class="w-full text-left py-3 px-4 hover:text-[#e4299c] hover:bg-[#FFF8F0] rounded-lg">
              Log Out
            </button>
          </form>
        </div>

        <!-- Mobile Login -->
        <div v-else class="border-t border-[#febd59] pt-4 mt-4">
          <Link href="/login" @click="showMobileMenu = false"
            class="block py-3 px-4 hover:text-[#e4299c] hover:bg-[#FFF8F0] rounded-lg">
            Log In
          </Link>
        </div>
      </div>
    </nav>

    <!-- HEADER -->
    <header class="relative px-3 py-2 sm:px-4 sm:py-3 flex justify-between items-center bg-gradient-to-r from-[#febd59] to-[#ffbc59] shadow-lg border-b-4 border-[#e8662c]">
      <div class="flex items-center gap-4">
        <img src="/images/B (1).png" class="h-20 w-20 sm:h-24 sm:w-24 lg:h-28 lg:w-28 object-contain" />
      </div>

      <!-- Mobile Menu Button -->
      <button @click="showMobileMenu = true"
        class="sm:hidden flex items-center justify-center w-10 h-10 text-[#2D1810] hover:text-[#e4299c]"
        aria-label="Open navigation menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <!-- Desktop Navigation -->
      <nav class="hidden sm:flex items-center gap-6 lg:gap-8 text-base lg:text-lg font-medium">
        <Link href="/" class="hover:text-[#e4299c] hover:underline">Home</Link>
        <Link :href="route('about')" class="hover:text-[#e4299c] hover:underline">About</Link>

        <!-- Desktop User Menu -->
        <div v-if="user" class="relative" ref="dropdown">
          <button @click="toggleMenu"
            class="flex items-center gap-2 hover:text-[#e4299c] transition-colors">
            <span>{{ user.username }}</span>
            <img v-if="user.profile_photo" :src="`/storage/${user.profile_photo}`"
              class="w-10 h-10 lg:w-11 lg:h-11 rounded-full object-cover border-2 border-[#e8662c]" />
            <img v-else src="/images/default-profile.jpg"
              class="w-10 h-10 lg:w-11 lg:h-11 rounded-full object-cover border-2 border-[#e8662c]" />
          </button>

          <div v-if="showMenu"
            class="absolute right-0 mt-2 w-44 bg-white shadow-xl rounded-lg overflow-hidden z-50 border border-[#febd59]">
            <Link :href="route('profile.edit')" class="block px-4 py-3 hover:bg-[#FFF8F0] hover:text-[#e4299c]">Profile</Link>
            <Link v-if="user.role !== 'admin'" :href="route('my-services')" class="block px-4 py-3 hover:bg-[#FFF8F0] hover:text-[#e4299c]">My Services</Link>
            <Link v-if="user.role === 'admin'" :href="route('admin.dashboard')" class="block px-4 py-3 hover:bg-[#FFF8F0] hover:text-[#e4299c]">Admin Panel</Link>
            <Link :href="route('notifications.index')" class="block px-4 py-3 hover:bg-[#FFF8F0] hover:text-[#e4299c]">Notifications</Link>

            <form @submit.prevent="logout" class="border-t border-[#febd59]">
              <button type="submit" class="w-full text-left px-4 py-3 hover:bg-[#FFF8F0] hover:text-[#e4299c]">Log Out</button>
            </form>
          </div>
        </div>

        <div v-else>
          <Link href="/login" class="hover:text-[#e4299c] hover:underline">Log In</Link>
        </div>
      </nav>
    </header>

    <main class="grow">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-[#febd59] to-[#ffbc59] text-center py-6 mt-8 border-t-4 border-[#e8662c]">
      <div class="max-w-6xl mx-auto px-4">
        <p class="font-medium text-sm sm:text-base">© 2025 BeauHive. All rights reserved.</p>
        <div class="mt-2 flex flex-col sm:flex-row justify-center gap-3 sm:gap-6 text-xs sm:text-sm">
          <Link href="/privacy" class="hover:text-[#e4299c]">Privacy Policy</Link>
          <Link href="/terms" class="hover:text-[#e4299c]">Terms of Service</Link>
          <Link href="/contact" class="hover:text-[#e4299c]">Contact</Link>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({ user: Object })

const showMenu = ref(false)
const showMobileMenu = ref(false)
const dropdown = ref(null)

const toggleMenu = () => { showMenu.value = !showMenu.value }
const logout = () => { router.post(route('logout')) }

const handleClickOutside = (event) => {
  if (showMenu.value && dropdown.value && !dropdown.value.contains(event.target)) {
    showMenu.value = false
  }
}
const handleEscapeKey = (event) => {
  if (event.key === 'Escape') {
    showMenu.value = false
    showMobileMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleEscapeKey)
})
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleEscapeKey)
})
</script>
