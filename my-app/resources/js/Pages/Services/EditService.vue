<template>
  <MainLayout :user="auth.user">
    <section class="py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto space-y-6">

        <!-- Edit Service Form Card -->
        <div class="backdrop-blur-lg bg-white/80 p-6 sm:p-8 shadow-xl border border-white/60 rounded-2xl">
          <h1 class="text-3xl font-bold mb-8 text-[#2D1810]">Edit Service</h1>

          <form @submit.prevent="submitEditedForm" @keydown.enter.capture="blockEnterSubmit" class="space-y-6">
            <!-- Title -->
            <div>
              <InputLabel for="title" value="Service Title" />
              <TextInput v-model="form.title" id="title" class="w-full bg-[#fefdfb] border-gray-300 focus:border-[#e4299c] focus:ring-[#e4299c] px-5 py-3.5" />
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
              ></textarea>
              <p v-if="form.errors.description" class="text-red-600 text-sm mt-2 font-semibold">{{ form.errors.description }}</p>
            </div>

            <!-- Phone -->
            <div>
              <InputLabel for="phone" value="Contact Phone Number" />
              <TextInput v-model="form.phone" id="phone" class="w-full bg-[#fefdfb] border-gray-300 focus:border-[#e4299c] focus:ring-[#e4299c] px-5 py-3.5" />
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
              />
              <p v-if="form.errors.price" class="text-red-600 text-sm mt-2 font-semibold">{{ form.errors.price }}</p>
            </div>

            <!-- Banner -->
            <div>
              <InputLabel for="banner" value="Banner Image (optional)" />
              <input 
                type="file" 
                @change="handleBannerChange" 
                class="w-full mt-2 bg-[#fefdfb] border border-gray-300 rounded-xl px-5 py-3.5 text-sm text-[#2D1810] file:mr-4 file:py-2.5 file:px-5 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gradient-to-r file:from-[#e4299c] file:to-[#ff6b9d] file:text-white hover:file:shadow-lg file:transition-all file:duration-200 cursor-pointer focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] outline-none transition-all" 
                accept="image/*" 
              />
              <p v-if="form.errors.banner" class="text-red-600 text-sm mt-2 font-semibold">{{ form.errors.banner }}</p>

              <div v-if="form.banner === null && service.banner" class="mt-4">
                <div class="relative w-full sm:w-1/2 aspect-video rounded-xl border border-white/60 overflow-hidden shadow-lg">
                  <img :src="`/storage/${service.banner}`" alt="Current banner" class="absolute inset-0 w-full h-full object-cover" />
                  <div class="absolute inset-0 bg-gradient-to-br from-[#e4299c]/10 to-[#e8662c]/10 mix-blend-multiply"></div>
                </div>
              </div>
            </div>

            <!-- Labels -->
            <div>
              <InputLabel value="Labels (max 10)" />
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
              <input
                v-model="newLabel"
                @keyup.enter.prevent="addLabel(newLabel)"
                type="text"
                class="border border-gray-300 px-5 py-3.5 rounded-xl w-full bg-[#fefdfb] text-[#2D1810] placeholder-[#6b5b73] focus:ring-2 focus:ring-[#e4299c] focus:border-[#e4299c] outline-none transition-all font-body"
                placeholder="Type a new label and press Enter or Add"
                :disabled="form.labels.length >= 10"
              />
              <button
                type="button"
                class="mt-3 bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white px-6 py-3 rounded-xl text-sm font-semibold hover:shadow-lg transition-all duration-200 disabled:opacity-50"
                @click="addLabel(newLabel)"
                :disabled="form.labels.length >= 10"
              >
                Add Label
              </button>
              <div class="flex flex-wrap gap-2 mt-4">
                <span
                  v-for="(label, index) in form.labels"
                  :key="index"
                  class="bg-[#fce7f3] text-[#e4299c] border border-[#e4299c]/40 px-4 py-2 rounded-full text-sm font-semibold flex items-center gap-2"
                >
                  {{ label }}
                  <button type="button" class="text-red-600 hover:text-red-700 font-bold text-base" @click="removeLabel(index)">×</button>
                </span>
              </div>
              <p v-if="form.labels.length >= 10" class="text-red-600 text-sm mt-2 font-semibold">Maximum 10 labels allowed.</p>
            </div>

            <!-- Time Slots -->
            <div>
              <InputLabel value="Available Time Slots" class="mb-3" />
              <DateTimePicker
                :weekStartsOn="1"
                :step="60"
                :minDate="todayISO"
                :slots="form.available_slots"
                v-model:slots="form.available_slots"
                variant="provider"
              />
            </div>

            <!-- Submit -->
            <div class="pt-4">
              <PrimaryButton type="submit" class="w-full sm:w-auto">Save Changes</PrimaryButton>
            </div>
          </form>
        </div>

        <!-- Delete Section -->
        <div class="backdrop-blur-lg bg-white/80 p-6 sm:p-8 shadow-xl border border-red-200/60 rounded-2xl">
          <h2 class="text-2xl font-bold text-red-600 mb-4">Danger Zone</h2>
          <p class="text-sm text-[#6b5b73] mb-6 font-body leading-relaxed">
            This will permanently delete the service and all associated bookings. This action cannot be undone.
          </p>
          <button
            @click="deleteService"
            class="w-full sm:w-auto bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-3 rounded-xl hover:shadow-lg font-semibold transition-all duration-200"
          >
            Delete Service
          </button>
        </div>
      </div>
    </section>

    <!-- PopupConfirm instance -->
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
import { useForm, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import InputLabel from '@/Components/basics/InputLabel.vue'
import TextInput from '@/Components/basics/TextInput.vue'
import PrimaryButton from '@/Components/basics/PrimaryButton.vue'
import DateTimePicker from '@/Components/DateTimePicker.vue'
import PopupConfirm from '@/Components/PopupConfirm.vue'

const props = defineProps({
  auth: Object,
  service: Object,
})

// Form setup
const form = useForm({
  title: props.service.title,
  description: props.service.description,
  phone: props.service.phone,
  banner: null,
  available_slots: [...props.service.slots],
  labels: [...(props.service.labels || [])],
  price: props.service.price || '',
})

// Labels and suggestions
const newLabel = ref('')
const labelSuggestions = ['Nails', 'Hair', 'Skincare', 'Lashes', 'Body', 'Makeup', 'Wellness']

// File upload
function handleBannerChange(event) {
  form.banner = event.target.files[0]
}

function blockEnterSubmit(e) {
  const el = e.target
  // allow Enter in textareas
  const isTextarea = el.tagName === 'TEXTAREA' || el.closest('textarea')
  // allow Enter on specifically flagged fields (like the label input)
  const allow = el.hasAttribute('data-allow-enter')

  if (!isTextarea && !allow) {
    e.preventDefault()  // stops the implicit form submit
  }
}

// Submit form
function submitEditedForm() {
  form.transform(data => ({ ...data, _method: 'PUT' }))
  form.post(route('services.update', props.service.id), {
    forceFormData: true,
    onSuccess: () => {
      router.visit(route('my-services'))
    },
  })
}

// Label management
function addLabel(label) {
  const trimmed = label.trim()
  if (trimmed && !form.labels.includes(trimmed) && form.labels.length < 10) {
    form.labels.push(trimmed)
  }
  newLabel.value = ''
}

function removeLabel(index) {
  form.labels.splice(index, 1)
}

// Confirm modal management
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

// Delete service
function deleteService() {
  askConfirm({
    title: 'Delete Service',
    message: 'This will permanently delete the service and all related bookings. Continue?',
    onConfirm: () => {
      router.delete(route('services.destroy', props.service.id), {
        onSuccess: () => router.visit(route('my-services')),
      })
    },
  })
}
</script>
