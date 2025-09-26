<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  reviewId: Number,
  serviceId: Number // optional
})

const reason = ref('')
const showForm = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

function submitReport() {
  errorMessage.value = ''
  successMessage.value = ''

  console.log('Reporting reviewId:', props.reviewId, 'serviceId:', props.serviceId) // debug

  axios.post('/reports', {
    review_id: props.reviewId || null,
    service_id: props.serviceId || null,
    reason: reason.value,
  })
  .then(() => {
    successMessage.value = 'Report submitted successfully'
    reason.value = ''
    showForm.value = false
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
</script>

<template>
  <div>
    <button @click="showForm = !showForm">Report</button>
    <div v-if="showForm">
      <textarea v-model="reason" placeholder="Why are you reporting this?"></textarea>
      <button @click="submitReport">Submit Report</button>
      <p v-if="errorMessage" class="text-red-500">{{ errorMessage }}</p>
      <p v-if="successMessage" class="text-green-500">{{ successMessage }}</p>
    </div>
  </div>
</template>
