<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconBriefcase2Filled } from '@tabler/icons-vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()

const props = defineProps({
  id: { type: [String, Number], default: null },
  title: { type: String, default: null },
  description: { type: String, default: null },
  spots: { type: Number, default: null },
  offers: { type: Array, default: () => [] },
})

const visibleOffers = computed(() => {
  return props.offers.slice(0, 4)
})

const hasMoreOffers = computed(() => {
  return props.offers.length > 4
})

const viewOffer = (offerId) => {
  const url = ROUTES.OFFER_SHOW.replace('{offer}', String(offerId))
  router.get(url)
}

const viewAllOffers = () => {
  router.get(ROUTES.COMPANY_OFFERS, { company_id: props.id })
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <h2 class="text-xl font-bold text-text">{{ t('profiles.currentOffers') }}</h2>

    <div v-if="offers && offers.length > 0" class="flex flex-col gap-3">
      <div
        v-for="offer in visibleOffers"
        :key="offer.id"
        class="rounded-2xl border border-border bg-white p-4 shadow-sm transition-colors hover:border-primary/40 sm:p-5"
      >
        <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold tracking-wide text-green-700">
                {{ t('company.offers.index.status.published') }}
              </span>
              <h3 class="min-w-0 truncate font-semibold text-text text-base">
                {{ offer.title }}
              </h3>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-3 text-additional text-sm">
              <span class="inline-flex items-center gap-1">
                <IconBriefcase2Filled class="h-4 w-4" aria-hidden="true" />
                {{ t('profiles.spots') }}: {{ offer.spots }}
              </span>
            </div>
            <p v-if="offer.description" class="mt-2 line-clamp-2 text-additional text-sm">
              {{ offer.description }}
            </p>
          </div>

          <BaseButton
            variant="primary"
            class="w-full shrink-0 px-6 py-2.5 text-sm font-medium shadow-sm sm:w-auto"
            @click="viewOffer(offer.id)"
          >
            {{ t('buttons.view') }}
          </BaseButton>
        </div>
      </div>

      <div v-if="hasMoreOffers" class="flex justify-center mt-2">
        <BaseButton
          variant="secondary"
          class="px-8 py-2.5 text-sm font-medium shadow-sm w-full sm:w-auto bg-gray-50 hover:bg-gray-100 text-secondary border border-gray-200"
          @click="viewAllOffers"
        >
          {{ t('buttons.showAll') }}
        </BaseButton>
      </div>
    </div>

    <div v-else class="text-gray-400 italic text-sm">
      {{ t('profiles.noOffers') }}
    </div>
  </div>
</template>
