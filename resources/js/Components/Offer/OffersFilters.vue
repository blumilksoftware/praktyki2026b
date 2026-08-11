<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMapboxGeocoding } from '@/Composables/useMapboxGeocoding'
import DynamicMultiSelect from '@/Components/Common/DynamicMultiSelect.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'

const props = defineProps({
  studyFields: { type: Array, default: () => [] },
})

const emit = defineEmits(['reset'])

const filters = defineModel({ type: Object, required: true })

const { t } = useI18n()
const { cityOptions, fetchSuggestions: fetchCitySuggestions } = useMapboxGeocoding()

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

      <DynamicMultiSelect
        id="offers-filter-cities"
        v-model="filters.cities"
        :label="t('student.offers.filters.city')"
        :placeholder="t('student.offers.filters.city')"
        :options="cityOptions"
        :allow-custom="true"
        remote
        @search="fetchCitySuggestions"
      />

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
