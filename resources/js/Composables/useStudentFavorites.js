import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { studentOfferFavourite } from '@/Helpers/routes'

export function useStudentFavorites() {
  const page = usePage()

  const favoriteIds = computed(() => page.props.favoriteOfferIds ?? [])
  const favoriteCount = computed(() => favoriteIds.value.length)

  function toggleFavorite(offerId) {
    const url = studentOfferFavourite(offerId)

    if (favoriteIds.value.includes(offerId)) {
      router.delete(url, { preserveScroll: true })
    } else {
      router.post(url, {}, { preserveScroll: true })
    }
  }

  function clearFavorites() {
    const remaining = [...favoriteIds.value]

    const removeNext = () => {
      const offerId = remaining.shift()

      if (!offerId) return

      router.delete(studentOfferFavourite(offerId), {
        preserveScroll: true,
        onSuccess: removeNext,
      })
    }

    removeNext()
  }

  return { favoriteIds, favoriteCount, toggleFavorite, clearFavorites }
}
