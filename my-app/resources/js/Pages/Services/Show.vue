<template>
  <MainLayout :user="auth.user">
    <section class="max-w-5xl mx-auto py-10 px-6">
      <!-- Glassmorphic card container -->
      <div class="backdrop-blur-lg bg-white/60 shadow-xl border border-white/60 rounded-2xl overflow-hidden">
        <!-- Banner with gradient overlay -->
        <div class="relative overflow-hidden">
          <img
            :src="service.banner ? `/storage/${service.banner}` : '/images/default-banner.jpg'"
            alt="Lesson Banner"
            class="w-full h-64 object-cover"
          />
          <div class="absolute inset-0 bg-gradient-to-br from-[#e4299c]/30 to-[#e8662c]/30 mix-blend-multiply"></div>
        </div>

        <div class="p-6 space-y-6">
          <!-- Title + Rating + Actions -->
          <div>
            <div class="flex items-center justify-between">
              <h1 class="text-3xl font-bold text-[#2D1810]">{{ service.title }}</h1>

              <div class="flex items-center gap-3">
                <ReportButton
                  v-if="auth.user && mode !== 'provider'"
                  :service-id="service.id"
                />
                <Link
                  v-if="mode === 'provider'"
                  :href="route('services.edit', service.id)"
                  class="bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-200 text-sm font-semibold"
                >
                  Edit
                </Link>
              </div>
            </div>

            <div class="flex items-center gap-2 mt-2">
              <div class="flex text-2xl">
                <span
                  v-for="n in 5"
                  :key="n"
                  :class="service.rating >= n ? 'text-yellow-500' : 'text-gray-300'"
                >★</span>
              </div>
              <span class="text-[#6b5b73] text-lg font-body">
                {{ service.rating ? `${service.rating}/5` : 'Not Rated' }}
              </span>
            </div>
          </div>

          <!-- Description -->
          <div class="backdrop-blur-sm bg-white/30 rounded-xl p-5 border border-white/40">
            <h2 class="text-xl font-semibold mb-3 text-[#2D1810]">Description</h2>
            <p class="text-[#6b5b73] leading-relaxed font-body">{{ service.description }}</p>
          </div>

          <!-- Contact Info & Price (side by side) -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="backdrop-blur-sm bg-white/30 rounded-xl p-5 border border-white/40">
              <h2 class="text-lg font-semibold mb-2 text-[#2D1810]">&#128222; Contact Info</h2>
              <p class="text-[#6b5b73] font-body">{{ service.phone ?? 'N/A' }}</p>
            </div>
            <div class="backdrop-blur-sm bg-white/30 rounded-xl p-5 border border-white/40">
              <h2 class="text-lg font-semibold mb-2 text-[#2D1810]">&#128181; Price</h2>
              <p class="text-[#e8662c] font-bold text-lg font-body">€ {{ service.price ?? 'Free' }}</p>
            </div>
          </div>

          <!-- PUBLIC (applicant) picker -->
          <div v-if="mode === 'public'" class="backdrop-blur-sm bg-white/30 rounded-xl p-5 border border-white/40">
            <h2 class="text-lg font-semibold mb-4 text-[#2D1810]">&#128197; Pick a date</h2>
            <DateTimePicker
              variant="applicant"
              :slots="applicantSlots"
              v-model="pickedSlot"
              :week-start="1"
            />

            <p v-if="flash.success" class="mt-4 text-green-600 text-sm font-semibold">
              {{ flash.success }}
            </p>

            <div class="mt-4" v-if="pickedSlot">
              <div class="flex items-center justify-between backdrop-blur-md bg-white/50 px-5 py-4 rounded-xl border border-white/60 shadow-lg">
                <div>
                  <div class="font-semibold text-[#2D1810]">Selected</div>
                  <div class="text-sm text-[#6b5b73] font-body">
                    {{ pickedSlot.date }} at {{ pickedSlot.time }}
                  </div>
                </div>

                <button
                  v-if="auth.user && auth.user.role === 'reguser'"
                  :disabled="isBooking"
                  @click="submitSignup(pickedSlot.time, pickedSlot.date)"
                  class="bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white px-5 py-2.5 rounded-xl hover:shadow-lg disabled:opacity-60 transition-all duration-200 font-semibold"
                >
                  {{ isBooking ? 'Booking...' : 'Sign Up' }}
                </button>
                <div v-else class="text-sm text-[#2D1810] font-semibold backdrop-blur-sm bg-white/40 px-4 py-2 rounded-lg border border-white/40">
                  Log in as User to apply
                </div>
              </div>
            </div>
          </div>

          <!-- PROVIDER preview (ALL slots, read-only) -->
          <div v-else class="backdrop-blur-sm bg-white/30 rounded-xl p-5 border border-white/40">
            <h2 class="text-lg font-semibold mb-4 text-[#2D1810]">&#128197; Schedule preview</h2>
            <DateTimePicker
              variant="provider"
              :readonly="true"
              :show-unavailable="true"
              :slots="allSlots"
              :disabled-dates="[]"
              :week-start="1"
              @select-slot-preview="onPreviewSelect"
            />

            <div class="mt-4" v-if="mode === 'provider' && selectedProviderSlot">
              <div class="backdrop-blur-md bg-white/50 px-6 py-4 rounded-xl border border-white/60 shadow-lg">
                <div class="flex items-center justify-between gap-4">
                  <div>
                    <div class="font-semibold text-[#2D1810]">Selected</div>
                    <div class="text-sm text-[#6b5b73] font-body mt-1">
                      {{ selectedProviderSlot.date }} at {{ selectedProviderSlot.time }}
                    </div>
                  </div>

                  <div class="flex-1 text-center">
                    <div class="text-sm">
                      <span
                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold backdrop-blur-sm border"
                        :class="selectedProviderSlot.available 
                          ? 'bg-green-100/80 text-green-700 border-green-200' 
                          : 'bg-gray-100/80 text-gray-700 border-gray-200'"
                      >
                        {{ selectedProviderSlot.available ? 'Free' : 'Booked' }}
                      </span>
                    </div>
                    <div v-if="!selectedProviderSlot.available" class="text-sm text-[#6b5b73] mt-2 font-body">
                      By: <span class="font-semibold text-[#2D1810]">{{ selectedProviderSlot.reguser_name ?? 'Unknown user' }}</span>
                    </div>
                  </div>

                  <div>
                    <button
                      v-if="!selectedProviderSlot.available"
                      type="button"
                      class="bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-200 font-semibold text-sm"
                      @click="cancelSelectedByProvider"
                    >
                      Cancel booking
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reviews Section -->
      <div class="backdrop-blur-lg bg-white/60 mt-8 p-6 shadow-xl border border-white/60 rounded-2xl space-y-4">
        <h2 class="text-xl font-semibold text-[#2D1810]">
          Reviews <span class="text-[#6b5b73]">({{ reviews.length }})</span>
        </h2>

        <div v-if="auth.user?.role === 'reguser'" class="mb-6 backdrop-blur-sm bg-white/100 rounded-xl p-5 border border-white/40">
          <div class="flex items-center gap-1 mb-3">
            <span
              v-for="n in 5"
              :key="n"
              class="text-3xl cursor-pointer transition-all hover:scale-110" 
              :class="n <= form.rating ? 'text-yellow-500' : 'text-gray-300'"
              @click="form.rating = n"
            >★</span>
          </div>
          <p v-if="form.errors.rating" class="text-red-600 text-sm mb-2 font-semibold ">
            {{ form.errors.rating }}
          </p>

          <textarea
            v-model="form.comment"
            rows="3"
            class="w-full rounded-xl backdrop-blur-sm bg-white/50 border px-4 py-3 text-[#2D1810] placeholder-[#6b5b73] focus:ring-2 focus:ring-[#e4299c] focus:bg-white/70 outline-none transition-all font-body"
            :class="form.errors.comment ? 'border-red-500' : 'border-white/60'"
            :aria-invalid="!!form.errors.comment"
            placeholder="Write a comment (optional)"
          ></textarea>

          <p v-if="form.errors.comment" class="text-red-600 text-sm mt-2 font-semibold">
            {{ form.errors.comment }}
          </p>

          <div v-if="form.errors.review" class="text-red-500 text-sm mt-2 font-semibold">
            {{ form.errors.review }}
          </div>

          <button
            @click="submitReview"
            :disabled="form.processing"
            class="mt-3 bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white px-5 py-2 rounded-xl hover:shadow-lg transition-all duration-200 font-semibold text-sm"
          >
            {{ form.processing ? 'Posting...' : 'Post Review' }}
          </button>

          <p v-if="flash.success" class="text-green-600 text-sm mt-3 font-semibold">
            {{ flash.success }}
          </p>
        </div>

        <div v-else class="text-[#6b5b73] italic mb-6 backdrop-blur-sm bg-white/20 rounded-xl p-4 border border-white/30 font-body">
          You must be a registered user to leave a review.
        </div>

        <div v-if="reviews.length" class="space-y-3">
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
        <div v-else class="text-sm text-[#6b5b73] backdrop-blur-sm bg-white/20 rounded-xl p-4 border border-white/30 font-body">
          No reviews yet.
        </div>
      </div>

      <!-- PopUp confirmation window -->
      <PopupConfirm
        v-model:show="showConfirm"
        :title="confirmTitle"
        :message="confirmMessage"
        @confirm="handleConfirm"
      />
    </section>
  </MainLayout>
