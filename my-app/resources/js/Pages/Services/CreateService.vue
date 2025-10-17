<template>
  <MainLayout :user="auth.user">
    <section class="py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto space-y-6">

        <!-- Service Creation Card -->
        <div class="backdrop-blur-lg bg-white/80 p-6 sm:p-8 shadow-xl border border-white/60 rounded-2xl">
          <h1 class="text-3xl font-bold mb-8 text-[#2D1810]">Create a New Service</h1>

          <form @submit.prevent="submitForm" @keydown.enter.prevent class="space-y-6">
            <!-- Title -->
            <div>
              <InputLabel for="title" value="Service Title" />
              <TextInput v-model="form.title" id="title" class="w-full bg-[#fefdfb] border-gray-300 focus:border-[#e4299c] focus:ring-[#e4299c] px-5 py-3.5" placeholder="e.g. Manicure & Nail Art" />
              <p v-if="form.errors.title" class="text-red-600 text-sm mt-2 font-semibold">{{ form.errors.title }}</p>
            </div>

            <!-- Description -->
            <div>
              <InputLabel for="description" value="Description" />
              <textarea
                v-model="form.description"
                id="description"
                rows="4"
                class="w-full border border-gray-300 rounded-xl px-5 py-3.5 bg-[#fefdfb] text-[#2D1810] placeholder-[#6b5b73] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] outline-none transition-all font-body"
                placeholder="Write a short description..."
              ></textarea>
              <p v-if="form.errors.description" class="text-red-600 text-sm mt-2 font-semibold">{{ form.errors.description }}</p>
            </div>

            <!-- Banner Image -->
            <div>
              <InputLabel for="banner" value="Service Banner Image" />
              <input 
                type="file" 
                @change="handleBannerChange" 
                class="w-full mt-2 bg-[#fefdfb] border border-gray-300 rounded-xl px-5 py-3.5 text-sm text-[#2D1810] file:mr-4 file:py-2.5 file:px-5 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gradient-to-r file:from-[#e4299c] file:to-[#ff6b9d] file:text-white hover:file:shadow-lg file:transition-all file:duration-200 cursor-pointer focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] outline-none transition-all" 
                accept="image/*" 
              />

              <!-- Preview -->
              <div v-if="bannerPreview" class="mt-4">
                <p class="text-sm text-[#6b5b73] mb-2 font-semibold font-body">Image Preview:</p>
                <div class="relative w-full sm:w-1/2 rounded-xl border border-white/60 overflow-hidden shadow-lg">
                  <img
                    :src="bannerPreview"
                    alt="Banner Preview"
                    class="w-full max-h-64 object-cover"
                  />
                  <div class="absolute inset-0 bg-gradient-to-br from-[#e4299c]/10 to-[#e8662c]/10 mix-blend-multiply"></div>
                </div>
              </div>
            </div>

            <!-- Contact Info -->
            <div>
              <InputLabel for="phone" value="Contact Phone Number" />
              <TextInput v-model="form.phone" id="phone" class="w-full bg-[#fefdfb] border-gray-300 focus:border-[#e4299c] focus:ring-[#e4299c] px-5 py-3.5" placeholder="+1234567890" />
              <p v-if="form.errors.phone" class="text-red-600 text-sm mt-2 font-semibold">{{ form.errors.phone }}</p>
            </div>

            <!-- Price -->
            <div>
              <InputLabel for="price" value="Price (optional)" />
              <TextInput
                v-model="form.price"
                id="price"
                class="w-full bg-[#fefdfb] border-gray-300 focus:border-[#e4299c] focus:ring-[#e4299c] px-5 py-3.5"
                type="number"
                step="0.01"
                min="0"
                placeholder="e.g. 29.99"
              />
              <p v-if="form.errors.price" class="text-red-600 text-sm mt-2 font-semibold">{{ form.errors.price }}</p>
            </div>

            <!-- Labels -->
            <div>
              <InputLabel value="Labels (max 10)" />

              <!-- Suggestions -->
              <div class="flex flex-wrap gap-2 mb-3">
                <button
                  v-for="suggestion in labelSuggestions"
                  :key="suggestion"
                  type="button"
                  class="px-4 py-2 bg-[#f5f3f0] border border-gray-300 rounded-full hover:bg-[#fce7f3] hover:border-[#e4299c] text-sm font-semibold text-[#2D1810] transition-all duration-200"
                  @click="addLabel(suggestion)"
                >
                  {{ suggestion }}
                </button>
              </div>

              <!-- Input field for new label -->
              <input
                v-model="newLabel"
                @keyup.enter.prevent="addLabel(newLabel)"
                type="text"
                class="border border-gray-300 px-5 py-3.5 rounded-xl w-full bg-[#fefdfb] text-[#2D1810] placeholder-[#6b5b73] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] outline-none transition-all font-body"
                placeholder="Type a new label and press Enter or Add"
                :disabled="form.labels.length >= 10"
              />

              <!-- Add button -->
              <button
                type="button"
                class="mt-3 bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white px-6 py-3 rounded-xl text-sm font-semibold hover:shadow-lg transition-all duration-200 disabled:opacity-50"
                @click="addLabel(newLabel)"
                :disabled="form.labels.length >= 10"
              >
                Add Label
              </button>

              <!-- Label bubbles -->
              <div class="flex flex-wrap gap-2 mt-4">
                <span
                  v-for="(label, index) in form.labels"
                  :key="index"
                  class="bg-[#fce7f3] text-[#e4299c] border border-[#e4299c]/40 px-4 py-2 rounded-full text-sm font-semibold flex items-center gap-2"
                >
                  {{ label }}
                  <button
                    type="button"
                    class="text-red-600 hover:text-red-700 font-bold text-base"
                    @click="removeLabel(index)"
                  >
                    ×
                  </button>
                </span>
              </div>

              <p v-if="form.labels.length >= 10" class="text-red-600 text-sm mt-2 font-semibold">Maximum 10 labels allowed.</p>
            </div>

            <!-- Available Slots -->
            <div class="bg-[#f9f8f6] p-6 sm:p-8 shadow-lg border border-gray-300 rounded-xl">
              <h2 class="text-xl font-bold mb-4 text-[#2D1810]">Available Service Dates & Times</h2>

              <!-- DateTimePicker with double-click to type time -->
              <DateTimePicker
                :weekStartsOn="1"
                :step="60"
                :slots="form.available_slots" 
                v-model:slots="form.available_slots"
                variant="provider"
              />
            </div>

            <div v-if="Object.keys(form.errors).length" class="bg-red-50 border border-red-400 text-red-700 p-4 rounded-xl font-semibold">
              Please fix the errors above before submitting.
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
              <PrimaryButton type="submit" class="w-full sm:w-auto">Create Service</PrimaryButton>
            </div>
          </form>
        </div>

      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import { useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import InputLabel from '@/Components/basics/InputLabel.vue'
import TextInput from '@/Components/basics/TextInput.vue'
import PrimaryButton from '@/Components/basics/PrimaryButton.vue'
import DateTimePicker from '@/Components/DateTimePicker.vue'

defineProps({ auth: Object })

const labelSuggestions = [
  'Nails', 'Hair', 'Skincare', 'Lashes', 'Body', 'Makeup', 'Wellness',
]

// NEW: missing refs
const newLabel = ref('')
const bannerPreview = ref(null)
let lastObjectUrl = null

const form = useForm({
  title: '',
  description: '',
  phone: '',
  banner: null,
  available_slots: [],
  labels: [],
  price: '',
})

function handleBannerChange(e) {
  const file = e.target.files?.[0]
  if (!file) return
  form.banner = file
  if (lastObjectUrl) URL.revokeObjectURL(lastObjectUrl)
  lastObjectUrl = URL.createObjectURL(file)
  bannerPreview.value = lastObjectUrl
}

onBeforeUnmount(() => {
  if (lastObjectUrl) URL.revokeObjectURL(lastObjectUrl)
})

function addLabel(label) {
  const trimmed = (label || '').trim()
  if (trimmed && !form.labels.includes(trimmed) && form.labels.length < 10) {
    form.labels.push(trimmed)
    newLabel.value = ''
  }
}

function removeLabel(index) {
  form.labels.splice(index, 1)
}

function submitForm() {
  form.post(route('services.store'), {
    forceFormData: true,
    onSuccess: () => {
      form.reset()
      bannerPreview.value = null
      if (lastObjectUrl) { URL.revokeObjectURL(lastObjectUrl); lastObjectUrl = null }
      window.Inertia?.visit?.('/my-services') || (location.href = '/my-services')
    },
  })
}
</script>
