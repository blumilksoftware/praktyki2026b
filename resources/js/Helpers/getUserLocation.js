
const DETAIL_ZOOM = 12

export function tryDetectUserLocation() {
  return new Promise((resolve) => {
    if (!navigator.geolocation) {
      return resolve(null)
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { longitude, latitude } = position.coords
        resolve({
          center: [longitude, latitude],
          zoom: DETAIL_ZOOM,
        })
      },
      (error) => {
        resolve(null)
      },
      {
        timeout: 10000,
        maximumAge: 60000,
        enableHighAccuracy: true,
      },
    )
  })
}
