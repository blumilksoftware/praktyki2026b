<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMapboxGeocoding } from '@/Composables/useMapboxGeocoding'
import DynamicMultiSelect from '@/Components/Common/DynamicMultiSelect.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'

const props = defineProps({
  studyFields: { type: Array, default: () => [] },
  radiusOptions: { type: Array, default: () => [10, 25, 50, 100] },
})

const emit = defineEmits(['reset'])

const filters = defineModel({ type: Object, required: true })

const { t } = useI18n()
const { cityOptions, cityCoordinates, fetchSuggestions: fetchCitySuggestions } = useMapboxGeocoding()

const workModes = ['remote', 'hybrid', 'onSite']
const workModeLabel = (mode) => t(`student.workModes.${mode}`)

const studyFieldLabels = computed(() => props.studyFields.map((f) => f.label))
const studyFieldLabelToValue = (label) => props.studyFields.find((f) => f.label === label)?.value

function toggleWorkMode(mode) {
  const index = filters.value.workModes.indexOf(mode)
  if (index === -1) {
    filters.value.workModes.push(mode)
  } else {
    filters.value.workModes.splice(index, 1)
  }
}

const clearRadiusFilter = () => {
  filters.value.radiusCityLabel = ''
  filters.value.latitude = null
  filters.value.longitude = null
  filters.value.radiusKm = null
}

const handleCitySelection = (selectedLabels) => {
  filters.value.cities = selectedLabels

  if (selectedLabels.length > 0) {
    const firstName = selectedLabels[0]
    const coords = cityCoordinates.value.get(firstName)
    if (coords) {
      filters.value.radiusCityLabel = firstName
      filters.value.latitude = coords.latitude
      filters.value.longitude = coords.longitude
    } else {
      clearRadiusFilter()
    }
  } else {
    clearRadiusFilter()
  }
}

const isRadiusOpen = ref(false)
const radiusDropdownRef = ref(null)

const radiusLabel = computed(() => (
  filters.value.radiusKm
    ? t('student.offers.filters.radius.option', { km: filters.value.radiusKm })
    : t('student.offers.filters.radius.placeholder')
))

function toggleRadiusDropdown() {
  isRadiusOpen.value = !isRadiusOpen.value
}

function selectRadius(km) {
  filters.value.radiusKm = filters.value.radiusKm === km ? null : km
  isRadiusOpen.value = false
}

function clearRadius(event) {
  event.stopPropagation()
  filters.value.radiusKm = null
  isRadiusOpen.value = false
}

function handleClickOutside(event) {
  if (radiusDropdownRef.value && !radiusDropdownRef.value.contains(event.target)) {
    isRadiusOpen.value = false
  }
}

function handleKeydown(event) {
  if (event.key === 'Escape') {
    isRadiusOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})

const dateRangeError = computed(() => {
  if (filters.value.dateFrom && filters.value.dateTo && filters.value.dateTo < filters.value.dateFrom) {
    return 'student.offers.filters.dateRangeInvalid'
  }
  return null
})

defineExpose({ studyFieldLabelToValue })
</script>

