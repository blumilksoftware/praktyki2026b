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

    <div v-if="offers && offers.length > 0" class="flex flex-col gap-4">
      <div 
        v-for="offer in visibleOffers" 
        :key="offer.id"
        class="border border-border rounded-2xl p-5 hover:border-primary transition-colors bg-white shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-5 sm:gap-6 group cursor-pointer"
      >
        <div class="flex-1 flex items-start gap-4 sm:gap-5 w-full">
          <div class="flex items-center justify-center bg-gray-50 border border-gray-100 rounded-xl p-3 shrink-0 text-gray-700">
            <IconBriefcase2Filled class="w-6 h-6" />
          </div>

          <div class="flex flex-col pt-0.5">
            <h3 class="font-semibold text-lg text-text group-hover:text-primary transition-colors">
              {{ offer.title }}
            </h3>
            <p class="text-gray-500 text-sm mt-1.5 line-clamp-2">
              {{ offer.description }}
            </p>
          </div>
        </div>

        <div class="shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
          <BaseButton
            variant="primary"
            class="px-6 py-2.5 text-sm font-medium shadow-sm w-full sm:w-auto"
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
