import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import { router } from '@inertiajs/vue3'
import OffersPagination from '@/Components/Company/Offers/OffersPagination.vue'
import en from '@/lang/en.json'

vi.mock('@inertiajs/vue3', () => ({
  router: { get: vi.fn() },
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const paginator = {
  data: [{ id: 'o1' }],
  from: 1,
  to: 10,
  total: 25,
  last_page: 3,
  links: [
    { url: null, label: '&laquo; Previous', active: false },
    { url: '/company/dashboard?page=1', label: '1', active: true },
    { url: '/company/dashboard?page=2', label: '2', active: false },
    { url: '/company/dashboard?page=2', label: 'Next &raquo;', active: false },
  ],
}

function mountPagination(offers = {}) {
  return mount(OffersPagination, {
    props: {
      offers: { ...paginator, ...offers },
    },
    global: {
      plugins: [i18n],
    },
  })
}

describe('OffersPagination', () => {
  beforeEach(() => {
    vi.mocked(router.get).mockClear()
  })

  it('renders nothing when there is only one page', () => {
    const wrapper = mountPagination({ last_page: 1 })

    expect(wrapper.find('button').exists()).toBe(false)
    expect(wrapper.text()).toBe('')
  })

  it('renders nothing when the page holds no offers', () => {
    const wrapper = mountPagination({ data: [] })

    expect(wrapper.find('button').exists()).toBe(false)
  })

  it('renders the range summary', () => {
    const wrapper = mountPagination()

    expect(wrapper.find('span').text()).toBe('Showing 1–10 of 25')
  })

  it('decodes html entities in the link labels', () => {
    const wrapper = mountPagination()
    const labels = wrapper.findAll('button').map((button) => button.text())

    expect(labels).toEqual(['« Previous', '1', '2', 'Next »'])
  })

  it('highlights the active page and disables links without a url', () => {
    const wrapper = mountPagination()
    const buttons = wrapper.findAll('button')

    expect(buttons[0].attributes('disabled')).toBeDefined()
    expect(buttons[0].classes()).toContain('cursor-not-allowed')
    expect(buttons[1].classes()).toContain('bg-primary')
    expect(buttons[2].attributes('disabled')).toBeUndefined()
    expect(buttons[2].classes()).toContain('cursor-pointer')
  })

  it('navigates to the clicked page while preserving state and scroll', async () => {
    const wrapper = mountPagination()

    await wrapper.findAll('button')[2].trigger('click')

    expect(router.get).toHaveBeenCalledWith(
      '/company/dashboard?page=2',
      {},
      { preserveState: true, preserveScroll: true, replace: true },
    )
  })

  it('does not navigate when the link has no url', async () => {
    const wrapper = mountPagination()

    await wrapper.findAll('button')[0].trigger('click')

    expect(router.get).not.toHaveBeenCalled()
  })
})
