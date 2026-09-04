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
const RADIUS_SOURCE_ID = 'search-radius'
const RADIUS_FILL_LAYER_ID = 'search-radius-fill'
const RADIUS_OUTLINE_LAYER_ID = 'search-radius-outline'
const EARTH_RADIUS_KM = 6371
const RADIUS_CIRCLE_POINTS = 64

function buildRadiusCircleGeoJson(center, radiusKm) {
  const [lng, lat] = center
  const coords = []
  const latRad = (lat * Math.PI) / 180
  const lngRad = (lng * Math.PI) / 180
  const angularDistance = radiusKm / EARTH_RADIUS_KM

  for (let i = 0; i <= RADIUS_CIRCLE_POINTS; i++) {
    const bearing = (i * 2 * Math.PI) / RADIUS_CIRCLE_POINTS
    const pointLatRad = Math.asin(
      Math.sin(latRad) * Math.cos(angularDistance)
      + Math.cos(latRad) * Math.sin(angularDistance) * Math.cos(bearing),
    )
    const pointLngRad = lngRad + Math.atan2(
      Math.sin(bearing) * Math.sin(angularDistance) * Math.cos(latRad),
      Math.cos(angularDistance) - Math.sin(latRad) * Math.sin(pointLatRad),
    )

    coords.push([(pointLngRad * 180) / Math.PI, (pointLatRad * 180) / Math.PI])
  }

  return {
    type: 'Feature',
    geometry: { type: 'Polygon', coordinates: [coords] },
    properties: {},
  }
}

const INDIVIDUAL_ZOOM_THRESHOLD = 11

