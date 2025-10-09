<template>
  <MainLayout :user="auth.user">
    <section class="max-w-4xl mx-auto py-10 px-6">
      <h1 class="text-2xl font-bold mb-6">My Booked Services</h1>

      <div v-if="items.length">
        <transition-group name="fade" tag="ul" class="space-y-4">
          <li
            v-for="service in items"
            :key="compositeId(service)"
            v-show="!cancelingIds.includes(compositeId(service))"
            class="border border-gray-200 rounded p-4 bg-white shadow-sm flex items-center justify-between gap-4"
          >
            <!-- Left: Clickable area goes to show page -->
            <Link
              :href="route('services.show', service.id)"
              class="flex items-center gap-4 flex-1 cursor-pointer hover:bg-gray-50 rounded-md transition p-1 -m-1"
            >
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
            </Link>

            <!-- Right: Cancel Button (stops navigation) -->
            <div>
              <button
                @click.stop="requestCancel(service)"
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

    <!-- Reusable confirm modal -->
    <PopupConfirm
      v-model:show="showConfirm"
      :title="confirmTitle"
      :message="confirmMessage"
      @confirm="handleConfirm"
    />
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3' 
import MainLayout from '@/Layouts/MainLayout.vue'
import PopupConfirm from '@/Components/PopupConfirm.vue'

const props = defineProps({
  auth: Object,
  services: Array,
})

// Local, mutable copy of services for UI updates
const items = ref([...(props.services ?? [])])

// Helpers
const compositeId = (s) => `${s.id}|${s.date}|${s.time}`

// UI state
const cancelingIds = ref([])

// Confirm modal state
const showConfirm = ref(false)
const confirmTitle = ref('')
const confirmMessage = ref('')
let confirmHandler = null

function askConfirm({ title = 'Please Confirm', message = '', onConfirm }) {
  confirmTitle.value = title
  confirmMessage.value = message
  confirmHandler = typeof onConfirm === 'function' ? onConfirm : null
  showConfirm.value = true
}

function handleConfirm() {
  showConfirm.value = false
  if (confirmHandler) confirmHandler()
  confirmHandler = null
}

// Cancel flow
function requestCancel(service) {
  askConfirm({
    title: 'Cancel Booking',
    message: `Are you sure you want to cancel your booking for "${service.title}" on ${service.date} at ${service.time}?`,
    onConfirm: () => performCancel(service),
  })
}

function performCancel(service) {
  const id = compositeId(service)
  if (!cancelingIds.value.includes(id)) cancelingIds.value.push(id)

  router.post(
    route('services.cancel'),
    {
      service_id: service.id,
      date: service.date,
      time: service.time,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        // Remove from list with a slight delay to allow fade transition
        setTimeout(() => {
          items.value = items.value.filter((l) => compositeId(l) !== id)
          cancelingIds.value = cancelingIds.value.filter((x) => x !== id)
        }, 300)
      },
      onError: () => {
        // Undo canceling flag if server failed
        cancelingIds.value = cancelingIds.value.filter((x) => x !== id)
      },
    }
  )
}
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
