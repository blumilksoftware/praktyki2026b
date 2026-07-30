<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import SearchFilterPanel from '@/Components/Offers/SearchFilterPanel.vue'
import Pagination from '@/Components/Common/Pagination.vue'
import OffersList from '@/Components/Offer/OffersList.vue'

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

  <BaseLayout>
    <div class="bg-background py-6 min-h-screen">
      <header class="mx-auto mb-6 max-w-7xl px-4 sm:px-6 lg:px-8">
        <h1 class="font-semibold text-text text-2xl tracking-tight">
          {{ t('offers.search.title') }}
        </h1>
        <p class="mt-1 text-additional text-sm">
          {{ t('offers.search.subtitle') }}
        </p>
      </header>

      <div class="flex flex-col lg:items-start gap-6 lg:grid lg:grid-cols-[290px_minmax(0,1fr)] mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SearchFilterPanel
          :filters="filters"
          :study-field-options="studyFields"
        />

        <section
          aria-labelledby="offer-search-results-heading"
          class="-mx-4 sm:mx-0 sm:rounded-3xl sm:border sm:border-border/80 sm:bg-white/90 sm:p-6 sm:shadow-[0_14px_40px_rgba(11,26,48,0.08)] sm:backdrop-blur-sm"
        >
          <div class="flex justify-between items-end gap-4 mb-5 px-4 pt-5 sm:px-0 sm:pt-0">
            <div>
              <p class="font-medium text-additional text-sm">{{ t('offers.search.resultsTitle') }}</p>
              <h2
                id="offer-search-results-heading"
                class="mt-1 font-semibold text-text text-3xl tracking-tight"
                aria-live="polite"
              >
                {{ t('offers.search.resultsCount', { count: offers.data?.length ?? 0 }) }}
              </h2>
            </div>
          </div>

          <OffersList
            :offers="offers.data"
            guest
            :empty-description="t('offers.search.empty')"
          />

          <Pagination
            v-if="offers.meta?.last_page > 1"
            class="mt-6"
            :meta="offers"
          />
        </section>
      </div>
    </div>
  </BaseLayout>
</template>
