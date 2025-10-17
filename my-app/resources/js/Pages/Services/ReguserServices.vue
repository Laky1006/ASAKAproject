<template>
  <MainLayout :user="auth.user">
    <section class="max-w-4xl mx-auto py-10 px-6">
      <h1 class="text-3xl font-bold mb-8 text-[#2D1810]">My Booked Services</h1>

      <div v-if="items.length">
        <transition-group name="fade" tag="ul" class="space-y-4">
          <li
            v-for="service in items"
            :key="compositeId(service)"
            v-show="!cancelingIds.includes(compositeId(service))"
            class="backdrop-blur-lg bg-white/80 border border-white/60 rounded-2xl p-5 shadow-xl flex items-center justify-between gap-4 hover:shadow-2xl transition-all duration-300"
          >
            <!-- Left: Clickable area goes to show page -->
            <Link
              :href="route('services.show', service.id)"
              class="flex items-center gap-4 flex-1 cursor-pointer hover:bg-white/40 rounded-xl transition-all duration-200 p-2 -m-2"
            >
              <div class="relative overflow-hidden rounded-xl flex-shrink-0 shadow-md">
                <img
                  :src="service.banner ? `/storage/${service.banner}` : '/images/default-banner.jpg'"
                  alt="Service banner"
                  class="w-24 h-24 object-cover"
                />
                <div class="absolute inset-0 bg-gradient-to-br from-[#e4299c]/20 to-[#e8662c]/20 mix-blend-multiply"></div>
              </div>
              <div>
                <div class="text-xl font-bold text-[#2D1810] mb-1">
                  {{ service.title }}
                </div>
                <div class="text-sm text-[#6b5b73] mb-2 font-body">
                  With: <span class="font-semibold text-[#2D1810]">{{ service.provider }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm text-[#6b5b73] font-body">
                  <span class="inline-flex items-center gap-1">
                    📅 {{ service.date }}
                  </span>
                  <span class="inline-flex items-center gap-1">
                    🕒 {{ service.time }}
                  </span>
                </div>
              </div>
            </Link>

            <!-- Right: Cancel Button (stops navigation) -->
            <div>
              <button
                @click.stop="requestCancel(service)"
                class="bg-gradient-to-r from-red-500 to-red-600 text-white px-5 py-2.5 text-sm rounded-xl hover:shadow-lg transition-all duration-200 font-semibold"
              >
                Cancel
              </button>
            </div>
          </li>
        </transition-group>
      </div>

      <div v-else class="backdrop-blur-lg bg-white/80 border border-white/60 rounded-2xl p-8 text-center shadow-xl">
        <div class="text-[#6b5b73] text-lg font-body">
          You haven't booked any services yet.
        </div>
      </div>
    </section>

    <!-- Reusable confirm modal -->
    <PopupConfirm
      v-model:show="showConfirm"
      :title="confirmTitle"
      :message="confirmMessage"
      @confirm="handleConfirm"
    />

    <!-- Policy Modal -->
    <div v-if="showPolicyModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900">{{ policyModalTitle }}</h3>
        </div>
        
        <p class="text-gray-700 mb-6 leading-relaxed">{{ policyModalMessage }}</p>
        
        <div class="flex gap-3 justify-end">
          <button
            @click="handlePolicyCancel"
            class="px-4 py-2 text-gray-600 hover:text-gray-800 font-semibold transition-colors"
          >
            Close
          </button>
          <button
            v-if="policyCanProceed"
            @click="handlePolicyConfirm"
            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold transition-colors"
          >
            Proceed with Cancellation
          </button>
        </div>
      </div>
    </div>
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

// Policy modal state
const showPolicyModal = ref(false)
const policyModalTitle = ref('')
const policyModalMessage = ref('')
const policyServiceToCancel = ref(null)
const policyCanProceed = ref(true)


function getHoursUntilAppointment(service) {
  const now = new Date()
  const appointmentDateTime = new Date(`${service.date} ${service.time}`)
  const diffMs = appointmentDateTime - now
  return diffMs / (1000 * 60 * 60) // Convert to hours
}

function checkCancellationPolicy(service) {
  const hoursLeft = getHoursUntilAppointment(service)
  
  if (hoursLeft < 12) {
    policyModalTitle.value = 'Cancellation Not Allowed'
    policyModalMessage.value = `You cannot cancel this booking as there are less than 12 hours remaining until your appointment. You will be charged the full booking amount.`
    policyCanProceed.value = false
  } else if (hoursLeft < 24) {
    policyModalTitle.value = 'Cancellation Fee Applies'
    policyModalMessage.value = `You can cancel this booking, but since there are less than 24 hours remaining, you will be charged a cancellation fee of 20% of the booking cost.`
    policyCanProceed.value = true
  } else {
    return null
  }
  
  policyServiceToCancel.value = service
  return true
}


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

function handlePolicyConfirm() {
  showPolicyModal.value = false
  
  if (policyCanProceed.value) {
    performCancel(policyServiceToCancel.value)
  }
  
  policyServiceToCancel.value = null
}


function handlePolicyCancel() {
  showPolicyModal.value = false
  policyServiceToCancel.value = null
}


function requestCancel(service) {
  const needsPolicyModal = checkCancellationPolicy(service)
  
  if (needsPolicyModal) {
    // Show policy modal first
    showPolicyModal.value = true
  } else {
    // Proceed directly with normal confirmation
    askConfirm({
      title: 'Cancel Booking',
      message: `Are you sure you want to cancel your booking for "${service.title}" on ${service.date} at ${service.time}?`,
      onConfirm: () => performCancel(service, false), // Pass false for regular cancel
    })
  }
}



function performCancel(service, isPolicyCancel = false) {
  const id = compositeId(service)
  if (!cancelingIds.value.includes(id)) cancelingIds.value.push(id)

  router.post(
    route('services.cancel'),
    {
      service_id: service.id,
      date: service.date,
      time: service.time,
      policy_cancellation: isPolicyCancel,
      hours_remaining: isPolicyCancel ? getHoursUntilAppointment(service) : null,
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
