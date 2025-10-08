<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- calender -->
    <div class="bg-white border rounded-lg p-4">
      <div class="mb-3 font-medium">
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
        :disabled-dates="disabledDatesParsed"
        :markers="dayMarkers" 
        @update:model-value="onDatePicked"
      />
    </div>

    <!-- time -->
    <div class="bg-white border rounded-lg p-4">
      <div class="space-y-4">
        <div class="text-sm text-gray-600">
          Selected date:
          <span class="font-medium" v-if="selectedDate">{{ selectedDate }}</span>
          <span v-else class="italic">none</span>
        </div>

        <!-- time picker -->
        <div>
          <label class="text-sm block mb-1">Time</label>
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
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
            :disabled="!selectedDate || timeModel === null"
            :title="!selectedDate ? 'Pick a date first' : (timeModel===null ? 'Pick a time' : '')"
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

        <div v-if="selectedDate" class="mt-2">
          <div class="text-sm font-medium mb-2">
            Times on {{ selectedDate }}
          </div>

          <div v-if="daySlots.length" class="space-y-2 max-h-48 overflow-auto">
            <div
              v-for="slot in daySlots"
              :key="slot.time"
              class="flex items-center justify-between border rounded px-3 py-2 bg-white"
            >
              <span class="text-sm">{{ slot.time }}</span>
              <button
                type="button"
                class="text-red-600 text-sm hover:underline"
                @click="emitRemove(slot.time)"
              >
                Remove
              </button>
            </div>
          </div>

          <p v-else class="text-sm text-gray-500 italic">
            No times added for this date yet.
          </p>
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
  weekStartsOn: { type: Number, default: 1 },
  step: { type: Number, default: 60 },
  minDate: { type: String, default: null },
  disabledDates: { type: Array, default: () => [] },
  slots: { type: Array, default: () => [] }, // [{ date:'YYYY-MM-DD', time:'HH:mm' }]
})

const emit = defineEmits(['add-slot', 'remove-slot', 'update:slots',])

// states and refs
const selectedDate = ref('')
const dateModel = ref(null)
const timeModel = ref(null)
const tp = ref(null)

// constants
const monthNames = [
  'January','February','March','April','May','June',
  'July','August','September','October','November','December'
]

// formats
function pad(n) { return n < 10 ? `0${n}` : `${n}` }
function ymd(d) { return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}` }


// makes minDate and disabledDates to actual date objects (midnight)
const todayISO = computed(() => {
  const d = new Date()
  const pad = n => (n < 10 ? `0${n}` : n)
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
})

const disabledDatesParsed = computed(() =>
  (props.disabledDates || []).map(d => new Date(d + 'T00:00:00'))
)

// minute increment 
const minuteStep = computed(() => {
  const m = Math.max(1, Math.round(props.step / 60))
  return Math.min(60, m)
})

const daySlots = computed(() => {
  if (!selectedDate.value) return []
  return props.slots
    .filter(s => s.date === selectedDate.value)
    .slice()
    .sort((a, b) => a.time.localeCompare(b.time))
})

// Keep a readable month/year for header (from current dateModel or today)
const _hdrDate = computed(() => dateModel.value ?? new Date())
const displayMonth = computed(() => _hdrDate.value.getMonth())
const displayYear  = computed(() => _hdrDate.value.getFullYear())

// Calendar markers for dates that have at least one time
const dayMarkers = computed(() => {
  const uniq = [...new Set((props.slots || []).map(s => s.date))]
  return uniq.map(d => ({
    date: new Date(d + 'T00:00:00'),
    type: 'dot',
    color: '#c2007e',
  }))
})


/* =========================
   Core Logic (pure helpers)
   ========================= */
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


/* =========================
   Event Handlers
   ========================= */
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


/* =========================
   Keyboard Handling (Enter-to-select)
   ========================= */
function onKeydown(e) {
  if (e.key !== 'Enter') return

  // If the user is typing in the text input, let the component handle it
  const el = e.target
  if (el && el.classList && el.classList.contains('dp__input')) return

  e.preventDefault()
  // Apply the currently selected hour/minute (same as clicking "Select")
  if (tp.value && typeof tp.value.selectDate === 'function') tp.value.selectDate()
  if (tp.value && typeof tp.value.closeMenu === 'function') tp.value.closeMenu()
}

function bindEnter()   { document.addEventListener('keydown', onKeydown, true) }
function unbindEnter() { document.removeEventListener('keydown', onKeydown, true) }


/* =========================
   Lifecycle
   ========================= */
onBeforeUnmount(() => unbindEnter())
</script>

<style scoped>
/* this makes the time popup normal size */
:deep(.dp--overlay-relative) {
  max-height: fit-content !important;   
  height: auto !important;       
  overflow: hidden !important;
}

:deep(.dp__time_col) {
  max-height: fit-content !important;
}

:deep(.dp__time_display){
  font-size: 24px;
}

:deep(.dp__time_col){
  padding-left: 8px;
  padding-right: 8px;
}

:deep(.dp__selection_preview) {
  display: none !important;
}
</style>
