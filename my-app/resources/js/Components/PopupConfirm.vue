<template>
  <Transition name="fade">
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4"
    >
      <div class="backdrop-blur-xl bg-white/90 rounded-2xl p-6 w-full max-w-sm shadow-2xl border border-white/60">
        <h2 class="text-xl font-bold mb-3 text-[#2D1810]">{{ title }}</h2>
        <p class="text-[#6b5b73] mb-6 font-body leading-relaxed">{{ message }}</p>

        <div class="flex flex-col sm:flex-row justify-end gap-3">
          <button
            @click="$emit('update:show', false)"
            class="px-5 py-2 rounded-xl backdrop-blur-sm bg-white/70 border border-white/60 hover:bg-white/90 transition-all duration-200 font-semibold text-sm text-[#2D1810]"
          >
            Cancel
          </button>
          <button
            @click="confirmAction"
            class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white hover:shadow-lg transition-all duration-200 font-semibold text-sm"
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
