<template>
  <MainLayout :user="auth.user">
    <section class="max-w-4xl mx-auto py-10 px-6">
      <h1 class="text-2xl font-bold mb-6">My Booked Services</h1>

      <div v-if="services.length">
        <transition-group name="fade" tag="ul" class="space-y-4">
          <li
            v-for="service in services"
            :key="service.id + service.date + service.time"
            v-show="!cancelingServiceIds.includes(service.id + service.date + service.time)"
            class="border border-gray-200 rounded p-4 bg-white shadow-sm flex items-center justify-between gap-4"
          >
            <!-- Left: Banner + Info -->
            <div class="flex items-center gap-4">
              <img
                :src="service.banner ? `/storage/${service.banner}` : '/images/default-banner.jpg'"
                alt="Service banner"
                class="w-24 h-24 object-cover rounded-md flex-shrink-0"
              />
              <div>
                <div class="text-xl font-semibold text-gray-800">
                  {{ service.title }}
                </div>
                <div class="text-sm text-gray-600 mt-1">
                  With: {{ service.provider }}
                </div>
                <div class="mt-2 text-gray-700">
                  📅 {{ service.date }} &nbsp;&nbsp; 🕒 {{ service.time }}
                </div>
              </div>
            </div>

            <!-- Right: Cancel Button -->
            <div>
              <button
                @click="cancelBooking(service)"
                class="bg-red-600 text-white px-5 py-2 text-base rounded hover:bg-red-700 transition"
              >
                Cancel
              </button>
            </div>
          </li>
        </transition-group>
      </div>

      <div v-else class="text-gray-500">
        You haven't booked any services yet.
      </div>
    </section>
  </MainLayout>
</template>

<script>
import MainLayout from '@/Layouts/MainLayout.vue';

export default {
  props: {
    auth: Object,
    services: Array,
  },
  components: {
    MainLayout,
  },
  data() {
    return {
      cancelingServiceIds: [],
    };
  },
  methods: {
    cancelBooking(service) {
      const confirmed = confirm(
        `Are you sure you want to cancel your booking for "${service.title}" on ${service.date} at ${service.time}?`
      );

      if (!confirmed) return;

      this.cancelingServiceIds.push(service.id + service.date + service.time);

      this.$inertia.post(
        route('services.cancel'),
        {
          service_id: service.id,
          date: service.date,
          time: service.time,
        },
        {
          preserveScroll: true,
          onSuccess: () => {
            setTimeout(() => {
              this.services = this.services.filter(
                l => l.id + l.date + l.time !== service.id + service.date + service.time
              );
              this.cancelingServiceIds = this.cancelingServiceIds.filter(
                id => id !== service.id + service.date + service.time
              );
            }, 300);
          },
        }
      );
    },
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-leave-to {
  opacity: 0;
}
</style>
