<template>
  <div ref="layoutRoot" class="flex flex-col min-h-svh bg-[#f8f6f4]">

    <!-- Mobile Sidebar Overlay -->
    <div 
      v-if="showMobileMenu" 
      @click="showMobileMenu = false"
      class="fixed inset-0 bg-black/20 z-40 sm:hidden"
    ></div>

    <!-- Mobile Sidebar: FIXED and stays visible while scrolling -->
    <nav 
      :class="[
        'fixed left-0 w-80 max-w-[85vw] bg-white shadow-xl transform transition-transform duration-300 ease-in-out z-50 sm:hidden overflow-y-auto',
        showMobileMenu ? 'translate-x-0' : '-translate-x-full',
        'top-[72px] h-[calc(100vh-72px)]'
      ]"
    >
      <div class="flex items-center justify-between p-6 border-b border-[#e8e5e2]">
        <div class="flex items-center gap-3">
          <img 
            src="/images/B (1).png" 
            class="h-12 w-12 object-contain"
          />
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

      <div class="px-6 py-4 space-y-2 font-baron">
        <button
          v-if="user?.role === 'admin'"
          @click="goAdmin"
          class="block w-full text-left py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg"
        >
          Admin
        </button>
        <a 
          href="/" 
          @click="showMobileMenu = false"
          class="block py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg"
        >
          Home
        </a>
        <a 
          :href="route('about')" 
          @click="showMobileMenu = false"
          class="block py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg"
        >
          About
        </a>

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
          
          <Link 
            :href="route('profile.edit')" 
            @click="showMobileMenu = false"
            class="block py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg"
          >
            Profile
          </Link>
          <Link 
            :href="route('my-services')" 
            @click="showMobileMenu = false"
            class="block py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg"
          >
            My services
          </Link>
          <Link 
            :href="route('notifications.index')" 
            @click="showMobileMenu = false"
            class="block py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg"
          >
            Notifications
          </Link>

          <form @submit.prevent="logout" class="block">
            <button 
              type="submit" 
              class="w-full text-left py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg"
            >
              Log Out
            </button>
          </form>
        </div>

        <!-- Mobile Login -->
        <div v-else class="border-t border-[#e8e5e2] pt-4 mt-4">
          <a 
            href="/login" 
            @click="showMobileMenu = false"
            class="block py-3 px-4 text-[#6b5b73] hover:text-[#e4299c] hover:bg-[#f8f6f4] transition-colors rounded-lg"
          >
            Log In
          </a>
        </div>
      </div>
    </nav>

    <!-- STICKY Header - stays at top when scrolling -->
    <header class="sticky top-0 z-50 px-4 py-3 sm:px-6 sm:py-4 flex justify-between items-center bg-white shadow-sm border-b border-[#e8e5e2]">      
      <!-- Logo Section -->
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
      <nav class="hidden sm:flex items-center gap-8 lg:gap-10 text-base lg:text-lg text-[#6b5b73] font-baron">
        <button
          v-if="user?.role === 'admin'"
          @click="goAdmin"
          class="text-[#6b5b73] hover:text-[#e4299c] hover:underline bg-transparent px-0 py-0 transition-colors duration-200"
        >
          Admin
        </button>
        <a href="/" class="hover:text-[#e4299c] transition-colors duration-200">Home</a>
        <a :href="route('about')" class="hover:text-[#e4299c] transition-colors duration-200">About</a>

        <!-- Desktop User Menu -->
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
          <div
            v-if="showMenu"
            class="absolute right-0 mt-2 w-44 bg-white text-[#6b5b73] shadow-xl rounded-lg overflow-hidden z-50 border border-[#e8e5e2]"
          >
            <Link :href="route('profile.edit')" class="block px-4 py-3 hover:bg-[#f8f6f4] hover:text-[#e4299c] transition-colors">Profile</Link>
            <Link :href="route('my-services')" class="block px-4 py-3 hover:bg-[#f8f6f4] hover:text-[#e4299c] transition-colors">My services</Link>
            <Link :href="route('notifications.index')" class="block px-4 py-3 hover:bg-[#f8f6f4] hover:text-[#e4299c] transition-colors">Notifications</Link>

            <form @submit.prevent="logout" class="block border-t border-[#e8e5e2]">
              <button type="submit" class="w-full text-left px-4 py-3 hover:bg-[#f8f6f4] hover:text-[#e4299c] transition-colors">Log Out</button>
            </form>
          </div>
        </div>

        <!-- Desktop Login -->
        <div v-else>
          <a href="/login" class="hover:text-[#e4299c] transition-colors duration-200">Log In</a>
        </div>
      </nav>
    </header>

    <!-- Page Content -->
    <main class="grow">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-white text-[#6b5b73] text-center py-8 mt-8 border-t border-[#e8e5e2] font-baron">
      <div class="max-w-6xl mx-auto px-4">
        <p class="font-medium text-sm sm:text-base">© 2025 BeauHive. All rights reserved.</p>
        <div class="mt-3 flex flex-col sm:flex-row justify-center gap-4 sm:gap-8 text-xs sm:text-sm">
          <a href="/privacy" class="hover:text-[#e4299c] transition-colors">Privacy Policy</a>
          <a href="/terms" class="hover:text-[#e4299c] transition-colors">Terms of Service</a>
          <a href="/contact" class="hover:text-[#e4299c] transition-colors">Contact</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
import { Link, router } from '@inertiajs/vue3'

export default {
  props: { user: Object },
  components: { Link },
  data() {
    return { 
      showMenu: false,
      showMobileMenu: false
    }
  },
  methods: {
    toggleMenu() {
      this.showMenu = !this.showMenu
    },
    logout() {
      router.post('/logout')
      this.showMobileMenu = false
    },
    handleClickOutside(event) {
      if (this.showMenu && this.$refs.dropdown && !this.$refs.dropdown.contains(event.target)) {
        this.showMenu = false
      }
    },
    handleEscapeKey(event) {
      if (event.key === 'Escape') {
        if (this.showMenu) this.showMenu = false
        if (this.showMobileMenu) this.showMobileMenu = false
      }
    },
    goAdmin() {
      router.get('/admin')
      this.showMobileMenu = false
    },
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside)
    document.addEventListener('keydown', this.handleEscapeKey)
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside)
    document.removeEventListener('keydown', this.handleEscapeKey)
  },
}
</script>
