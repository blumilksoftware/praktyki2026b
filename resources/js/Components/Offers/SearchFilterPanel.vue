<script setup>
import { computed, toRef } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconFilter, IconX } from '@tabler/icons-vue'
import DynamicMultiSelect from '@/Components/Common/DynamicMultiSelect.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import { useOfferSearchFilters } from '@/composables/useOfferSearchFilters'

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
    class="rounded-2xl border border-border bg-white p-5 shadow-sm"
    aria-labelledby="offer-search-filters-heading"
  >
    <div class="flex items-center justify-between gap-3">
      <h2
        id="offer-search-filters-heading"
        class="flex items-center gap-2 font-semibold text-text text-base"
      >
        <IconFilter class="h-5 w-5 text-primary" aria-hidden="true" />
        {{ t('offers.filters.title') }}
      </h2>
      <button
        v-if="hasActiveFilters()"
        type="button"
        class="inline-flex cursor-pointer items-center gap-1 text-additional text-sm transition hover:text-text"
        :aria-label="t('offers.filters.clearAll')"
        @click="clearFilters"
      >
        <IconX class="h-4 w-4" aria-hidden="true" />
        {{ t('offers.filters.clearAll') }}
      </button>
    </div>

    <div class="mt-5 space-y-6">
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
