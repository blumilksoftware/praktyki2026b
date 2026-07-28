import { computed, onMounted, ref, watch } from 'vue'

const STORAGE_KEY = 'student.favoriteOffers'

export function useStudentFavorites() {
  const favoriteIds = ref<string[]>([])
  const isReady = ref(false)

  const loadFavorites = () => {
    if (typeof window === 'undefined') {
      return []
    }

    try {
      const storedValue = window.localStorage.getItem(STORAGE_KEY)

      if (!storedValue) {
        return []
      }

      const parsedValue = JSON.parse(storedValue)

      return Array.isArray(parsedValue)
        ? parsedValue.filter((item): item is string => typeof item === 'string')
        : []
    } catch {
      return []
    }
  }

  onMounted(() => {
    favoriteIds.value = loadFavorites()
    isReady.value = true
  })

  watch(favoriteIds, (value) => {
    if (typeof window === 'undefined' || !isReady.value) {
      return
    }

    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(value))
  }, { deep: true })

  const isFavorite = (offerId: string) => favoriteIds.value.includes(offerId)

  const toggleFavorite = (offerId: string) => {
    favoriteIds.value = isFavorite(offerId)
      ? favoriteIds.value.filter((favoriteId) => favoriteId !== offerId)
      : [...favoriteIds.value, offerId]
  }

  const clearFavorites = () => {
    favoriteIds.value = []
  }

  const favoriteCount = computed(() => favoriteIds.value.length)

  return {
    favoriteIds,
    favoriteCount,
    isFavorite,
    toggleFavorite,
    clearFavorites,
  }
}