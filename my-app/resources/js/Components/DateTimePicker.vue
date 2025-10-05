<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Calendar -->
    <div class="bg-white border rounded-lg p-4">
      <div class="mb-3 font-medium">
        {{ monthNames[displayMonth] }} {{ displayYear }}
      </div>

      <!-- Inline calendar, date-only -->
      <DatePicker
        v-model="dateModel"
        inline
        auto-apply
        :enable-time-picker="false"
        :week-start="weekStartsOn"
        :min-date="parsedMinDate"
        :disabled-dates="disabledDatesParsed"
        @update:model-value="onDatePicked"
      />
    </div>

    <!-- Time + Add -->
    <div class="bg-white border rounded-lg p-4">
      <div class="space-y-4">
        <div class="text-sm text-gray-600">
          Selected date:
          <span class="font-medium" v-if="selectedDate">{{ selectedDate }}</span>
          <span v-else class="italic">none</span>
        </div>

        <!-- Time picker (24h), inline, minute step derived from 'step' seconds -->
        <div>
          <label class="text-sm block mb-1">Time</label>
          <DatePicker
            v-model="timeModel"
            time-picker
            time-picker-inline
            auto-apply
            :is-24="true"
            :hours-increment="1"
            :minutes-increment="minuteStep"
            :seconds-increment="0"
            :hide-input-icon="true"
          />
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
            :disabled="!selectedDate || timeModel === null"
            @click="emitAdd"
          >
            Add
          </button>

          <button
            type="button"
            class="px-3 py-2 border rounded hover:bg-gray-50"
            @click="clearTime"
          >
            Clear time
          </button>
        </div>

        <div v-if="timeModel" class="text-xs text-gray-600">
          Selected time: <span class="font-medium">{{ formattedTime }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import DatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

/**
 * Props
 * - weekStartsOn: 1 (Mon) or 0 (Sun)
 * - step: seconds for time step (e.g., 60 = 1 minute increments)
 * - minDate: block dates before this ISO (YYYY-MM-DD)
 * - disabledDates: array of ISO dates to disable (YYYY-MM-DD)
 */
const props = defineProps({
  weekStartsOn: { type: Number, default: 1 },
  step: { type: Number, default: 60 },
  minDate: { type: String, default: null },
  disabledDates: { type: Array, default: () => [] }
})

const emit = defineEmits(['add-slot'])

/** Calendar month header helpers */
const monthNames = [
  'January','February','March','April','May','June',
  'July','August','September','October','November','December'
]

/** Selected date (YYYY-MM-DD) + time */
const selectedDate = ref('')

/**
 * Date models:
 * - dateModel: a JS Date used by vue-datepicker (date-only)
 * - timeModel: time picker returns { hours, minutes, seconds }
 */
const dateModel = ref(null)
const timeModel = ref(null)

/** Parse minDate and disabledDates to actual Date objects (midnight) */
const parsedMinDate = computed(() => props.minDate ? new Date(props.minDate + 'T00:00:00') : null)
const disabledDatesParsed = computed(() =>
  (props.disabledDates || []).map(d => new Date(d + 'T00:00:00'))
)

/** Minute increment derived from step seconds (60 -> 1 minute increments) */
const minuteStep = computed(() => {
  const m = Math.max(1, Math.round(props.step / 60))
  return Math.min(60, m)
})

/** Format helpers */
function pad(n) { return n < 10 ? `0${n}` : `${n}` }
function ymd(d) {
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}

/** Keep a readable month/year for header (from current dateModel or today) */
const _hdrDate = computed(() => dateModel.value ?? new Date())
const displayMonth = computed(() => _hdrDate.value.getMonth())
const displayYear  = computed(() => _hdrDate.value.getFullYear())

/** When date is picked inline, store as YYYY-MM-DD */
function onDatePicked(val) {
  if (!val) {
    selectedDate.value = ''
    return
  }
  selectedDate.value = ymd(val)
}

/** Time formatting for preview + emission */
const formattedTime = computed(() => {
  if (!timeModel) return ''
  const t = timeModel.value
  if (!t) return ''
  return `${pad(t.hours ?? 0)}:${pad(t.minutes ?? 0)}`
})

function clearTime() {
  timeModel.value = null
}

/** Emit add-slot { date, time } in 24h HH:mm */
function emitAdd() {
  if (!selectedDate.value || !timeModel.value) return
  const { hours = 0, minutes = 0 } = timeModel.value
  const time = `${pad(hours)}:${pad(minutes)}`
  emit('add-slot', { date: selectedDate.value, time })
}
</script>

<style scoped>

</style>
