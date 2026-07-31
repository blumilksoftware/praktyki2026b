<script setup>
import { ref, onMounted, onUnmounted, watch, computed, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import mapboxgl from 'mapbox-gl'
import 'mapbox-gl/dist/mapbox-gl.css'
import OfferCard from '@/Components/Offer/OfferCard.vue'

const props = defineProps({
  offers: { type: Array, default: () => [] },
  hasCv: { type: Boolean, default: true },
  guest: { type: Boolean, default: false },
  mapboxToken: { type: String, default: '' },
})

const { t } = useI18n()

const mapContainer = ref(null)
let map = null
let markers = []

const selectedCity = ref(null)

const FALLBACK_CITY_COORDINATES = {
  'Warsaw': [21.0122, 52.2297],
  'Krakow': [19.9450, 50.0647],
  'Wroclaw': [17.0385, 51.1100],
  'Poznan': [16.9299, 52.4064],
  'Gdansk': [18.6466, 54.3520],
  'Lodz': [19.4560, 51.7592],
  'Katowice': [19.0238, 50.2649],
}

const groupedOffers = computed(() => {
  const groups = {}
  props.offers.forEach((offer) => {
    if (!offer.city) return
    if (!groups[offer.city]) {
      groups[offer.city] = []
    }
    groups[offer.city].push(offer)
  })
  return groups
})

const selectedCityOffers = computed(() => {
  if (!selectedCity.value) return []
  return groupedOffers.value[selectedCity.value] || []
})

const getOfferCoordinates = (offer) => {
  if (
    offer.longitude !== null && offer.longitude !== undefined &&
    offer.latitude !== null && offer.latitude !== undefined &&
    !isNaN(Number(offer.longitude)) && !isNaN(Number(offer.latitude))
  ) {
    return [Number(offer.longitude), Number(offer.latitude)]
  }
  return null
}

const getCityCoordinates = (cityName, cityOffers) => {
  const validOffer = cityOffers.find((o) => getOfferCoordinates(o) !== null)
  if (validOffer) {
    return getOfferCoordinates(validOffer)
  }

  if (FALLBACK_CITY_COORDINATES[cityName]) {
    return FALLBACK_CITY_COORDINATES[cityName]
  }

  let hash = 0
  for (let i = 0; i < cityName.length; i++) {
    hash = cityName.charCodeAt(i) + ((hash << 5) - hash)
  }
  const lng = 19.0 + (Math.abs(hash) % 100) / 50
  const lat = 52.0 + (Math.abs(hash >> 2) % 100) / 50
  return [lng, lat]
}

const clearMarkers = () => {
  markers.forEach((marker) => marker.remove())
  markers = []
}

const renderMarkers = (fitBounds = true) => {
  if (!map) return
  clearMarkers()

  const bounds = new mapboxgl.LngLatBounds()
  let hasValidBounds = false

  Object.entries(groupedOffers.value).forEach(([cityName, cityOffers]) => {
    const count = cityOffers.length
    if (count === 0) return

    const coords = getCityCoordinates(cityName, cityOffers)
    bounds.extend(coords)
    hasValidBounds = true

    const el = document.createElement('button')
    el.type = 'button'
    el.className = 'custom-map-pin'
    el.setAttribute('aria-label', `${cityName}: ${count}`)

    const isSelected = selectedCity.value === cityName
    el.innerHTML = `
      <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full shadow-lg border text-xs font-bold transition transform hover:scale-105 cursor-pointer ${
      isSelected
        ? 'bg-primary text-white border-primary ring-4 ring-primary/20 scale-110'
        : 'bg-white text-text border-border hover:border-primary/50'
    }">
        <svg class="w-3.5 h-3.5 text-primary ${isSelected ? 'text-white' : ''}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span>${cityName}</span>
        <span class="ml-0.5 px-1.5 py-0.5 rounded-full text-[10px] ${
      isSelected ? 'bg-white/20 text-white' : 'bg-background text-additional'
    }">${count}</span>
      </div>
    `

    el.addEventListener('click', () => {
      flyToCity(cityName, cityOffers, coords)
      selectedCity.value = cityName
    })

    const marker = new mapboxgl.Marker({ element: el })
      .setLngLat(coords)
      .addTo(map)
    markers.push(marker)
  })

  if (fitBounds && hasValidBounds && markers.length > 0) {
    map.fitBounds(bounds, { padding: 80, maxZoom: 10 })
  }
}

const flyToCity = (cityName, cityOffers, fallbackCoords) => {
  if (cityOffers.length <= 1) {
    map.flyTo({ center: fallbackCoords, zoom: 12, speed: 1.2 })
    return
  }

  const cityBounds = new mapboxgl.LngLatBounds()
  let hasOfferBounds = false

  cityOffers.forEach((offer) => {
    const offerCoords = getOfferCoordinates(offer)
    if (offerCoords) {
      cityBounds.extend(offerCoords)
      hasOfferBounds = true
    }
  })

  if (hasOfferBounds && !cityBounds.isEmpty()) {
    map.fitBounds(cityBounds, {
      padding: 80,
      maxZoom: 14,
      duration: 1200
    })
  } else {
    map.flyTo({ center: fallbackCoords, zoom: 12, speed: 1.2 })
  }
}

onMounted(() => {
  if (!mapContainer.value) return

  const token = props.mapboxToken || import.meta.env.VITE_MAPBOX_TOKEN

  if (!token) {
    console.error('Mapbox API Token is missing!')
    return
  }

  mapboxgl.accessToken = token

  nextTick(() => {
    map = new mapboxgl.Map({
      container: mapContainer.value,
      style: 'mapbox://styles/mapbox/light-v11',
      center: [19.1451, 51.9194],
      zoom: 5.5,
    })

    map.addControl(new mapboxgl.NavigationControl(), 'top-right')

    map.on('load', () => {
      map.resize()
      renderMarkers(true)
    })
  })
})

onUnmounted(() => {
  clearMarkers()
  if (map) map.remove()
})

watch(
  () => props.offers,
  () => {
    renderMarkers(true)
    if (selectedCity.value && !groupedOffers.value[selectedCity.value]) {
      selectedCity.value = null
    }
  },
  { deep: true }
)

watch(selectedCity, () => {
  renderMarkers(false)
})
</script>

<template>
  <div class="space-y-4 py-4 sm:px-4 sm:py-6">
    <div class="relative w-full h-[450px] rounded-3xl overflow-hidden border border-border shadow-sm">
      <div ref="mapContainer" class="w-full h-full"></div>

      <div
        v-if="selectedCity"
        class="absolute top-4 left-4 bg-white/95 backdrop-blur px-4 py-2 rounded-2xl border border-border shadow-md flex items-center gap-3 z-10"
      >
        <span class="text-sm font-semibold text-text">
          {{ selectedCity }} ({{ selectedCityOffers.length }})
        </span>
        <button
          type="button"
          class="text-xs text-additional hover:text-text font-medium underline cursor-pointer"
          @click="selectedCity = null"
        >
          {{ t('student.offers.map.showAll') }}
        </button>
      </div>
    </div>

    <div v-if="selectedCity" class="mt-6">
      <h3 class="font-semibold text-text text-lg mb-4">
        {{ t('student.offers.map.cityOffersTitle', { city: selectedCity }) }}
      </h3>
      <div class="space-y-3 sm:space-y-4">
        <OfferCard
          v-for="offer in selectedCityOffers"
          :key="offer.id"
          :offer="offer"
          :has-cv="hasCv"
          :guest="guest"
        />
      </div>
    </div>

    <div v-else class="text-center py-6 text-additional text-sm bg-background/50 rounded-2xl border border-dashed border-border">
      {{ t('student.offers.map.selectPinHint') }}
    </div>
  </div>
</template>
