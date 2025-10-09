<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- calendar -->
    <div class="backdrop-blur-md bg-white/70 border border-white/60 rounded-xl p-5 shadow-lg">
      <div class="mb-4 font-semibold text-lg text-[#2D1810]">
        {{ monthNames[displayMonth] }} {{ displayYear }}
      </div>

      <!-- date picking -->
      <DatePicker
        v-model="dateModel"
        inline
        auto-apply
        :enable-time-picker="false"
        :week-start="weekStartsOn"
        :min-date="todayISO"
        :disabled-dates="props.variant === 'applicant' ? applicantDisablePredicate : disabledDatesParsed"
        :markers="dayMarkers"
        @update:model-value="onDatePicked"
      />
    </div>

    <!-- time -->
    <div class="backdrop-blur-md bg-white/70 border border-white/60 rounded-xl p-5 shadow-lg">
      <div class="space-y-4">
        <!-- PROVIDER: EDITOR (existing behavior) -->
        <div v-if="props.variant === 'provider' && !props.readonly" class="space-y-4">
          <div class="text-sm text-[#6b5b73] font-body">
            Selected date:
            <span class="font-semibold text-[#2D1810]" v-if="selectedDate">{{ selectedDate }}</span>
            <span v-else class="italic">none</span>
          </div>

          <div>
            <label class="text-sm block mb-2 font-semibold text-[#2D1810]">Time</label>
            <DatePicker
              ref="tp"
              v-model="timeModel"
              time-picker
              :is-24="true"
              text-input
              format="HH:mm"
              :minutes-increment="minuteStep"
              :hours-increment="1"
              :text-input-options="{ enterSubmit: true, tabSubmit: true }"
              :action-row="{ showNow:false, showSelect:true, showCancel:false }"
              @open="bindEnter"
              @closed="unbindEnter"
            />
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              class="px-4 py-2 bg-gradient-to-r from-[#e4299c] to-[#ff6b9d] text-white rounded-xl hover:shadow-lg disabled:opacity-50 transition-all duration-200 font-semibold text-sm"
              :disabled="!selectedDate || timeModel === null"
              :title="!selectedDate ? 'Pick a date first' : (timeModel===null ? 'Pick a time' : '')"
              @click="emitAdd"
            >
              Add
            </button>

            <button
              type="button"
              class="px-4 py-2 backdrop-blur-sm bg-white/60 border border-white/60 rounded-xl hover:bg-white/80 transition-all duration-200 font-semibold text-sm text-[#2D1810]"
              @click="clearTime"
            >
              Clear time
            </button>
          </div>

          <div v-if="selectedDate" class="mt-3">
            <div class="text-sm font-semibold mb-3 text-[#2D1810]">
              Times on {{ selectedDate }}
            </div>

            <div v-if="daySlots.length" class="space-y-2 max-h-48 overflow-auto">
              <div
                v-for="slot in daySlots"
                :key="slot.time"
                class="flex items-center justify-between backdrop-blur-sm bg-white/60 border border-white/40 rounded-lg px-4 py-2.5 shadow-sm"
              >
                <span class="text-sm font-semibold text-[#2D1810]">{{ slot.displayTime }}</span>
                <button
                  type="button"
                  class="text-red-600 text-sm hover:underline font-semibold"
                  @click="emitRemove(slot.time)"
                >
                  Remove
                </button>
              </div>
            </div>

            <p v-else class="text-sm text-[#6b5b73] italic font-body">
              No times added for this date yet.
            </p>
          </div>
        </div>

        <!-- PROVIDER: PREVIEW (read-only, shows all) -->
        <div v-else-if="props.variant === 'provider' && props.readonly" class="space-y-4">
          <div class="text-lg font-semibold mb-3 text-[#2D1810]">
            Schedule preview on
            <span class="font-semibold" v-if="selectedDate">{{ selectedDate }}</span>
            <span v-else class="italic text-[#6b5b73]">select date...</span>
          </div>

          <div v-if="selectedDate">
            <div v-if="previewSlots.length" class="grid grid-cols-2 gap-2">
              <button
                v-for="slot in previewSlots"
                :key="slot.time"
                type="button"
                class="px-3 py-2.5 backdrop-blur-sm border rounded-xl text-center font-semibold text-sm transition-all duration-200"
                :class="[
                  slot.available 
                    ? 'bg-green-50/80 border-green-400 text-green-700 hover:bg-green-100/80' 
                    : 'bg-gray-100/80 border-gray-300 text-gray-400 line-through',
                  selectedPreviewTime === slot.time ? 'ring-2 ring-[#e4299c]' : ''
                ]"
                @click="selectPreviewSlot(slot)"
              >
                {{ slot.displayTime }}
              </button>
            </div>

            <p v-else class="text-sm text-[#6b5b73] italic font-body">
              No times for this date.
            </p>
          </div>

          <div class="mt-3 flex items-center gap-4 text-sm text-[#6b5b73] font-body">
            <span class="inline-flex items-center gap-1.5">
              <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500"></span> Available
            </span>
            <span class="inline-flex items-center gap-1.5">
              <span class="inline-block w-2.5 h-2.5 rounded-full bg-gray-400"></span> Booked
            </span>
          </div>
        </div>

        <!-- APPLICANT (existing) -->
        <div v-else class="space-y-4">
          <div class="text-lg font-semibold mb-3 text-[#2D1810]">
            Available times on
            <span class="font-semibold" v-if="selectedDate">{{ selectedDate }}</span>
            <span v-else class="italic text-[#6b5b73]">select date...</span>
          </div>

          <div v-if="selectedDate">
            <div v-if="daySlots.length" class="grid grid-cols-2 gap-2">
              <button
                v-for="slot in daySlots"
                :key="slot.time"
                type="button"
                class="px-3 py-2.5 backdrop-blur-sm bg-white/60 border border-white/60 rounded-xl hover:bg-white/80 transition-all duration-200 font-semibold text-sm text-[#2D1810]"
                :class="{ 'ring-2 ring-[#e4299c] bg-white/90': isSelected(slot) }"
                @click="selectApplicantSlot(slot)"
              >
                {{ slot.displayTime }}
              </button>
            </div>

            <p v-else class="text-sm text-[#6b5b73] italic font-body">
              No times available for this date.
            </p>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import DatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

