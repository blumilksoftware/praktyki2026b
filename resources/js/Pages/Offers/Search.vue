<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import SearchFilterPanel from '@/Components/Offers/SearchFilterPanel.vue'
import Pagination from '@/Components/Common/Pagination.vue'

defineProps({
  offers: { type: Object, required: true },
  mapPoints: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  studyFields: { type: Array, default: () => [] },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="t('offers.search.title')" />

  <BaseLayout
    :minimal-header="true"
    :show-background="false"
    :nav-items="[]"
  >
    <header class="mb-6">
      <h1 class="font-semibold text-text text-2xl">
        {{ t('offers.search.title') }}
      </h1>
      <p class="mt-1 text-additional text-sm">
        {{ t('offers.search.subtitle') }}
      </p>
    </header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(16rem,20rem)_1fr]">
      <SearchFilterPanel
        :filters="filters"
        :study-field-options="studyFields"
      />

      <section
        aria-labelledby="offer-search-results-heading"
        class="rounded-2xl border border-border bg-white p-5 shadow-sm"
      >
        <h2
          id="offer-search-results-heading"
          class="font-medium text-text text-lg"
        >
          {{ t('offers.search.resultsTitle') }}
        </h2>

        <p
          v-if="!offers.data?.length"
          class="mt-4 text-additional text-sm"
          role="status"
        >
          {{ t('offers.search.empty') }}
        </p>
        <p
          v-else
          class="mt-4 text-additional text-sm"
          role="status"
        >
          {{ t('offers.search.resultsCount', { count: offers.data.length }) }}
        </p>

        <Pagination
          v-if="offers.meta?.last_page > 1"
          class="mt-6"
          :meta="offers"
        />
      </section>
    </div>
  </BaseLayout>
</template>
