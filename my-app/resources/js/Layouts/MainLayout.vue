<template>
  <div class="flex flex-col min-h-svh bg-[#FFF8F0] font-body">

    <!-- Mobile Sidebar Overlay -->
    <div 
      v-if="showMobileMenu" 
      @click="showMobileMenu = false"
      class="fixed inset-0 bg-black/20 z-40 sm:hidden"
    ></div>

    <!-- Mobile Sidebar -->
    <nav
      :class="[
        'fixed left-0 w-80 max-w-[85vw] bg-white shadow-xl transform transition-transform duration-300 ease-in-out z-50 sm:hidden overflow-y-auto',
        showMobileMenu ? 'translate-x-0' : '-translate-x-full',
        'top-[72px] h-[calc(100vh-72px)]'
      ]"
    >
      <div class="flex items-center justify-between p-6 border-b border-[#e8e5e2]">
        <!-- Logo -->
        <div class="flex items-center gap-3">
          <img src="/images/B (1).png" class="h-12 w-12 object-contain" />
        </div>

        <button
          @click="showMobileMenu = false"
          class="p-2 text-[#6b5b73] hover:text-[#e4299c] transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div class="px-6 py-4 space-y-2 font-heading">
        <Link href="/" @click="showMobileMenu = false"
          class="block py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg">
          Home
        </Link>
        <Link :href="route('about')" @click="showMobileMenu = false"
          class="block py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg">
          About
        </Link>

        <!-- Mobile User Section -->
        <div v-if="user" class="border-t border-[#e8e5e2] pt-4 mt-4">
          <div class="flex items-center gap-3 py-3 px-4 mb-2">
            <img
              v-if="user.profile_photo"
              :src="`/storage/${user.profile_photo}`"
              class="w-10 h-10 rounded-full object-cover border-2 border-[#e4299c]"
            />
            <img
              v-else
              src="/images/default-profile.jpg"
              class="w-10 h-10 rounded-full object-cover border-2 border-[#e4299c]"
            />
            <span class="text-[#6b5b73] font-medium">{{ user.username }}</span>
          </div>

          <Link :href="route('profile.edit')" @click="showMobileMenu = false"
            class="block py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg">
            Profile
          </Link>

          <!-- MyServices switches to AdminPanel when loged in as Admin -->
          <Link v-if="user.role !== 'admin'" :href="route('my-services')" @click="showMobileMenu = false"
            class="block py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg">
            My Services
          </Link>

          <Link v-if="user.role === 'admin'" :href="route('admin-panel.dashboard')" @click="showMobileMenu = false"
            class="block py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg">
            Admin Panel
          </Link>

          <Link :href="route('notifications.index')" @click="showMobileMenu = false"
            class="block py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg">
            Notifications
          </Link>

          <form @submit.prevent="logout" class="block">
            <button type="submit"
              class="w-full text-left py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg">
              Log Out
            </button>
          </form>
        </div>

        <!-- Mobile Login -->
        <div v-else class="border-t border-[#e8e5e2] pt-4 mt-4">
          <Link href="/login" @click="showMobileMenu = false"
            class="block py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg">
            Log In
          </Link>
        </div>
      </div>
    </nav>

    <!-- HEADER with scroll effect -->
    <header 
      :class="[
        'sticky top-0 z-50 px-4 py-3 sm:px-6 sm:py-4 flex justify-between items-center transition-all duration-100',
        isScrolled 
          ? 'bg-white shadow-sm border-b border-[#e8e5e2]' 
          : 'bg-transparent'
      ]"
    >

      <!-- Logo -->
      <div class="flex items-center gap-4">
        <img 
          src="/images/B (1).png" 
          class="h-14 w-auto sm:h-16 lg:h-18 object-contain"
        />
      </div>

      <!-- Mobile Menu Button -->
      <button
        @click="showMobileMenu = true"
        class="sm:hidden flex items-center justify-center w-10 h-10 text-[#6b5b73] hover:text-[#e4299c] transition-colors"
        aria-label="Open navigation menu"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>

      <!-- Desktop Navigation -->
      <nav class="hidden sm:flex items-center gap-8 lg:gap-10 text-lg lg:text-xl text-[#6b5b73] font-heading">

        <!-- iii -->
        <Link href="/" class="inline-block hover:text-[#e4299c] hover:scale-110 transition-all duration-200">Home</Link>
        <Link :href="route('about')" class="inline-block hover:text-[#e4299c] hover:scale-110 transition-all duration-200">About</Link>

        <div v-if="user" class="relative" ref="dropdown">
          <button 
            @click="toggleMenu" 
            :aria-expanded="showMenu"
            class="flex items-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#e4299c] rounded-md hover:text-[#e4299c] transition-colors duration-200"
          >
            <span>{{ user.username }}</span>
            <img
              v-if="user.profile_photo"
              :src="`/storage/${user.profile_photo}`"
              class="w-10 h-10 lg:w-11 lg:h-11 rounded-full object-cover border-2 border-[#e4299c]"
            />
            <img
              v-else
              src="/images/default-profile.jpg"
              class="w-10 h-10 lg:w-11 lg:h-11 rounded-full object-cover border-2 border-[#e4299c]"
            />
          </button>

          <!-- Desktop Dropdown Menu -->
          <div v-if="showMenu"
            class="absolute right-0 mt-2 w-44 bg-white text-[#6b5b73] shadow-xl rounded-lg overflow-hidden z-50 border border-[#e8e5e2] font-body font-normal"
          >
            <Link :href="route('profile.edit')" class="block px-4 py-3 hover:bg-[#f8f6f4] hover:text-[#e4299c] transition-colors">Profile</Link>
            <Link v-if="user.role !== 'admin'" :href="route('my-services')" class="block px-4 py-3 hover:bg-[#f8f6f4] hover:text-[#e4299c] transition-colors">My Services</Link>
            <Link v-if="user.role === 'admin'" :href="route('admin-panel.dashboard')" class="block px-4 py-3 hover:bg-[#f8f6f4] hover:text-[#e4299c] transition-colors">Admin Panel</Link>
            <Link :href="route('notifications.index')" class="block px-4 py-3 hover:bg-[#f8f6f4] hover:text-[#e4299c] transition-colors">Notifications</Link>

            <form @submit.prevent="logout" class="border-t border-[#febd59]">
              <button type="submit" class="w-full text-left px-4 py-3 hover:bg-[#FFF8F0] hover:text-[#e4299c]">Log Out</button>
            </form>
          </div>
        </div>

        <!-- Desktop Login -->
        <div v-else>
          <Link href="/login" class="inline-block hover:text-[#e4299c] hover:scale-110 transition-all duration-200">Log In</Link>
        </div>
      </nav>
    </header>

    <!-- Page Content -->
    <main class="grow">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-white text-[#6b5b73] text-center py-8 mt-8 border-t border-[#e8e5e2] font-heading">
      <div class="max-w-6xl mx-auto px-4">
        <p class="font-semibold text-sm sm:text-base">© 2025 BeauHive. All rights reserved.</p>
        <div class="mt-3 flex flex-col sm:flex-row justify-center gap-4 sm:gap-8 text-xs sm:text-sm font-body">
          <a href="/privacy" class="hover:text-[#e4299c] transition-colors">Privacy Policy</a>
          <a href="/terms" class="hover:text-[#e4299c] transition-colors">Terms of Service</a>
          <a href="/contact" class="hover:text-[#e4299c] transition-colors">Contact</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Link, router } from '@inertiajs/vue3'
defineProps({ user: Object })

// state
const showMenu = ref(false)
const showMobileMenu = ref(false)
const isScrolled = ref(false)

// refs
const dropdown = ref(null)

const closeMenus = () => {
  showMenu.value = false
  showMobileMenu.value = false
}

// actions
const toggleMenu = () => { showMenu.value = !showMenu.value }

const logout = () => {
  const hasRoute = typeof route === 'function'
  router.post(hasRoute ? route('logout') : '/logout')
  showMobileMenu.value = false
}

// scroll handler
const handleScroll = () => {
  isScrolled.value = window.scrollY > 10
}

// listeners
const handleClickOutside = (event) => {
  if (showMenu.value && dropdown.value && !dropdown.value.contains(event.target)) {
    showMenu.value = false
  }
}

const handleEscapeKey = (event) => {
  if (event.key === 'Escape') closeMenus()
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleEscapeKey)
  window.addEventListener('scroll', handleScroll)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleEscapeKey)
  window.removeEventListener('scroll', handleScroll)
})
</script>