const props = defineProps({
  variant: { type: String, default: 'provider' },          // 'provider' | 'applicant'
  readonly: { type: Boolean, default: false },             // NEW: provider preview
  showUnavailable: { type: Boolean, default: false },      // NEW: show booked too
  weekStartsOn: { type: Number, default: 1 },
  step: { type: Number, default: 60 },
  minDate: { type: String, default: null },
  disabledDates: { type: Array, default: () => [] },
  slots: { type: Array, default: () => [] },
  modelValue: { type: Object, default: null },
})

const emit = defineEmits(['add-slot', 'remove-slot', 'update:slots', 'update:modelValue', 'select-slot-preview',])

// state
const selectedDate = ref('')
const dateModel = ref(null)
const timeModel = ref(null)
const tp = ref(null)
const selectedPreviewTime = ref(null)

// constants
const monthNames = [
  'January','February','March','April','May','June',
  'July','August','September','October','November','December'
]

// helpers
function pad(n) { return n < 10 ? `0${n}` : `${n}` }
function ymd(d) { return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}` }

// min/disabled dates
const todayISO = computed(() => {
  if (props.minDate) return new Date(props.minDate + 'T00:00:00')
  const now = new Date()
  return new Date(now.getFullYear(), now.getMonth(), now.getDate())
})

const disabledDatesParsed = computed(() =>
  (props.disabledDates || []).map(d => new Date(d + 'T00:00:00'))
)

const allowedDays = computed(() => {
  const s = new Set()
  for (const item of (props.slots || [])) {
    if (item && typeof item.date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(item.date)) {
      s.add(item.date)
    }
  }
  return s
})

function applicantDisablePredicate(date) {
  if (!allowedDays.value.size) return true
  return !allowedDays.value.has(ymd(date))
}

// increments
const minuteStep = computed(() => {
  const m = Math.max(1, Math.round(props.step / 60))
  return Math.min(60, m)
})

// slots for selected date (include availability)
const daySlots = computed(() => {
  if (!selectedDate.value || !Array.isArray(props.slots)) return []
  return props.slots
    .filter(s => s && s.date === selectedDate.value && typeof s.time === 'string')
    .map(s => ({
      ...s,
      available: s.available !== false, // default to true if missing
      displayTime: s.time.split(':').slice(0,2).join(':'),
    }))
    .sort((a, b) => a.time.localeCompare(b.time))
})

// provider preview visible slots list
const previewSlots = computed(() => {
  return props.showUnavailable ? daySlots.value : daySlots.value.filter(s => s.available)
})

function selectPreviewSlot(slot) {
  selectedPreviewTime.value = slot.time
  // Send { date, time, available, reguser_name? } up to the parent
  emit('select-slot-preview', {
    date: selectedDate.value,
    time: slot.time,
    available: !!slot.available,
    reguser_name: slot.reguser_name ?? null,
  })
}

// header date
const _hdrDate = computed(() => dateModel.value ?? new Date())
const displayMonth = computed(() => _hdrDate.value.getMonth())
const displayYear  = computed(() => _hdrDate.value.getFullYear())

// calendar markers (green if any available, gray if only booked)
const dayMarkers = computed(() => {
  const byDate = new Map()
  for (const s of (props.slots || [])) {
    if (!s?.date) continue
    const key = s.date
    const entry = byDate.get(key) || { avail: 0, booked: 0 }
    if (s.available === false) entry.booked++
    else entry.avail++
    byDate.set(key, entry)
  }
  return Array.from(byDate.entries()).map(([d, { avail }]) => ({
    date: new Date(d + 'T00:00:00'),
    type: 'dot',
    color: avail > 0 ? '#16a34a' : '#9ca3af',
  }))
})

/* ===== core logic ===== */
function addSlot({ date, time }) {
  if (!date || !time) return
  const exists = (props.slots || []).some(s => s.date === date && s.time === time)
  if (exists) return
  const next = [...props.slots, { date, time }]
  emit('update:slots', next)
}

function removeSlot(date, time) {
  const next = (props.slots || []).filter(s => !(s.date === date && s.time === time))
  emit('update:slots', next)
}

function isSelected(slot) {
  return props.modelValue
    && props.modelValue.date === selectedDate.value
    && props.modelValue.time === slot.time
}

function selectApplicantSlot(slot) {
  emit('update:modelValue', { date: selectedDate.value, time: slot.time })
}

/* ===== events ===== */
function onDatePicked(val) {
  selectedDate.value = val ? ymd(val) : ''
}

function clearTime() {
  timeModel.value = null
}

// Emit add-slot { date, time } in 24h HH:mm
function emitAdd() {
  if (!selectedDate.value || !timeModel.value) return
  const { hours = 0, minutes = 0 } = timeModel.value
  const time = `${pad(hours)}:${pad(minutes)}`
  addSlot({ date: selectedDate.value, time })
}

function emitRemove(time) {
  if (!selectedDate.value) return
  removeSlot(selectedDate.value, time)
}

/* ===== keyboard ===== */
function onKeydown(e) {
  if (e.key !== 'Enter') return
  const el = e.target
  if (el && el.classList && el.classList.contains('dp__input')) return
  e.preventDefault()
  if (tp.value && typeof tp.value.selectDate === 'function') tp.value.selectDate()
  if (tp.value && typeof tp.value.closeMenu === 'function') tp.value.closeMenu()
}
function bindEnter()   { document.addEventListener('keydown', onKeydown, true) }
function unbindEnter() { document.removeEventListener('keydown', onKeydown, true) }

/* ===== lifecycle ===== */
onBeforeUnmount(() => unbindEnter())
</script>

<style scoped>
/* this makes the time popup normal size */
:deep(.dp--overlay-relative) { max-height: fit-content !important; height: auto !important; overflow: hidden !important; }
:deep(.dp__time_col)        { max-height: fit-content !important; }
:deep(.dp__time_display)    { font-size: 24px; }
:deep(.dp__time_col)        { padding-left: 8px; padding-right: 8px; }
:deep(.dp__selection_preview){ display: none !important; }
:deep(.dp__time_display) {
  pointer-events: none;
}
</style>