<template>
  <aside
    aria-labelledby="offers-filters-heading"
    class="lg:top-6 lg:sticky bg-white/90 shadow-[0_14px_40px_rgba(11,26,48,0.08)] backdrop-blur-sm p-5 border border-border/80 rounded-3xl"
  >
    <div class="flex justify-between items-start gap-3">
      <div>
        <p class="font-medium text-additional text-sm">{{ t('student.offers.filters.kicker') }}</p>
        <h2 id="offers-filters-heading" class="mt-2 font-semibold text-text text-2xl tracking-tight">
          {{ t('student.offers.filters.title') }}
        </h2>
      </div>
      <button
        type="button"
        class="hover:bg-background px-3 py-1.5 border border-border cursor-pointer hover:border-primary/40 rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 font-semibold text-text text-xs transition"
        :aria-label="t('student.offers.filters.reset')"
        @click="emit('reset')"
      >
        {{ t('student.offers.filters.reset') }}
      </button>
    </div>

    <div class="space-y-4 mt-6">
      <label class="block" for="offers-filter-search">
        <span class="block mb-2 font-medium text-text text-sm">{{ t('student.offers.filters.search') }}</span>
        <input
          id="offers-filter-search"
          v-model="filters.search"
          type="search"
          :placeholder="t('student.offers.filters.city')"
          class="bg-background focus:bg-white px-4 py-3 border border-border focus:border-primary/50 rounded-2xl outline-none focus:ring-2 focus:ring-primary/20 w-full text-text placeholder:text-additional text-sm transition"
        >
      </label>

      <DynamicMultiSelect
        id="offers-filter-study-fields"
        v-model="filters.studyFieldLabels"
        :label="t('student.offers.filters.studyFields.label')"
        :placeholder="t('student.offers.filters.studyFields.placeholder')"
        :options="studyFieldLabels"
        :allow-custom="false"
      />

      <div>
        <DynamicMultiSelect
          id="offers-filter-cities"
          :model-value="filters.cities"
          :label="t('student.offers.filters.city')"
          :placeholder="t('student.offers.filters.city')"
          :options="cityOptions"
          :allow-custom="true"
          remote
          @update:model-value="handleCitySelection"
          @search="fetchCitySuggestions"
        />

        <div v-if="filters.latitude && filters.longitude" ref="radiusDropdownRef" class="relative mt-3">
          <span class="mb-2 block font-medium text-text text-xs text-additional">
            {{ t('student.offers.filters.radius.label') }}
          </span>

          <button
            type="button"
            class="flex w-full items-center justify-between gap-2 rounded-2xl border border-border bg-background px-4 py-2.5 text-sm font-medium transition hover:border-primary/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 cursor-pointer"
            aria-haspopup="listbox"
            :aria-expanded="isRadiusOpen"
            @click="toggleRadiusDropdown"
          >
            <span :class="filters.radiusKm ? 'text-text' : 'text-additional'">{{ radiusLabel }}</span>
            <span class="flex items-center gap-1">
              <svg
                v-if="filters.radiusKm"
                class="h-4 w-4 text-additional transition hover:text-text"
                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                role="button"
                :aria-label="t('student.offers.filters.radius.clear')"
                @click="clearRadius"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <svg
                class="h-4 w-4 text-additional transition"
                :class="isRadiusOpen ? 'rotate-180' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </span>
          </button>

          <ul
            v-if="isRadiusOpen"
            role="listbox"
            class="absolute z-10 mt-2 w-full overflow-hidden rounded-2xl border border-border bg-white shadow-[0_14px_40px_rgba(11,26,48,0.12)]"
          >
            <li
              v-for="km in radiusOptions"
              :key="km"
              role="option"
              :aria-selected="filters.radiusKm === km"
              class="flex cursor-pointer items-center justify-between px-4 py-2.5 text-sm transition hover:bg-background"
              :class="filters.radiusKm === km ? 'font-semibold text-primary' : 'text-text'"
              @click="selectRadius(km)"
            >
              {{ t('student.offers.filters.radius.option', { km }) }}
              <svg v-if="filters.radiusKm === km" class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </li>
          </ul>
        </div>
      </div>

      <div>
        <span class="mb-2 block font-medium text-text text-sm">{{ t('student.offers.filters.workMode') }}</span>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="mode in workModes"
            :key="mode"
            type="button"
            class="rounded-full border px-3 py-1.5 text-sm font-medium transition cursor-pointer"
            :class="filters.workModes.includes(mode)
              ? 'border-primary bg-primary/10 text-primary'
              : 'border-border text-additional hover:border-primary/40 hover:text-text'"
            :aria-pressed="filters.workModes.includes(mode)"
            @click="toggleWorkMode(mode)"
          >
            {{ workModeLabel(mode) }}
          </button>
        </div>
      </div>

      <div>
        <p class="mb-2 font-medium text-text text-sm">
          {{ t('student.offers.filters.dateRange.label') }}
        </p>
        <div class="grid grid-cols-1 gap-3">
          <BaseInput
            id="offers-filter-date-from"
            v-model="filters.dateFrom"
            type="date"
            stacked
            :label="t('student.offers.filters.dateRange.from')"
          />
          <BaseInput
            id="offers-filter-date-to"
            v-model="filters.dateTo"
            type="date"
            stacked
            :label="t('student.offers.filters.dateRange.to')"
          />
        </div>
        <p v-if="dateRangeError" class="mt-2 text-error text-sm" role="alert">
          {{ t(dateRangeError) }}
        </p>
      </div>
    </div>
  </aside>
</template>
