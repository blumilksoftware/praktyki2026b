import { computed, ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { ROUTES } from '@/Helpers/routes'

function offerFavouriteRoute(offerId: string): string {
  return ROUTES.STUDENT_OFFER_FAVORITE.replace('{offer}', offerId)
}

export function useStudentFavorites() {
  const page = usePage()
  const favoriteIds = ref<string[]>([...((page.props.favoriteOfferIds as string[] | undefined) ?? [])])
    watch(() => page.props.favoriteOfferIds as string[] | undefined, (value) => {
      favoriteIds.value = [...(value ?? [])]
    })

  const isFavorite = (offerId: string) => favoriteIds.value.includes(offerId)

  const toggleFavorite = (offerId: string) => {
    const wasFavorite = isFavorite(offerId)

    favoriteIds.value = wasFavorite
      ? favoriteIds.value.filter((favoriteId) => favoriteId !== offerId)
      : [...favoriteIds.value, offerId]

    const options = { preserveScroll: true, preserveState: true }

    if (wasFavorite) {
      router.delete(offerFavouriteRoute(offerId), options)
    }

    else {
      router.post(offerFavouriteRoute(offerId), {}, options)
    }

  }

  function deleteFavourite(offerId: string): Promise<void> {
    return new Promise((resolve) => {
      router.delete(offerFavouriteRoute(offerId), {
        preserveScroll: true,
          preserveState: true,
          onFinish: () => resolve(),
      })
    })
  }

  const clearFavorites = () => {
    const idsToRemove = [...favoriteIds.value]
    favoriteIds.value = []
      return idsToRemove.reduce(
      (chain, offerId) => chain.then(() => deleteFavourite(offerId)),
        Promise.resolve(),
      )
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
