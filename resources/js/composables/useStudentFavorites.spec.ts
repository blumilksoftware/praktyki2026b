import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useStudentFavorites } from '@/composables/useStudentFavorites'

const { routerPost, routerDelete, mockPageProps } = vi.hoisted(() => ({
  routerPost: vi.fn(),
  routerDelete: vi.fn((url: string, options?: { onFinish?: () => void }) => options?.onFinish?.()),
  mockPageProps: { favoriteOfferIds: [] as string[] },
}))

vi.mock('@inertiajs/vue3', () => ({
  usePage: () => ({ props: mockPageProps }),
  router: {
    post: routerPost,
    delete: routerDelete,
  },
}))

describe('useStudentFavorites', () => {
  beforeEach(() => {
    routerPost.mockClear()
    routerDelete.mockClear()
    mockPageProps.favoriteOfferIds = ['offer-1', 'offer-2']
  })

  it('initializes favoriteIds from the shared favoriteOfferIds page prop', () => {
    const { favoriteIds, favoriteCount } = useStudentFavorites()

    expect(favoriteIds.value).toEqual(['offer-1', 'offer-2'])
    expect(favoriteCount.value).toBe(2)
  })

  it('isFavorite reports true only for saved offers', () => {
    const { isFavorite } = useStudentFavorites()

    expect(isFavorite('offer-1')).toBe(true)
    expect(isFavorite('offer-3')).toBe(false)
  })

  it('toggleFavorite sends a POST request and updates local state when adding', () => {
    const { toggleFavorite, isFavorite } = useStudentFavorites()

    toggleFavorite('offer-3')

    expect(isFavorite('offer-3')).toBe(true)
    expect(routerPost).toHaveBeenCalledWith(
      '/student/offers/offer-3/favorite',
      {},
      expect.objectContaining({ preserveScroll: true, preserveState: true }),
    )
    expect(routerDelete).not.toHaveBeenCalled()
  })

  it('toggleFavorite sends a DELETE request and updates local state when removing', () => {
    const { toggleFavorite, isFavorite } = useStudentFavorites()

    toggleFavorite('offer-1')

    expect(isFavorite('offer-1')).toBe(false)
    expect(routerDelete).toHaveBeenCalledWith(
      '/student/offers/offer-1/favorite',
      expect.objectContaining({ preserveScroll: true, preserveState: true }),
    )
  })

  it('clearFavorites empties the list immediately and removes every offer on the server', async () => {
    const { clearFavorites, favoriteIds } = useStudentFavorites()

    const done = clearFavorites()
    expect(favoriteIds.value).toEqual([])

    await done

    expect(routerDelete).toHaveBeenCalledTimes(2)
  })
})
