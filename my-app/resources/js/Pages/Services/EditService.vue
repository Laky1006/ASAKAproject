<template>
  <MainLayout :user="auth.user">
    <section class="py-12">
      <div class="max-w-4xl mx-auto space-y-6 sm:px-6 lg:px-8">

        <!-- Edit Service Form Card -->
        <div class="bg-white p-6 shadow sm:rounded-lg sm:p-8">
          <h1 class="text-2xl font-bold mb-6">Edit Service</h1>

          <form @submit.prevent="submitForm" @keydown.enter.capture="blockEnterSubmit" lass="space-y-6">
            <!-- Title -->
            <div>
              <InputLabel for="title" value="Service Title" />
              <TextInput v-model="form.title" id="title" class="w-full" />
              <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
            </div>

            <!-- Description -->
            <div>
              <InputLabel for="description" value="Description" />
              <textarea v-model="form.description" id="description" rows="4" class="w-full border rounded px-3 py-2"></textarea>
              <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
            </div>

            <!-- Phone -->
            <div>
              <InputLabel for="phone" value="Contact Phone Number" />
              <TextInput v-model="form.phone" id="phone" class="w-full" />
              <p v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</p>
            </div>

            <!-- Price -->
            <div>
              <InputLabel for="price" value="Price (optional)" />
              <TextInput
                v-model="form.price"
                id="price"
                class="w-full"
                type="number"
                step="0.01"
                min="0"
              />
              <p v-if="form.errors.price" class="text-red-500 text-sm mt-1">{{ form.errors.price }}</p>
            </div>

            <!-- Banner -->
            <div>
              <InputLabel for="banner" value="Banner Image (optional)" />
              <input type="file" @change="handleBannerChange" class="w-full mt-1" accept="image/*" />
              <p v-if="form.errors.banner" class="text-red-500 text-sm mt-1">{{ form.errors.banner }}</p>

              <div v-if="form.banner === null && service.banner" class="mt-2">
                <div class="relative w-1/2 aspect-video rounded border overflow-hidden">
                  <img :src="`/storage/${service.banner}`" alt="Current banner" class="absolute inset-0 w-full h-full object-cover" />
                </div>
              </div>
            </div>

            <!-- Labels -->
            <div>
              <InputLabel value="Labels (max 10)" />
              <div class="flex flex-wrap gap-2 mb-2">
                <button
                  v-for="suggestion in labelSuggestions"
                  :key="suggestion"
                  type="button"
                  class="px-3 py-1 bg-gray-200 rounded-full hover:bg-gray-300 text-sm"
                  @click="addLabel(suggestion)"
                >
                  {{ suggestion }}
                </button>
              </div>
              <input
                v-model="newLabel"
                @keyup.enter.prevent="addLabel(newLabel)"
                type="text"
                class="border px-3 py-2 rounded w-full"
                placeholder="Type a new label and press Enter or Add"
                :disabled="form.labels.length >= 10"
              />
              <button
                type="button"
                class="mt-2 bg-blue-600 text-white px-4 py-1 rounded text-sm"
                @click="addLabel(newLabel)"
                :disabled="form.labels.length >= 10"
              >
                Add
              </button>
              <div class="flex flex-wrap gap-2 mt-3">
                <span
                  v-for="(label, index) in form.labels"
                  :key="index"
                  class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm flex items-center"
                >
                  {{ label }}
                  <button type="button" class="ml-2 text-red-500 hover:text-red-700" @click="removeLabel(index)">x</button>
                </span>
              </div>
              <p v-if="form.labels.length >= 10" class="text-red-500 text-sm mt-1">Maximum 10 labels allowed.</p>
            </div>

            <!-- Time Slots -->
            <DateTimePicker
              :weekStartsOn="1"
              :step="60"
              :minDate="todayISO"
              :slots="form.available_slots"
              v-model:slots="form.available_slots"
              variant="provider"
            />

            <!-- Submit -->
            <div class="pt-4">
              <PrimaryButton type="submit">Save Changes</PrimaryButton>
            </div>
          </form>
        </div>

        <!-- Delete Section -->
        <div class="bg-white p-6 shadow sm:rounded-lg sm:p-8">
          <h2 class="text-xl font-semibold text-red-600 mb-4">Danger Zone</h2>
          <p class="text-sm text-gray-700 mb-4">
            This will permanently delete the service and all associated bookings.
          </p>
          <button
            @click="deleteService"
            class="bg-red-600 text-white px-5 py-2 rounded hover:bg-red-700"
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
function submitForm() {
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
