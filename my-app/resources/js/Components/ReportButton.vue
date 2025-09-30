<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'

const props = defineProps({
  reviewId: Number,
  serviceId: Number
})

const open = ref(false)
const reason = ref('')
const errorMessage = ref('')
const successMessage = ref('')

const reasons = [
  'Spam or misleading',
  'Offensive or abusive content',
  'Hate speech or harassment',
  'Inappropriate language',
  'Other'
]

function submitReport() {
  errorMessage.value = ''
  successMessage.value = ''

  if (!reason.value) {
    errorMessage.value = 'Please select a reason.'
    return
  }

  axios.post('/reports', {
    review_id: props.reviewId || null,
    service_id: props.serviceId || null,
    reason: reason.value
  })
  .then(() => {
    successMessage.value = 'This review has been reported.'
    reason.value = ''
    open.value = false

    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  })
  .catch(error => {
    if (error.response?.data?.errors) {
      errorMessage.value = Object.values(error.response.data.errors).join(' ')
    } else if (error.response?.data?.error) {
      errorMessage.value = error.response.data.error
    } else {
      errorMessage.value = 'An error occurred while submitting the report.'
    }
  })
}

// close dropdown when clicking outside
function handleClickOutside(e) {
  if (open.value && !e.target.closest('.report-button')) {
    open.value = false
  }
}
onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
</script>

<template>
  <div class="report-button inline-block relative">
    <!-- Icon -->
    <button
      @click.stop="open = !open"
      class="p-1 text-gray-500 hover:text-red-600"
    >
      <span class="material-symbols-outlined">exclamation</span>
    </button>

    <!-- Dropdown (absolute relative to button) -->
    <div
      v-if="open"
      class="absolute right-0 mt-2 w-56 bg-white border rounded shadow-lg z-50 p-2"
    >
      <select v-model="reason" class="w-full border rounded p-1 text-sm">
        <option disabled value="">-- Select a reason --</option>
        <option v-for="(r, i) in reasons" :key="i" :value="r">{{ r }}</option>
      </select>

      <button
        @click="submitReport"
        class="mt-2 w-full bg-red-500 text-white text-sm px-2 py-1 rounded hover:bg-red-600"
      >
        Report
      </button>

      <p v-if="errorMessage" class="text-red-500 text-xs mt-1">{{ errorMessage }}</p>
    </div>

    <!-- Toast message -->
    <div
      v-if="successMessage"
      class="fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow z-[9999]"
    >
      {{ successMessage }}
    </div>
  </div>
</template>
