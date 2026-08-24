import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import ReviewList from '@/Components/Profiles/ReviewList.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string, params?: any) => (params ? `${key} ${JSON.stringify(params)}` : key) })
}))

const mockRouterPatch = vi.fn()
const mockRouterDelete = vi.fn()
vi.mock('@inertiajs/vue3', () => ({
  router: {
    patch: (url: string, data?: any, options?: any) => mockRouterPatch(url, data, options),
    delete: (url: string, options?: any) => mockRouterDelete(url, options)
  }
}))

vi.mock('@/Helpers/routes', () => ({
  companyReviewHide: (reviewId: string) => `/company/reviews/${reviewId}/hide`,
  companyReviewUnhide: (reviewId: string) => `/company/reviews/${reviewId}/unhide`,
  adminReviewDelete: (reviewId: string) => `/admin/reviews/${reviewId}`
}))

describe('ReviewList.vue', () => {
  const globalStubs = {
    BaseButton: { template: '<button><slot /></button>' },
    BaseModal: { props: ['open'], template: '<section v-if="open"><slot /></section>' },
  }

  const baseReviews = {
    items: [
      { id: 'review-1', rating: 4, comment: 'Nice place', studentName: 'Jan K.', createdAt: '2026-08-01T00:00:00Z', hidden: false },
    ],
    averageRating: 4,
    count: 1,
    canReview: false,
    hasReviewed: true,
    canModerate: false,
    canDelete: false,
  }

  beforeEach(() => {
    mockRouterPatch.mockClear()
    mockRouterDelete.mockClear()
  })

  it('renders reviews with the reviewer name and comment', () => {
    const wrapper = mount(ReviewList, {
      props: { reviews: baseReviews },
      global: { stubs: globalStubs },
    })

    expect(wrapper.text()).toContain('Jan K.')
    expect(wrapper.text()).toContain('Nice place')
  })

  it('renders a fallback message when there are no reviews', () => {
    const wrapper = mount(ReviewList, {
      props: { reviews: { ...baseReviews, items: [], count: 0 } },
      global: { stubs: globalStubs },
    })

    expect(wrapper.text()).toContain('profiles.reviews.empty')
  })

  it('does not show moderation buttons without permission', () => {
    const wrapper = mount(ReviewList, {
      props: { reviews: baseReviews },
      global: { stubs: globalStubs },
    })

    expect(wrapper.findAll('button').length).toBe(0)
  })

  it('lets company staff hide a review after confirming in the modal', async () => {
    const wrapper = mount(ReviewList, {
      props: { reviews: { ...baseReviews, canModerate: true } },
      global: { stubs: globalStubs },
    })

    await wrapper.find('button').trigger('click')
    expect(mockRouterPatch).not.toHaveBeenCalled()

    const confirmButton = wrapper.findAll('button').at(-1)
    await confirmButton!.trigger('click')

    expect(mockRouterPatch).toHaveBeenCalledWith('/company/reviews/review-1/hide', {}, expect.any(Object))
  })

  it('lets company staff unhide a hidden review after confirming in the modal', async () => {
    const wrapper = mount(ReviewList, {
      props: {
        reviews: {
          ...baseReviews,
          canModerate: true,
          items: [{ ...baseReviews.items[0], hidden: true }],
        },
      },
      global: { stubs: globalStubs },
    })

    await wrapper.find('button').trigger('click')

    const confirmButton = wrapper.findAll('button').at(-1)
    await confirmButton!.trigger('click')

    expect(mockRouterPatch).toHaveBeenCalledWith('/company/reviews/review-1/unhide', {}, expect.any(Object))
  })

  it('lets a super admin delete a review after confirming in the modal', async () => {
    const wrapper = mount(ReviewList, {
      props: { reviews: { ...baseReviews, canDelete: true } },
      global: { stubs: globalStubs },
    })

    await wrapper.find('button').trigger('click')

    const confirmButton = wrapper.findAll('button').at(-1)
    await confirmButton!.trigger('click')

    expect(mockRouterDelete).toHaveBeenCalledWith('/admin/reviews/review-1', expect.any(Object))
  })

  it('does not call the API when the modal is cancelled', async () => {
    const wrapper = mount(ReviewList, {
      props: { reviews: { ...baseReviews, canModerate: true } },
      global: { stubs: globalStubs },
    })

    await wrapper.find('button').trigger('click')

    const cancelButton = wrapper.findAll('button').at(1)
    await cancelButton!.trigger('click')

    expect(mockRouterPatch).not.toHaveBeenCalled()
    expect(wrapper.findAll('button').length).toBe(1)
  })
})
