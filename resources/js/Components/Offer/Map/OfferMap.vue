<script setup>
import { toRef } from 'vue'
import { useI18n } from 'vue-i18n'
import { useOffersMap } from '@/Composables/useOffersMap.js'
import SelectedCityBadge from '@/Components/Offer/Map/SelectedCityBadge.vue'
import CityOffersList from '@/Components/Offer/Map/CityOffersList.vue'
import 'mapbox-gl/dist/mapbox-gl.css'

const props = defineProps({
  offers: { type: Array, default: () => [] },
  hasCv: { type: Boolean, default: true },
  guest: { type: Boolean, default: false },
  mapboxToken: { type: String, default: '' },
  initialOfferId: { type: [Number, String], default: null },
})

const { t } = useI18n()

const offersRef = toRef(props, 'offers')
const initialOfferIdRef = toRef(props, 'initialOfferId')

const {
  mapContainer,
  selectedCity,
  selectedOfferId,
  selectedCityOffers,
  clearSelection,
} = useOffersMap(offersRef, props.mapboxToken, initialOfferIdRef)
</script>

<template>
  <div class="space-y-4 py-4 sm:px-4 sm:py-6">
    <div class="relative w-full h-[450px] rounded-3xl overflow-hidden border border-border shadow-sm">
      <div ref="mapContainer" class="w-full h-full" />

      <SelectedCityBadge
        v-if="selectedCity"
        :city="selectedCity"
        :count="selectedCityOffers.length"
        @clear="clearSelection"
      />
    </div>

    <CityOffersList
      v-if="selectedCity"
      :city="selectedCity"
      :offers="selectedCityOffers"
      :selected-offer-id="selectedOfferId"
      :has-cv="hasCv"
      :guest="guest"
    />

    <div v-else class="text-center py-6 text-additional text-sm bg-background/50 rounded-2xl border border-dashed border-border">
      {{ t('student.offers.map.selectPinHint') }}
    </div>
  </div>
</template>