</template>


<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import DateTimePicker from '@/Components/DateTimePicker.vue'
import Review from '@/Components/Review.vue'
import ReportButton from '@/Components/ReportButton.vue'
import PopupConfirm from '@/Components/PopupConfirm.vue'
import { Link, router, usePage, useForm } from '@inertiajs/vue3'
import { ref, computed, onMounted, nextTick } from 'vue'

// Props
const props = defineProps({
  auth: Object,
  service: Object,
  reviews: Array,
  mode: { type: String, default: 'public' }, // 'public' or 'provider'
})

// useForm
const form = useForm({
  service_id: props.service.id,
  rating: 0,
  comment: '',
})

// flash
const page  = usePage()
const flash = computed(() => page?.props?.flash ?? {})

// state
const pickedSlot = ref(null)
const isBooking = ref(false)

// confirm modal
const showConfirm = ref(false)
const confirmTitle = ref('Please Confirm')
const confirmMessage = ref('')
let confirmHandler = null

const selectedProviderSlot = ref(null)

function onPreviewSelect(slot) {
  // slot: { date, time, available, reguser_name }
  selectedProviderSlot.value = slot
}

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

// scroll highlight with header offset
onMounted(async () => {
  const hash = window.location.hash
  const urlParams = new URLSearchParams(window.location.search)
  const reviewId = hash.startsWith('#review-') ? hash.replace('#review-', '') : urlParams.get('review')

  if (reviewId) {
    await nextTick()
    const el = document.getElementById(`review-${reviewId}`)
    const offset = 300

    if (el) {
      const top = el.getBoundingClientRect().top + window.scrollY - offset
      window.scrollTo({ top, behavior: 'smooth' })

      el.classList.add('ring-2', 'ring-blue-400', 'rounded-lg')
      setTimeout(() => el.classList.remove('ring-2', 'ring-blue-400'), 2000)
    }
  }
})


