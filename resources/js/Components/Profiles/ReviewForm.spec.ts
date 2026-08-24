import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import ReviewForm from '@/Components/Profiles/ReviewForm.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

const mockRouterPost = vi.fn()
vi.mock('@inertiajs/vue3', () => ({
  router: {
    post: (url: string, data?: any, options?: any) => mockRouterPost(url, data, options)
  }
}))

vi.mock('@/Helpers/routes', () => ({
  companyReviewsStore: (companyId: string) => `/student/companies/${companyId}/reviews`
}))

describe('ReviewForm.vue', () => {
  const globalStubs = {
    BaseButton: { template: '<button :type="type" :disabled="disabled"><slot /></button>', props: ['disabled', 'type'] },
    BaseTextarea: { template: '<textarea />' },
  }

  beforeEach(() => {
    mockRouterPost.mockClear()
  })

  it('disables submit until a rating is picked', () => {
    const wrapper = mount(ReviewForm, {
      props: { companyId: 'company-1' },
      global: { stubs: globalStubs },
    })

    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined()
  })

  it('submits the chosen rating to the company reviews endpoint', async () => {
    const wrapper = mount(ReviewForm, {
      props: { companyId: 'company-1' },
      global: { stubs: globalStubs },
    })

    await wrapper.findAll('button')[3].trigger('click')
    await wrapper.find('form').trigger('submit.prevent')

    expect(mockRouterPost).toHaveBeenCalledTimes(1)
    expect(mockRouterPost.mock.calls[0][0]).toBe('/student/companies/company-1/reviews')
    expect(mockRouterPost.mock.calls[0][1]).toEqual({ rating: 4, comment: '' })
  })

  it('shows server validation errors', async () => {
    mockRouterPost.mockImplementation((_url, _data, options) => {
      options.onError({ company: 'validation.already_reviewed_company' })
    })

    const wrapper = mount(ReviewForm, {
      props: { companyId: 'company-1' },
      global: { stubs: globalStubs },
    })

    await wrapper.findAll('button')[0].trigger('click')
    await wrapper.find('form').trigger('submit.prevent')

    expect(wrapper.text()).toContain('validation.already_reviewed_company')
  })
})
