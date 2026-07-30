<script setup>
import { computed, toRef } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconFilter } from '@tabler/icons-vue'
import DynamicMultiSelect from '@/Components/Common/DynamicMultiSelect.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import { useOfferSearchFilters } from '@/Composables/useOfferSearchFilters.ts'

const props = defineProps({
  filters: { type: Object, required: true },
  studyFieldOptions: { type: Array, default: () => [] },
})

const { t } = useI18n()

const {
  localFilters,
  dateRangeError,
  updateStudyFieldIds,
  selectWorkMode,
  clearFilters,
  hasActiveFilters,
} = useOfferSearchFilters(toRef(props, 'filters'))

const workModeOptions = computed(() => [
  { value: null, labelKey: 'offers.filters.workMode.all' },
  { value: 'onSite', labelKey: 'offers.filters.workMode.onSite' },
  { value: 'hybrid', labelKey: 'offers.filters.workMode.hybrid' },
  { value: 'remote', labelKey: 'offers.filters.workMode.remote' },
])
</script>

<template>
  <aside
    class="lg:top-6 lg:sticky bg-white/90 shadow-[0_14px_40px_rgba(11,26,48,0.08)] backdrop-blur-sm p-5 border border-border/80 rounded-3xl"
    aria-labelledby="offer-search-filters-heading"
  >
    <div class="flex justify-between items-start gap-3">
      <div>
        <p class="flex items-center gap-1.5 font-semibold text-additional text-xs uppercase tracking-[0.24em]">
          <IconFilter class="h-3.5 w-3.5" aria-hidden="true" />
          {{ t('offers.filters.kicker') }}
        </p>
        <h2
          id="offer-search-filters-heading"
          class="mt-2 font-semibold text-text text-2xl tracking-tight"
        >
          {{ t('offers.filters.title') }}
        </h2>
      </div>
      <button
        v-if="hasActiveFilters()"
        type="button"
        class="hover:bg-background px-3 py-1.5 border border-border cursor-pointer hover:border-primary/40 rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 font-semibold text-text text-xs transition"
        :aria-label="t('offers.filters.clearAll')"
        @click="clearFilters"
      >
        {{ t('offers.filters.clearAll') }}
      </button>
    </div>

    <div class="mt-6 space-y-6">
      <DynamicMultiSelect
        id="offer-search-study-fields"
        :model-value="localFilters.studyFieldIds"
        :label="t('offers.filters.studyFields.label')"
        :placeholder="t('offers.filters.studyFields.placeholder')"
        :options="studyFieldOptions"
        :allow-custom="false"
        @update:model-value="updateStudyFieldIds"
      />

      <fieldset>
        <legend class="mb-2 block text-additional text-sm">
          {{ t('offers.filters.workMode.label') }}
        </legend>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="option in workModeOptions"
            :key="option.labelKey"
            type="button"
            class="cursor-pointer rounded-lg border px-3 py-2 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
            :class="localFilters.workMode === option.value
              ? 'border-primary bg-primary text-white'
              : 'border-border bg-white text-text hover:border-primary/40'"
            :aria-pressed="localFilters.workMode === option.value"
            @click="selectWorkMode(option.value)"
          >
            {{ t(option.labelKey) }}
          </button>
        </div>
      </fieldset>

      <div>
        <p class="mb-2 text-additional text-sm">
          {{ t('offers.filters.dateRange.label') }}
        </p>
        <div class="grid grid-cols-1 gap-3">
          <BaseInput
            id="offer-search-date-from"
            v-model="localFilters.dateFrom"
            type="date"
            stacked
            :label="t('offers.filters.dateRange.from')"
          />
          <BaseInput
            id="offer-search-date-to"
            v-model="localFilters.dateTo"
            type="date"
            stacked
            :label="t('offers.filters.dateRange.to')"
          />
        </div>
        <p
          v-if="dateRangeError"
          class="mt-2 text-error text-sm"
          role="alert"
        >
          {{ t(dateRangeError) }}
        </p>
      </div>
    </div>
  </aside>
</template>