// SLOTS
// Provider preview expects all slots with availability; public shows only available.
const allSlots = computed(() =>
  (props.service.slots ?? []).map(s => ({
    slot_id: s.slot_id ?? null,
    date: s.date,
    time: s.time,
    available: s.is_available !== false,
    reguser_name: s.reguser_name ?? null,
  }))
)

const applicantSlots = computed(() =>
  allSlots.value.filter(s => s.available).map(({ date, time }) => ({ date, time }))
)

// review submit
function submitReview() {
  if (!form.rating && !form.comment.trim()) return
  form.post(route('reviews.store'), {
    preserveScroll: true,
    data: {
      service_id: props.service.id,
      rating: form.rating,
      comment: form.comment,
    },
    onSuccess: () => form.reset('comment', 'rating'),
  })
}

// booking
function submitSignup(time, dateOverride) {
  const date = dateOverride || pickedSlot.value?.date
  const t = time || pickedSlot.value?.time
  if (!date || !t) return

  askConfirm({
    title: 'Confirm Booking',
    message: `Do you want to book this slot on ${date} at ${t}?`,
    onConfirm: () => {
      isBooking.value = true
      router.post(
        route('services.book', props.service.id),
        { date, time: t },
        {
          preserveScroll: true,
          onSuccess: () => {
            pickedSlot.value = null
            router.reload({ only: ['service'] })
            isBooking.value = false
          },
        },
      )
    }
  })
}

function cancelSelectedByProvider() {
  const s = selectedProviderSlot.value
  if (!s || s.available) return  // only prevent when nothing selected or it's already free

  askConfirm({
    title: 'Cancel booking',
    message: `Cancel ${s.date} at ${s.time}? The slot will become available.`,
    onConfirm: () => {
      const payload = s.slot_id
        ? { slot_id: s.slot_id }          // preferred path
        : { date: s.date, time: s.time }  // fallback if id missing

      router.post(
        route('services.provider.cancel', props.service.id),
        payload,
        {
          preserveScroll: true,
          onSuccess: () => {
            selectedProviderSlot.value = null
            router.reload({ only: ['service'] })
          },
        }
      )
    }
  })
}

// delete review
function deleteReview(reviewId) {
  askConfirm({
    title: 'Delete Review',
    message: 'Are you sure you want to delete this review?',
    onConfirm: () => {
      router.delete(route('reviews.destroy', reviewId), { preserveScroll: true })
    }
  })
}

// sort reviews
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
