import { computed, ref } from 'vue'

export function useMapboxGeocoding() {
  const suggestions = ref([])
  const isLoading = ref(false)
  const error = ref(null)
  let debounceTimer = null
  let abortController = null

  const cityOptions = computed(() => {
    const seen = new Set()

    return suggestions.value
      .filter((city) => {
        if (seen.has(city.name)) return false
        seen.add(city.name)
        return true
      })
      .map((city) => ({ value: city.name, label: city.fullName }))
  })

  const clearSuggestions = () => {
    abortController?.abort()
    abortController = null
    suggestions.value = []
    error.value = null
  }

  const fetchSuggestions = (query) => {
    clearTimeout(debounceTimer)

    if (!query || query.trim().length < 3) {
      clearSuggestions()
      return
    }

    debounceTimer = setTimeout(async () => {
      if (abortController) {
        abortController.abort()
      }
      abortController = new AbortController()

      isLoading.value = true
      error.value = null
      try {
        const url = new URL('/geocoding/cities', window.location.origin)
        url.searchParams.set('query', query)

        const response = await fetch(url, {
          signal: abortController.signal,
          headers: { Accept: 'application/json' },
        })

        if (!response.ok) {
          const body = await response.json().catch(() => ({}))
          suggestions.value = []
          error.value = body.message ?? null
          return
        }

        suggestions.value = await response.json()
      } catch (requestError) {
        if (requestError.name !== 'AbortError') {
          suggestions.value = []
        }
      } finally {
        isLoading.value = false
      }
    }, 400)
  }

  return { suggestions, cityOptions, isLoading, error, fetchSuggestions, clearSuggestions }
}
