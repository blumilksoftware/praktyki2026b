const FALLBACK_CITY_COORDINATES = {
  'Warsaw': [21.0122, 52.2297],
  'Krakow': [19.9450, 50.0647],
  'Wroclaw': [17.0385, 51.1100],
  'Poznan': [16.9299, 52.4064],
  'Gdansk': [18.6466, 54.3520],
  'Lodz': [19.4560, 51.7592],
  'Katowice': [19.0238, 50.2649],
}


export function groupOffersByCity(offers) {
  const groups = {}
  offers.forEach((offer) => {
    if (!offer.city) return
    if (!groups[offer.city]) {
      groups[offer.city] = []
    }
    groups[offer.city].push(offer)
  })
  return groups
}

export function getOfferCoordinates(offer) {
  if (
    offer.longitude !== null && offer.longitude !== undefined &&
    offer.latitude !== null && offer.latitude !== undefined &&
    !isNaN(Number(offer.longitude)) && !isNaN(Number(offer.latitude))
  ) {
    const lng = Number(offer.longitude)
    const lat = Number(offer.latitude)

    // (0, 0) — практически всегда не реальная локация, а незаполненное
    // значение по умолчанию в БД ("Null Island"). Считаем невалидным,
    // чтобы такие офисы не портили fitBounds/центрирование карты.
    if (lng === 0 && lat === 0) {
      return null
    }

    return [lng, lat]
  }
  return null
}

export function getCityCoordinates(cityName, cityOffers) {
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

export function getJitteredCoordinates(offer, index, cityFallback) {
  const real = getOfferCoordinates(offer)
  if (real) return real

  let hash = 0
  const seed = String(offer.id ?? index)
  for (let i = 0; i < seed.length; i++) {
    hash = seed.charCodeAt(i) + ((hash << 5) - hash)
  }

  const angle = (Math.abs(hash) % 360) * (Math.PI / 180)
  const radius = 0.01 + (Math.abs(hash >> 3) % 5) * 0.004

  return [
    cityFallback[0] + Math.cos(angle) * radius,
    cityFallback[1] + Math.sin(angle) * radius,
  ]
}
