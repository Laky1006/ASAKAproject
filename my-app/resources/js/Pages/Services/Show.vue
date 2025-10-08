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
            <div class="flex items-center justify-between">
              <h1 class="text-3xl font-bold">{{ service.title }}</h1>
              <ReportButton
                v-if="auth.user"
                :service-id="service.id"
              />
            </div>
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
          <!-- Applicant slot picker -->
          <h2 class="text-lg font-semibold mb-2">&#128197; Pick a date</h2>
          <DateTimePicker
            variant="applicant"
            :slots="normalizedSlots"
            v-model="pickedSlot"
            :week-start="1"
          />

          <div class="mt-4" v-if="pickedSlot">
            <div class="flex items-center justify-between bg-gray-100 px-4 py-2 rounded">
              <div>
                <div class="font-medium">Selected</div>
                <div class="text-sm text-gray-700">
                  {{ pickedSlot.date }} at {{ pickedSlot.time }}
                </div>
              </div>

              <button
                v-if="auth.user && auth.user.role === 'reguser'"
                @click="submitSignup(pickedSlot.time, pickedSlot.date)"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
              >
                Sign Up
              </button>
              <div v-else class="text-base text-black-700 font-bold">
                LogIn as User to apply
              </div>
            </div>
          </div>

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
          <p v-if="form.errors.rating" class="text-red-600 text-sm -mt-1 mb-2">
            {{ form.errors.rating }}
          </p>

          <textarea
            v-model="form.comment"
            rows="3"
            class="w-full border rounded px-3 py-2"
            :class="form.errors.comment ? 'border-red-500' : 'border-gray-300'"
            :aria-invalid="!!form.errors.comment"
            placeholder="Write a comment (optional)"
          ></textarea>

          <p v-if="form.errors.comment" class="text-red-600 text-sm mt-1">
            {{ form.errors.comment }}
          </p>

          <div v-if="form.errors.review" class="text-red-500 text-sm mt-2">
            {{ form.errors.review }}
          </div>

          <button
            @click="submitReview"
            :disabled="form.processing"
            class="mt-2 bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 text-sm"
          >
            {{ form.processing ? 'Posting...' : 'Post Review' }}
          </button>

          <p v-if="flash.success" class="text-green-600 text-sm mt-2">
            {{ flash.success }}
          </p>
        </div>

        <div v-else class="text-gray-500 italic mb-6">
          You must be a registered user to leave a review.
        </div>

        <div v-if="reviews.length">
          <div
            v-for="(review, index) in sortedReviews"
            :key="review.id ?? index"
            :id="`review-${review.id}`"
          >
          <Review
              :review="review"
              :auth="auth"
              date-format="locale"
              @delete="deleteReview"
            />
          </div>
        </div>
        <div v-else class="text-sm text-gray-500">No reviews yet.</div>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import DateTimePicker from '@/Components/DateTimePicker.vue'
import Review from '@/Components/Review.vue'
import ReportButton from '@/Components/ReportButton.vue'

import { ref, computed, onMounted, nextTick } from 'vue'
import { router, usePage, useForm } from '@inertiajs/vue3'  

// Props
const props = defineProps({
  auth: Object,
  service: Object,
  reviews: Array,
})

// --- useForm replaces your manual ref + error handling
const form = useForm({
  service_id: props.service.id,
  rating: 0,
  comment: '',
})

// Keep if you use flash messages elsewhere
const page  = usePage()
const flash = computed(() => page?.props?.flash ?? {})

// Applicant picker
const pickedSlot = ref(null)

// Scroll-to-highlight (unchanged)
onMounted(async () => {
  const hash = window.location.hash
  const urlParams = new URLSearchParams(window.location.search)
  const reviewId = hash.startsWith('#review-') ? hash.replace('#review-', '') : urlParams.get('review')

  if (reviewId) {
    await nextTick()
    const el = document.getElementById(`review-${reviewId}`)
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'center' })
      el.classList.add('ring-2', 'ring-blue-400', 'rounded-lg')
      setTimeout(() => el.classList.remove('ring-2', 'ring-blue-400'), 2000)
    }
  }
})

// For calendar slots (unchanged)
const normalizedSlots = computed(() =>
  (props.service.slots ?? [])
    .filter(s => s.is_available)
    .map(s => ({ date: s.date, time: s.time }))
)

// --- useForm submit
function submitReview() {
  // Optional client-side guard (keeps behavior you had)
  if (!form.rating && !form.comment.trim()) return

  form.post(route('reviews.store'), {
    preserveScroll: true,
    data: {
      service_id: props.service.id,
      rating: form.rating,
      comment: form.comment,
    },
    onSuccess: () => {
      // Reset after success
      form.reset('comment', 'rating')
    },
    // onError: () => { // errors in form.errors.* automatically
    // },
  })
}

// Booking (unchanged)
function submitSignup(time, dateOverride) {
  const date = dateOverride || pickedSlot.value?.date
  const t = time || pickedSlot.value?.time
  if (!date || !t) return
  if (!confirm(`Do you want to book this slot on ${date} at ${t}?`)) return

  router.post(
    route('services.book', props.service.id),
    { date, time: t },
    { preserveScroll: true }
  )
}

// Delete review (unchanged)
function deleteReview(reviewId) {
  if (!confirm('Are you sure you want to delete this review?')) return
  router.delete(route('reviews.destroy', reviewId), { preserveScroll: true })
}

// Sort reviews (unchanged)
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
</script>
