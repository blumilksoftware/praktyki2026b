<script setup>
import { toRef, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useOffersMap } from '@/Composables/useOffersMap.js'
import SelectedCityBadge from '@/Components/Offer/Map/SelectedCityBadge.vue'
import CityOffersList from '@/Components/Offer/Map/CityOffersList.vue'
import OffersList from '@/Components/Offer/OffersList.vue'
import 'mapbox-gl/dist/mapbox-gl.css'

const props = defineProps({
  offers: { type: Array, default: () => [] },
  listOffers: { type: Object, required: true },
  hasCv: { type: Boolean, default: true },
  guest: { type: Boolean, default: false },
  canApply: { type: Boolean, default: false },
  mapboxToken: { type: String, default: '' },
  initialOfferId: { type: [Number, String], default: null },
  radiusKm: { type: [Number, String], default: null },
  latitude: { type: [Number, String], default: null },
  longitude: { type: [Number, String], default: null },
  selectedCityLabel: { type: String, default: '' },
})

const emit = defineEmits(['city-selected', 'clear'])
const { t } = useI18n()

const offersRef = toRef(props, 'offers')
const initialOfferIdRef = toRef(props, 'initialOfferId')
const radiusKmRef = toRef(props, 'radiusKm')
const radiusCenterRef = computed(() => (
  props.latitude != null && props.longitude != null
    ? { latitude: Number(props.latitude), longitude: Number(props.longitude) }
    : null
))
const selectedCityLabelRef = toRef(props, 'selectedCityLabel')

const {
  mapContainer,
  selectedCity,
  selectedOfferId,
  selectedCityOffers,
  selectedCityOffersCount,
  isOffersMode,
  resetFilters,
} = useOffersMap(
  offersRef,
  props.mapboxToken,
  initialOfferIdRef,
  radiusKmRef,
  radiusCenterRef,
  undefined,
  {
    onCitySelect: (city) => emit('city-selected', city),
    onClear: () => emit('clear'),
    selectedCityLabel: selectedCityLabelRef,
  },
)

defineExpose({ resetFilters })
</script>

<template>
  <div class="space-y-4 py-4 sm:px-4 sm:py-6">
    <div class="relative w-full h-[450px] rounded-3xl overflow-hidden border border-border shadow-sm">
      <div ref="mapContainer" class="w-full h-full" />
      <div class="pointer-events-none absolute inset-x-0 bottom-0 h-12 bg-gradient-to-t from-black/10 to-transparent" />
      <SelectedCityBadge
        v-if="selectedCity"
        :city="selectedCity"
        :count="selectedCityOffersCount"
        @clear="resetFilters"
      />
    </div>

    <div class="mt-6">
      <h3 class="font-semibold text-text text-lg mb-4">
        {{ selectedCity === null ? t('student.offers.map.cityOffersTitleFallback') : t('student.offers.map.cityOffersTitle', { city: selectedCity }) }}
      </h3>
    </div>

    <CityOffersList
      v-if="selectedCity"
      :city="selectedCity"
      :offers="selectedCityOffers"
      :selected-offer-id="selectedOfferId"
      :has-cv="hasCv"
      :guest="guest"
      :can-apply="canApply"
    />

    <OffersList
      v-else
      :offers="listOffers"
      :has-cv="hasCv"
      :guest="guest"
      :can-apply="canApply"
    />
  </div>
</template>
