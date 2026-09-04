<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconBriefcase2Filled } from '@tabler/icons-vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()

const props = defineProps({
  title: { type: String, default: null },
  description: { type: String, default: null },
  spots: { type: Number, default: null },
  offers: { type: Array, default: () => [] },
})

const limit = 4
const visibleCount = ref(limit)

const visibleOffers = computed(() => props.offers.slice(0, visibleCount.value))
const hasMoreOffers = computed(() => props.offers.length > visibleCount.value)

const loadMore = () => {
  visibleCount.value += limit
}

const viewOffer = (offerId) => {
  const url = ROUTES.OFFER_SHOW.replace('{offer}', String(offerId))
  router.get(url)
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
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold tracking-wide text-green-800">
                {{ t('company.offers.index.status.published') }}
              </span>
              <div class="min-w-0 flex-1">
                <h3 class="line-clamp-2 font-semibold text-text text-base">
                  {{ offer.title }}
                </h3>
              </div>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-3 text-additional text-sm">
              <span class="inline-flex items-center gap-1">
                <IconBriefcase2Filled class="h-4 w-4" aria-hidden="true" />
                {{ t('profiles.spots') }}: {{ t('company.offers.index.spotsCount', { remaining: offer.remaining_spots ?? offer.spots, total: offer.spots }) }}
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

      <BaseButton
        v-if="hasMoreOffers"
        variant="secondary"
        class="w-full justify-center mt-4"
        @click="loadMore"
      >
        {{ t('buttons.load_more') }}
      </BaseButton>
    </div>

    <div v-else class="text-additional italic text-sm">
      {{ t('profiles.noOffers') }}
    </div>
  </div>
</template>