export function useOffersMap(offersRef, mapboxToken, initialOfferId = ref(null), radiusKm = ref(null),
                             radiusCenter = ref(null), cityFilter = ref([]), options = {}) {
  const { onCitySelect, onClear, selectedCityLabel = ref('') } = options

  const mapContainer = ref(null)
  const selectedCity = ref(null)
  const selectedOfferId = ref(null)
  const currentZoom = ref(5.5)

  let map = null
  let markers = []
  let pendingInitialOfferId = null
  let isUnmounted = false

  const groupedOffers = computed(() => groupOffersByCity(offersRef.value))

  const selectedCityOffers = computed(() => {
    if (!selectedCity.value) return []
    return groupedOffers.value[selectedCity.value] || []
  })

  const viewMode = computed(() =>
    currentZoom.value >= INDIVIDUAL_ZOOM_THRESHOLD ? 'individual' : 'clustered',
  )

  const getRadiusKmValue = () => (typeof radiusKm === 'object' ? radiusKm.value : radiusKm)
  const getRadiusCenterValue = () => (typeof radiusCenter === 'object' ? radiusCenter.value : radiusCenter)

  const hasActiveRadius = () => {
    const km = getRadiusKmValue()
    const center = getRadiusCenterValue()
    return !!(km && center && center.latitude != null && center.longitude != null)
  }

  const fitToRadius = (duration = 0) => {
    if (!map) return
    const km = getRadiusKmValue()
    const center = getRadiusCenterValue()
    if (!km || !center || center.latitude == null || center.longitude == null) return

    const circleGeoJson = buildRadiusCircleGeoJson([center.longitude, center.latitude], km)
    const radiusBounds = new mapboxgl.LngLatBounds()
    circleGeoJson.geometry.coordinates[0].forEach((coord) => radiusBounds.extend(coord))
    map.fitBounds(radiusBounds, { padding: 40, duration })
  }

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

  const focusCity = (cityName, coords) => {
    if (!cityName || !map) return
    const cityOffers = groupedOffers.value[cityName] || []
    const fallbackCoords = coords
      ? [coords.longitude, coords.latitude]
      : getCityCoordinates(cityName, cityOffers)

    if (!fallbackCoords) return

    selectedCity.value = cityName
    selectedOfferId.value = null
    flyToCity(cityName, cityOffers, fallbackCoords)
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
        onCitySelect?.({ label: cityName, latitude: coords[1], longitude: coords[0] })
      })

      markers.push(new mapboxgl.Marker({ element: el }).setLngLat(coords).addTo(map))
    })

    if (fitBounds && hasValidBounds && markers.length > 0) {
      map.fitBounds(bounds, { padding: 80, maxZoom: 14 })
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
          onCitySelect?.({ label: cityName, latitude: cityFallback[1], longitude: cityFallback[0] })
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

  const updateRadiusCircle = () => {
    if (!map) return

    if (!map.isStyleLoaded()) {
      map.once('idle', updateRadiusCircle)
      return
    }

    const km = getRadiusKmValue()
    const center = getRadiusCenterValue()

    const hasCircle = map.getSource(RADIUS_SOURCE_ID)

    if (!km || !center || center.latitude == null || center.longitude == null) {
      if (hasCircle) {
        if (map.getLayer(RADIUS_FILL_LAYER_ID)) map.removeLayer(RADIUS_FILL_LAYER_ID)
        if (map.getLayer(RADIUS_OUTLINE_LAYER_ID)) map.removeLayer(RADIUS_OUTLINE_LAYER_ID)
        map.removeSource(RADIUS_SOURCE_ID)
      }
      return
    }

    const geojson = buildRadiusCircleGeoJson([center.longitude, center.latitude], km)

    if (hasCircle) {
      map.getSource(RADIUS_SOURCE_ID).setData(geojson)
      return
    }

    map.addSource(RADIUS_SOURCE_ID, { type: 'geojson', data: geojson })

    map.addLayer({
      id: RADIUS_FILL_LAYER_ID,
      type: 'fill',
      source: RADIUS_SOURCE_ID,
      paint: {
        'fill-color': '#2563eb',
        'fill-opacity': 0.08,
      },
    })

    map.addLayer({
      id: RADIUS_OUTLINE_LAYER_ID,
      type: 'line',
      source: RADIUS_SOURCE_ID,
      paint: {
        'line-color': '#2563eb',
        'line-width': 1.5,
        'line-dasharray': [2, 2],
      },
    })
  }

  const clearSelection = () => {
    selectedCity.value = null
    selectedOfferId.value = null
  }

  const resetFilters = () => {
    clearSelection()
    setTimeout(() => {
      renderMarkersForZoom(false)
      onClear?.()
    }, 100)
  }

  const waitForContainer = () => new Promise((resolve) => {
    const check = () => {
      if (isUnmounted || mapContainer.value) {
        resolve()
        return
      }
      requestAnimationFrame(check)
    }
    check()
  })

  onMounted(() => {
    const token = mapboxToken && typeof mapboxToken === 'object' ? mapboxToken.value : mapboxToken

    if (!token) {
      console.error('Mapbox API Token is missing!')
      return
    }

    mapboxgl.accessToken = token

    nextTick(async () => {
      const detectedView = await tryDetectUserLocation()


      if (isUnmounted) return

      await waitForContainer()

      if (isUnmounted || !mapContainer.value) return

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
          if (offersRef.value.length) {
            selectAndFocusOffer(rawInitialId)
          } else {
            pendingInitialOfferId = rawInitialId
          }
          renderMarkersForZoom(false)
        } else if (hasActiveRadius()) {
          renderMarkersForZoom(false)
          fitToRadius(0)
        } else {
          renderMarkersForZoom(true)
          map.setCenter(initialView.center)
          map.setZoom(initialView.zoom)
        }
        updateRadiusCircle()
      })

      map.on('zoomend', () => {
        currentZoom.value = map.getZoom()
      })
    })
  })

  onUnmounted(() => {
    isUnmounted = true
    clearMarkers()
    if (map) {
      map.remove()
      map = null
    }
  })

  watch(offersRef, () => {
    renderMarkersForZoom(!hasActiveRadius())

    if (pendingInitialOfferId) {
      const idToFocus = pendingInitialOfferId
      pendingInitialOfferId = null
      selectAndFocusOffer(idToFocus)
    }

    if (selectedCity.value && !groupedOffers.value[selectedCity.value]) {
      clearSelection()
    }
  }, { deep: true })

  watch([selectedCity, selectedOfferId], () => renderMarkersForZoom(false))

  watch(viewMode, () => renderMarkersForZoom(false))

  watch(
    () => getRadiusKmValue(),
    (newKm, oldKm) => {
      updateRadiusCircle()
      if (hasActiveRadius() && newKm !== oldKm) {
        fitToRadius(800)
      }
    },
  )

  watch(
    () => getRadiusCenterValue(),
    () => {
      updateRadiusCircle()
    },
    { deep: true },
  )

  watch(
    () => (typeof selectedCityLabel === 'object' ? selectedCityLabel.value : selectedCityLabel),
    (cityName) => {
      if (!cityName) {
        if (selectedCity.value) {
          clearSelection()
          renderMarkersForZoom(false)
        }
        return
      }
      if (selectedCity.value === cityName) return

      const center = getRadiusCenterValue()
      focusCity(cityName, center)
    },
  )

  return {
    mapContainer,
    selectedCity,
    selectedOfferId,
    selectedCityOffers,
    selectAndFocusOffer,
    resetFilters,
  }
}
