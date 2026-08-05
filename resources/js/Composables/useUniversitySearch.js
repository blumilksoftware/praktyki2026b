import { ref } from 'vue'
import { ROUTES } from '@/Helpers/routes'

export function useUniversitySearch() {
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
        const url = new URL(ROUTES.STUDENT_UNIVERSITY_SEARCH,
          window.location.origin)
        url.searchParams.set('query', query)

        const response = await fetch(url, {
          signal: abortController.signal,
          headers: { Accept: 'application/json' },
        })
        const data = response.ok
          ? await response.json()
          : { universities: [],
        }
        suggestions.value = data.universities
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
