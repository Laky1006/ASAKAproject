<template>
  <MainLayout :user="auth.user">
    <section class="max-w-5xl mx-auto py-10 px-6">
      <!-- White card container -->
      <div class="bg-white shadow sm:rounded-lg overflow-hidden">
        <!-- Banner -->
        <img
          :src="service.banner ? `/storage/${service.banner}` : '/images/default-banner.jpg'"
          alt="Lesson Banner"
          class="w-full h-64 object-cover"
        />

        <div class="p-6 space-y-6">
          <!-- Title + Rating -->
          <div>
            <h1 class="text-3xl font-bold">{{ service.title }}</h1>
            <div class="flex items-center gap-2">
              <div class="flex text-2xl">
                <span
                  v-for="n in 5"
                  :key="n"
                  :class="service.rating >= n ? 'text-yellow-500' : 'text-gray-300'"
                >★</span>
              </div>
              <span class="text-gray-600 text-lg">
                {{ service.rating ? `${service.rating}/5` : 'Not Rated' }}
              </span>
            </div>
          </div>

          <!-- Description -->
          <div>
            <h2 class="text-xl font-semibold mb-2">Description</h2>
            <p class="text-gray-700">{{ service.description }}</p>
          </div>

          <!-- Contact Info & Price (side by side) -->
          <div class="flex flex-col sm:flex-row gap-6">
            <div class="flex-1">
              <h2 class="text-xl font-semibold mb-2">&#128222; Contact Info</h2>
              <p class="text-gray-700">{{ service.phone ?? 'N/A' }}</p>
            </div>
            <div class="flex-1">
              <h2 class="text-xl font-semibold mb-2">&#128181; Price</h2>
              <p class="text-gray-700">{{ service.price ?? 'Free' }}</p>
            </div>
          </div>

          <!-- Date Picker -->
          <div>
            <h2 class="text-lg font-semibold mb-2">&#128197; Pick a Lesson Date</h2>
            <Datepicker
              v-model="selectedDate"
              :inline="true"
              :highlight="highlightedDates"
            />
          </div>

          <!-- Available Time Slots -->
          <div v-if="selectedSlots.length" class="mt-4">
            <h3 class="font-semibold mb-2">Times on {{ formattedSelectedDate }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div
                v-for="(time, idx) in selectedSlots"
                :key="idx"
                class="bg-gray-100 px-4 py-2 rounded flex justify-between items-center space-x-4"
              >
                <span>&#128338; {{ time }}</span>
                <button
                  v-if="auth.user && auth.user.role === 'reguser'"
                  @click="submitSignup(time)"
                  class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm"
                >
                  Sign Up
                </button>
                <div v-else class="text-base text-black-700 font-bold">
                  Log in as reguser to apply
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-sm text-gray-500">No available times on this date.</div>
        </div>
      </div>

      <!-- Reviews -->
      <div class="bg-white mt-8 p-6 shadow sm:rounded-lg space-y-4">
        <h2 class="text-xl font-semibold mb-4">
          Reviews <span class="text-gray-500">({{ reviews.length }})</span>
        </h2>

        <div v-if="auth.user?.role === 'reguser'" class="mb-6">
          <div class="flex items-center gap-1 mb-2">
            <span
              v-for="n in 5"
              :key="n"
              class="text-2xl cursor-pointer"
              :class="n <= form.rating ? 'text-yellow-500' : 'text-gray-300'"
              @click="form.rating = n"
            >★</span>
          </div>

          <textarea
            v-model="form.comment"
            rows="3"
            class="w-full border rounded px-3 py-2"
            placeholder="Write a comment (optional)"
          ></textarea>

          <div v-if="alreadyReviewed" class="text-red-500 text-sm mt-2">
            You have already left a review here.
          </div>

          <button
            @click="submitReview"
            class="mt-2 bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 text-sm"
          >
            Post Review
          </button>
        </div>

        <div v-else class="text-gray-500 italic mb-6">
          You must be signed in as a reguser to leave a review.
        </div>

        <div v-if="reviews.length">
          <Review
            v-for="(review, index) in sortedReviews"
            :key="review.id ?? index"
            :review="review"
            :auth="auth"
            date-format="locale"
            @delete="deleteReview"
          />
        </div>
        <div v-else class="text-sm text-gray-500">No reviews yet.</div>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import Review from '@/Components/Review.vue'

import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  auth: Object,
  service: Object,
  reviews: Array,
})

const selectedDate = ref(null)

const form = ref({
  rating: 0,
  comment: '',
})

const submitting = ref(false)

const page = usePage()
const alreadyReviewed = computed(() =>
  page?.props?.errors?.[0]?.includes?.('already reviewed') ?? false
)

const submitReview = () => {
  if (!form.value.rating && !form.value.comment.trim()) return

  submitting.value = true
  router.post(
    route('reviews.store'),
    {
      service_id: props.service.id,
      rating: form.value.rating,
      comment: form.value.comment,
    },
    {
      preserveScroll: true,
      onFinish: () => {
        form.value.comment = ''
        form.value.rating = 0
        submitting.value = false
      },
    }
  )
}

/* ----- slots / calendar helpers ----- */
const slotMap = computed(() => {
  const map = {}
  const slots = props.service.slots?.filter(s => s.is_available)
  if (Array.isArray(slots)) {
    slots.forEach(slot => {
      if (!map[slot.date]) map[slot.date] = []
      map[slot.date].push(slot.time)
    })
  }
  return map
})

const highlightedDates = computed(() =>
  Object.keys(slotMap.value)
    .filter(d => /^\d{4}-\d{2}-\d{2}$/.test(d))
    .map(d => {
      const [y, m, day] = d.split('-').map(Number)
      return new Date(y, m - 1, day)
    })
)

const selectedSlots = computed(() => {
  if (!selectedDate.value) return []
  const d = new Date(selectedDate.value)
  const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
  return slotMap.value[key] || []
})

const formattedSelectedDate = computed(() => {
  if (!selectedDate.value) return ''
  return new Date(selectedDate.value).toLocaleDateString()
})

/* ----- reviews list ordering ----- */
const sortedReviews = computed(() => {
  if (!props.reviews || !Array.isArray(props.reviews)) return []
  if (props.auth?.user?.role === 'reguser' && props.auth.user.reguser?.id) {
    const reguserId = props.auth.user.reguser.id
    const own = props.reviews.find(r => r.reguser_id === reguserId)
    const others = props.reviews.filter(r => r.reguser_id !== reguserId)
    return own ? [own, ...others] : others
  }
  return props.reviews
})

/* ----- actions ----- */
const submitSignup = (time) => {
  const d = new Date(selectedDate.value)
  const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`

  if (!confirm(`Do you want to book this slot on ${key} at ${time}?`)) return

  router.post(
    route('services.book', props.service.id),
    { date: key, time },
    { preserveScroll: true }
  )
}

const deleteReview = (reviewId) => {
  if (!confirm('Are you sure you want to delete this review?')) return
  router.delete(route('reviews.destroy', reviewId), {
    preserveScroll: true,
  })
}
</script>

<style>
.dp__theme_light {
  --dp-highlight-color: rgba(16, 0, 158, 0.329);
}
</style>
