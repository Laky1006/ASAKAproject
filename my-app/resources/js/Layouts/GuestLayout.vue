<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="relative min-h-screen flex flex-col items-center justify-center font-body">
    
    <!-- Animated gradient blobs with background color -->
    <div class="fixed inset-0 -z-10 overflow-hidden bg-[#FFF8F0]">
      <div class="absolute top-0 -left-20 w-96 h-96 bg-gradient-to-br from-[#e4299c]/30 to-[#ff6b9d]/20 rounded-full blur-3xl"></div>
      <div class="absolute top-40 right-0 w-[32rem] h-[32rem] bg-gradient-to-br from-[#febd59]/40 to-[#ffbc59]/30 rounded-full blur-3xl"></div>
      <div class="absolute bottom-20 left-1/3 w-80 h-80 bg-gradient-to-br from-[#e8662c]/20 to-[#ff8c42]/20 rounded-full blur-3xl"></div>
    </div>

    <!-- BeauHive Logo -->
    <Link href="/" class="mb-8 hover:opacity-80 transition-opacity">
      <div class="flex flex-col font-display font-bold leading-tight text-center">
        <span class="text-5xl sm:text-6xl text-[#e4299c]">Beau</span>
        <span class="text-5xl sm:text-6xl text-[#e8662c]">Hive</span>
      </div>
    </Link>

    <!-- Glassmorphic Form Container -->
    <div class="w-full max-w-md px-6">
      <div class="backdrop-blur-xl bg-white/50 rounded-3xl shadow-2xl border border-white/70 px-8 py-10">
        <slot />
      </div>
    </div>

  </div>
</template>
