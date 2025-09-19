<template>
  <div class="border rounded p-4 bg-white shadow-sm">
    <!-- Header: avatar + name + date -->
    <div class="flex items-center gap-3 mb-1">
      <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-sm font-bold">
        {{ (review.reguser?.name || 'U').charAt(0) }}
      </div>
      <div>
        <div class="font-medium text-base">{{ review.reguser?.name || 'Unknown' }}</div>
        <div class="text-xs text-gray-500">{{ formattedDate }}</div>
      </div>
    </div>

    <!-- Rating -->
    <div class="mb-1">
      <span
        v-for="n in 5"
        :key="n"
        class="text-xl"
        :class="n <= (review.rating || 0) ? 'text-yellow-500' : 'text-gray-300'"
      >★</span>
      <span class="ml-2 text-sm text-gray-700">({{ review.rating || 0 }}/5)</span>
    </div>

    <!-- Comment -->
    <p v-if="review.comment" class="text-gray-700 text-sm whitespace-pre-line">
      {{ review.comment }}
    </p>

    <!-- Delete button -->
    <div v-if="canDelete" class="text-right">
      <button
        @click="$emit('delete', review.id)"
        class="text-sm text-red-500 hover:text-red-700"
        type="button"
      >
        Delete
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  review: { type: Object, required: true },
  auth: { type: Object, default: () => ({}) },
  dateFormat: { type: [String, Object], default: 'locale' },
  canDeleteOverride: { type: [Boolean, null], default: null },
})
defineEmits(['delete'])

const canDelete = computed(() => {
  if (props.canDeleteOverride !== null) return !!props.canDeleteOverride
  const currentId = props.auth?.user?.reguser?.id
  return props.auth?.user?.role === 'reguser' && currentId === props.review?.reguser_id
})

const formattedDate = computed(() => {
  const raw = props.review?.created_at
  if (!raw) return ''
  const d = typeof raw === 'string' || typeof raw === 'number' ? new Date(raw) : raw

  if (props.dateFormat === 'iso') return d.toISOString().slice(0, 10)
  if (props.dateFormat === 'locale') return d.toLocaleString()
  try {
    return new Intl.DateTimeFormat(undefined, props.dateFormat).format(d)
  } catch {
    return d.toLocaleString()
  }
})
</script>
