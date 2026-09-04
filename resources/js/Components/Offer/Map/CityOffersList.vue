<script setup>
import { useI18n } from 'vue-i18n'
import OfferCard from '@/Components/Offer/OfferCard.vue'

defineProps({
  city: { type: String, required: true },
  offers: { type: Array, default: () => [] },
  selectedOfferId: { type: [Number, String], default: null },
  hasCv: { type: Boolean, default: true },
  guest: { type: Boolean, default: false },
  canApply: { type: Boolean, default: false },
})

const { t } = useI18n()
</script>

<template>
  <div class="space-y-1 py-2 sm:space-y-2 sm:px-2 sm:py-3">
    <div
      v-for="offer in offers"
      :key="offer.id"
      :data-offer-id="offer.id"
      :class="[
        'rounded-2xl transition',
        selectedOfferId === offer.id ? 'ring-2 ring-primary ring-offset-2' : '',
      ]"
    >
      <OfferCard
        :offer="offer"
        :has-cv="hasCv"
        :guest="guest"
        :can-apply="canApply"
      />
    </div>
  </div>
</template>
