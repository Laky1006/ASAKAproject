<template>
  <Transition name="fade">
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    >
      <div class="bg-white rounded-2xl p-6 w-80 shadow-xl">
        <h2 class="text-lg font-semibold mb-3">{{ title }}</h2>
        <p class="text-gray-700 mb-6">{{ message }}</p>

        <div class="flex justify-end gap-3">
          <button
            @click="$emit('update:show', false)"
            class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300"
          >
            Cancel
          </button>
          <button
            @click="confirmAction"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
          >
            Confirm
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
const props = defineProps({
  show: Boolean,
  title: { type: String, default: 'Please Confirm' },
  message: String,
})
const emit = defineEmits(['confirm', 'update:show'])

function confirmAction() {
  emit('confirm')
  emit('update:show', false)
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
