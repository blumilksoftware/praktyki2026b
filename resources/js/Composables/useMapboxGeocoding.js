import { ref } from 'vue'

export function useMapboxGeocoding() {
  const suggestions = ref([])
  const isLoading = ref(false)
  let debounceTimer = null
  let abortController = null

  const fetchSuggestions = (query) => {
    clearTimeout(debounceTimer)

    if (!query || query.trim().length < 2) {
      suggestions.value = []
      return
    }

    debounceTimer = setTimeout(async () => {
      if (abortController) {
        abortController.abort()
      }
      abortController = new AbortController()

      isLoading.value = true
      try {
        const url = new URL('/company/geocoding/cities', window.location.origin)
        url.searchParams.set('query', query)

        const response = await fetch(url, {
          signal: abortController.signal,
          headers: { Accept: 'application/json' },
        })

        suggestions.value = response.ok ? await response.json() : []
      } catch (error) {
        if (error.name !== 'AbortError') {
          suggestions.value = []
        }
      } finally {
        isLoading.value = false
      }
    }, 300)
  }

  const clearSuggestions = () => {
    suggestions.value = []
  }

  return { suggestions, isLoading, fetchSuggestions, clearSuggestions }
}
