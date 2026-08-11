
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import mapboxgl from 'mapbox-gl'
import {
  groupOffersByCity,
  getOfferCoordinates,
  getCityCoordinates,
  getJitteredCoordinates,
} from '@/Helpers/offerMapGeo'

import { tryDetectUserLocation } from '@/Helpers/getUserLocation.js'

const DEFAULT_MAP_VIEW = {
  center: [15, 50],
  zoom: 4,
}

const INDIVIDUAL_ZOOM_THRESHOLD = 11

export function useOffersMap(offersRef, mapboxToken, initialOfferId = ref(null)) {
  const mapContainer = ref(null)
  const selectedCity = ref(null)
  const selectedOfferId = ref(null)
  const currentZoom = ref(5.5)

  let map = null
  let markers = []

  const groupedOffers = computed(() => groupOffersByCity(offersRef.value))

  const selectedCityOffers = computed(() => {
    if (!selectedCity.value) return []
    return groupedOffers.value[selectedCity.value] || []
  })

  const viewMode = computed(() =>
    currentZoom.value >= INDIVIDUAL_ZOOM_THRESHOLD ? 'individual' : 'clustered',
  )

  const clearMarkers = () => {
    markers.forEach((marker) => marker.remove())
    markers = []
  }

  const scrollToOfferCard = (offerId) => {
    nextTick(() => {
      const el = document.querySelector(`[data-offer-id="${offerId}"]`)
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' })
      }
    })
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
      map.fitBounds(cityBounds, { padding: 80, maxZoom: 14, duration: 1200 })
    } else {
      map.flyTo({ center: fallbackCoords, zoom: 12, speed: 1.2 })
    }
  }

  const selectAndFocusOffer = (offerId) => {
    if (!offerId || !offersRef.value.length) return

    const offer = offersRef.value.find((o) => String(o.id) === String(offerId))
    if (!offer) return

    const cityName = offer.city
    const cityOffers = groupedOffers.value[cityName] || []
    const fallbackCityCoords = getCityCoordinates(cityName, cityOffers)
    const offerIndex = cityOffers.findIndex((o) => String(o.id) === String(offerId))

    const offerCoords = getJitteredCoordinates(offer, offerIndex >= 0 ? offerIndex : 0, fallbackCityCoords)

    selectedCity.value = cityName
    selectedOfferId.value = offer.id

    if (map && offerCoords) {
      currentZoom.value = 12
      map.flyTo({
        center: offerCoords,
        zoom: 14,
        speed: 2.5,
      })
    }
  }

  const buildClusterPinElement = (cityName, count, isSelected) => {
    const el = document.createElement('button')
    el.type = 'button'
    el.className = 'custom-map-pin focus:outline-none'
    el.setAttribute('aria-label', `${cityName}: ${count}`)

    el.innerHTML = `
      <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full shadow-lg border text-xs font-bold transition-all duration-200 transform hover:scale-105 cursor-pointer ${
  isSelected
    ? 'bg-primary text-white border-primary ring-4 ring-primary/20 scale-110 z-10'
    : 'bg-white text-text border-border hover:border-primary/50'
}">
        <svg class="w-3.5 h-3.5 ${isSelected ? 'text-white' : 'text-primary'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span>${cityName}</span>
        <span class="ml-0.5 px-1.5 py-0.5 rounded-full text-[10px] ${
  isSelected ? 'bg-white/20 text-white' : 'bg-background text-additional'
}">${count}</span>
      </div>
    `
    return el
  }

  const buildDotElement = (offer, isSelected) => {
    const el = document.createElement('button')
    el.type = 'button'
    el.className = 'custom-map-dot focus:outline-none'
    el.setAttribute('aria-label', offer.company_name || offer.title || '')

    el.innerHTML = `
      <div class="flex items-center justify-center rounded-full shadow-lg border-2 transition-all duration-200 transform hover:scale-110 cursor-pointer ${
  isSelected
    ? 'bg-primary border-white ring-4 ring-primary/25 scale-125 w-9 h-9 z-10'
    : 'bg-white border-primary/30 hover:border-primary/60 w-8 h-8'
}">
        <svg class="w-4 h-4 ${isSelected ? 'text-white' : 'text-primary'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
      </div>
    `
    return el
  }

  const renderClusteredMarkers = (fitBounds) => {
    const bounds = new mapboxgl.LngLatBounds()
    let hasValidBounds = false

    Object.entries(groupedOffers.value).forEach(([cityName, cityOffers]) => {
      if (cityOffers.length === 0) return

      const coords = getCityCoordinates(cityName, cityOffers)
      bounds.extend(coords)
      hasValidBounds = true

      const isSelected = selectedCity.value === cityName
      const el = buildClusterPinElement(cityName, cityOffers.length, isSelected)

      el.addEventListener('click', () => {
        flyToCity(cityName, cityOffers, coords)
        selectedCity.value = cityName
        selectedOfferId.value = null
      })

      markers.push(new mapboxgl.Marker({ element: el }).setLngLat(coords).addTo(map))
    })

    if (fitBounds && hasValidBounds && markers.length > 0) {
      map.fitBounds(bounds, { padding: 80, maxZoom: 10 })
    }
  }

  const renderIndividualMarkers = () => {
    Object.entries(groupedOffers.value).forEach(([cityName, cityOffers]) => {
      const cityFallback = getCityCoordinates(cityName, cityOffers)

      cityOffers.forEach((offer, index) => {
        const coords = getJitteredCoordinates(offer, index, cityFallback)
        const isSelected = String(selectedOfferId.value) === String(offer.id)

        const el = buildDotElement(offer, isSelected)

        el.addEventListener('click', () => {
          selectedCity.value = cityName
          selectedOfferId.value = offer.id
          scrollToOfferCard(offer.id)
        })

        markers.push(new mapboxgl.Marker({ element: el }).setLngLat(coords).addTo(map))
      })
    })
  }

  const renderMarkersForZoom = (fitBounds = true) => {
    if (!map) return
    clearMarkers()

    if (viewMode.value === 'individual') {
      renderIndividualMarkers()
    } else {
      renderClusteredMarkers(fitBounds)
    }
  }

  const clearSelection = () => {
    selectedCity.value = null
    selectedOfferId.value = null
  }

  const resetView = () => {
    clearSelection()
    currentZoom.value = DEFAULT_MAP_VIEW.zoom
    renderMarkersForZoom(true)
  }

  onMounted(() => {
    if (!mapContainer.value) return

    const token = typeof mapboxToken === 'object' ? mapboxToken.value : mapboxToken

    if (!token) {
      console.error('Mapbox API Token is missing!')
      return
    }

    mapboxgl.accessToken = token

    nextTick(async () => {
      const detectedView = await tryDetectUserLocation()
      const initialView = detectedView || DEFAULT_MAP_VIEW

      map = new mapboxgl.Map({
        container: mapContainer.value,
        style: 'mapbox://styles/mapbox/light-v11',
        center: initialView.center,
        zoom: initialView.zoom,
      })

      map.addControl(new mapboxgl.NavigationControl(), 'top-right')

      map.on('load', () => {
        map.resize()

        const rawInitialId = typeof initialOfferId === 'object' ? initialOfferId.value : initialOfferId
        if (rawInitialId) {
          selectAndFocusOffer(rawInitialId)
          renderMarkersForZoom(false)
        } else {
          renderMarkersForZoom(true)
          map.setCenter(initialView.center)
          map.setZoom(initialView.zoom)
        }
      })

      map.on('zoomend', () => {
        currentZoom.value = map.getZoom()
      })
    })
  })

  onUnmounted(() => {
    clearMarkers()
    if (map) map.remove()
  })

  watch(offersRef, () => {
    renderMarkersForZoom(true)
    if (selectedCity.value && !groupedOffers.value[selectedCity.value]) {
      clearSelection()
    }
  }, { deep: true })

  watch([selectedCity, selectedOfferId], () => renderMarkersForZoom(false))

  watch(viewMode, () => renderMarkersForZoom(false))

  return {
    mapContainer,
    selectedCity,
    selectedOfferId,
    selectedCityOffers,
    resetView,
    selectAndFocusOffer,
  }
}
