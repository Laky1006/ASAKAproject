<template>
  <div
    :id="`review-${review.id}`" 
    class="relative border rounded-2xl p-5 bg-white shadow-sm hover:shadow-md transition-all duration-200"
  >
    <!-- Header: avatar + name + report button + delete -->
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-3">
        <!-- Avatar -->
        <div
          class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-sm font-bold text-gray-700"
        >
          {{ (review.reguser?.name || 'U').charAt(0) }}
        </div>

        <!-- Name + report button -->
        <div class="flex flex-col">
          <div class="flex items-center gap-2">
            <div class="font-semibold text-base text-gray-900">
              {{ review.reguser?.name || 'Unknown' }}
            </div>
            <ReportButton
              v-if="auth?.user"
              :review-id="review.id"
              class="inline-block"
            />
          </div>
          <div class="text-xs text-gray-500">{{ formattedDate }}</div>
        </div>
      </div>

      <!-- Delete button (same style as before, aligned with name) -->
      <div v-if="canDelete">
        <button
          @click="$emit('delete', review.id)"
          type="button"
          class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-full hover:bg-red-50 hover:border-red-300 transition-colors duration-200"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-3.5 w-3.5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m2 0v13a1 1 0 01-1 1H7a1 1 0 01-1-1V7z"
            />
          </svg>
          Delete
        </button>
      </div>
    </div>

    <!-- Rating -->
    <div class="mb-2 flex items-center">
      <div>
        <span
          v-for="n in 5"
          :key="n"
          class="text-lg"
          :class="n <= (review.rating || 0) ? 'text-yellow-400' : 'text-gray-300'"
        >★</span>
      </div>
      <span class="ml-2 text-sm text-gray-700">({{ review.rating || 0 }}/5)</span>
    </div>

    <!-- Comment -->
    <p
      v-if="review.comment"
      class="text-gray-700 text-sm leading-relaxed whitespace-pre-line"
    >
      {{ review.comment }}
    </p>
  </div>
</template>



<script setup>
import { computed } from 'vue'
import ReportButton from '@/Components/ReportButton.vue'

const props = defineProps({
  review: { type: Object, required: true },
  auth: { type: Object, default: () => ({}) },
  dateFormat: { type: [String, Object], default: 'locale' },
  canDeleteOverride: { type: [Boolean, null], default: null },
})

defineEmits(['delete'])

// show delete if admin OR the author of the review
const canDelete = computed(() => {
  // If override is set, respect it
  if (props.canDeleteOverride !== null) return !!props.canDeleteOverride

  const currentUser = props.auth?.user
  const reviewOwnerId = props.review?.reguser_id

  // Admins can delete any review
  if (currentUser?.role === 'admin') return true

  // Regular user can delete only their own reviews
  if (currentUser?.role === 'reguser' && currentUser?.reguser?.id === reviewOwnerId) return true

  return false
})

const formattedDate = computed(() => {
  const raw = props.review?.created_at
  if (!raw) return ''

  // If backend sent “20 minutes ago”, just show it.
  if (typeof raw === 'string' && /\bago\b|just now|yesterday|in \d/.test(raw)) {
    return raw
  }

  // otherwise try to parse/format (ISO, timestamps, etc.)
  const d = new Date(raw)
  return Number.isNaN(d.getTime()) ? '' : d.toLocaleString()
})
</script>
