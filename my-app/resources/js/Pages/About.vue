<template>
  <MainLayout :user="auth?.user">
    <section class="max-w-5xl mx-auto py-16 px-6 sm:px-8 lg:px-10">
      
      <!-- Hero Section -->
      <div class="text-center mb-16">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-[#e8662c] font-display mb-4">
          About BeauHive
        </h1>
        <p class="text-xl text-[#6b5b73] font-body max-w-3xl mx-auto">
          Your trusted beauty services marketplace connecting clients with talented providers
        </p>
      </div>

      <!-- Main Content Cards -->
      <div class="space-y-8">
        
        <!-- Mission Card -->
        <div class="backdrop-blur-xl bg-white/50 rounded-3xl shadow-2xl border border-white/70 p-8 sm:p-10">
          <h2 class="text-3xl font-bold text-[#2D1810] font-display mb-4">Our Mission</h2>
          <p class="text-lg text-[#6b5b73] font-body leading-relaxed">
            BeauHive is a platform that connects clients with talented beauty service providers offering personalized treatments. 
            Whether it's nails, hair, skincare, lashes, body treatments, makeup, or wellness services - you can find the perfect provider here. 
            We believe everyone deserves access to quality beauty services in a trusted, transparent marketplace.
          </p>
        </div>

        <!-- What We Offer -->
        <div class="backdrop-blur-xl bg-white/50 rounded-3xl shadow-2xl border border-white/70 p-8 sm:p-10">
          <h2 class="text-3xl font-bold text-[#2D1810] font-display mb-6">What We Offer</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="space-y-4">
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] flex items-center justify-center flex-shrink-0">
                  <span class="text-white font-bold">✓</span>
                </div>
                <div>
                  <h3 class="font-bold text-[#2D1810] font-heading">Verified Providers</h3>
                  <p class="text-sm text-[#6b5b73] font-body">Trusted beauty professionals with ratings and reviews</p>
                </div>
              </div>
              
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] flex items-center justify-center flex-shrink-0">
                  <span class="text-white font-bold">✓</span>
                </div>
                <div>
                  <h3 class="font-bold text-[#2D1810] font-heading">Wide Range of Services</h3>
                  <p class="text-sm text-[#6b5b73] font-body">From nails to wellness, find all beauty services in one place</p>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] flex items-center justify-center flex-shrink-0">
                  <span class="text-white font-bold">✓</span>
                </div>
                <div>
                  <h3 class="font-bold text-[#2D1810] font-heading">Easy Booking</h3>
                  <p class="text-sm text-[#6b5b73] font-body">Simple, seamless booking process for all services</p>
                </div>
              </div>
              
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] flex items-center justify-center flex-shrink-0">
                  <span class="text-white font-bold">✓</span>
                </div>
                <div>
                  <h3 class="font-bold text-[#2D1810] font-heading">Transparent Reviews</h3>
                  <p class="text-sm text-[#6b5b73] font-body">Real client feedback to help you make informed choices</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- For Providers -->
        <div class="backdrop-blur-xl bg-white/50 rounded-3xl shadow-2xl border border-white/70 p-8 sm:p-10">
          <h2 class="text-3xl font-bold text-[#2D1810] font-display mb-4">For Beauty Providers</h2>
          <p class="text-lg text-[#6b5b73] font-body leading-relaxed mb-6">
            Are you a beauty professional? Join BeauHive to showcase your services, connect with new clients, and grow your business. 
            Our platform makes it easy to manage bookings, build your reputation, and reach customers looking for exactly what you offer.
          </p>
          <a href="/register" class="inline-block px-8 py-4 rounded-xl bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white font-bold text-base shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200 font-heading">
            Become a Provider
          </a>
        </div>

      </div>

    </section>
  </MainLayout>
</template>

<script>
import MainLayout from '@/Layouts/MainLayout.vue';

export default {
  props: {
    auth: Object,
  },
  components: {
    MainLayout,
  },
};
</script>
