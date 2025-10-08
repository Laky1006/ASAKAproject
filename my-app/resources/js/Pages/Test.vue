<!-- resources/js/Pages/Bookings/Create.vue -->
<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DateTimePicker from '@/Components/DateTimePicker.vue'

const when = ref(null)
// restrict to next 6 months
const min = new Date()
const max = new Date(min); max.setMonth(max.getMonth()+6)

function submit() {
  // if you want ISO specifically, set returnType="iso" on the component and skip conversion
  const payload = { date_time: when.value instanceof Date ? when.value.toISOString() : when.value }
  router.post(route('bookings.store'), payload)
}
</script>

<template>
  <div class="max-w-xl mx-auto p-6 space-y-4">
    <h1 class="text-2xl font-semibold">Create Booking</h1>

    <DateTimePicker
      v-model="when"
      :min="min"
      :max="max"
      :step-minutes="15"
      return-type="iso"
      placeholder="Select date & time"
    />

    <pre class="text-xs bg-gray-50 p-3 rounded border">{{ when }}</pre>

    <button
      class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700"
      @click="submit"
    >
      Save
    </button>
  </div>
</template>
