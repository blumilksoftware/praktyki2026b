import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import Offers from '@/Components/Profiles/Offers.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

const mockRouterGet = vi.fn()
vi.mock('@inertiajs/vue3', () => ({
  router: {
    get: (url: string, data?: any) => mockRouterGet(url, data)
  }
}))

vi.mock('@/Helpers/routes', () => ({
  ROUTES: {
    OFFER_SHOW: '/offers/{offer}'
  }
}))

describe('Offers.vue', () => {
  const generateMockOffers = (count: number) => {
    return Array.from({ length: count }, (_, i) => ({
      id: i + 1,
      title: `Offer Title ${i + 1}`,
      description: `Description for offer ${i + 1}`
    }))
  }

  const globalStubs = {
    IconBriefcase2Filled: true,
    BaseButton: {
      template: '<button><slot /></button>'
    }
  }

  const mountOffers = (count: number) => mount(Offers, {
    props: { offers: generateMockOffers(count) },
    global: { stubs: globalStubs }
  })

  const findLoadMoreButton = (wrapper: ReturnType<typeof mountOffers>) =>
    wrapper.findAll('button').find(button => button.text().includes('buttons.load_more'))

  beforeEach(() => {
    mockRouterGet.mockClear()
  })

  it('renders every offer and hides the load more button when there are 4 or fewer', () => {
    const wrapper = mountOffers(3)

    expect(wrapper.findAll('h3').length).toBe(3)
    expect(wrapper.text()).not.toContain('profiles.noOffers')
    expect(findLoadMoreButton(wrapper)).toBeUndefined()
  })

  it('renders the first 4 offers and shows the load more button when there are more', () => {
    const wrapper = mountOffers(6)

    expect(wrapper.findAll('h3').length).toBe(4)
    expect(findLoadMoreButton(wrapper)).toBeDefined()
  })

  it('renders a fallback message when the offers array is empty', () => {
    const wrapper = mount(Offers, {
      props: { offers: [] },
      global: { stubs: globalStubs }
    })

    expect(wrapper.text()).toContain('profiles.noOffers')
    expect(wrapper.findAll('h3').length).toBe(0)
  })

  it('navigates to the specific offer page when "View" is clicked', async () => {
    const wrapper = mountOffers(1)

    const viewButton = wrapper.findAll('button').find(b => b.text().includes('buttons.view'))
    await viewButton!.trigger('click')

    expect(mockRouterGet).toHaveBeenCalledTimes(1)
    expect(mockRouterGet).toHaveBeenCalledWith('/offers/1', undefined)
  })

  it('reveals the next batch on every click', async () => {
    const wrapper = mountOffers(10)

    await findLoadMoreButton(wrapper)!.trigger('click')
    expect(wrapper.findAll('h3').length).toBe(8)

    await findLoadMoreButton(wrapper)!.trigger('click')
    expect(wrapper.findAll('h3').length).toBe(10)
  })

  it('hides the load more button once every offer is shown', async () => {
    const wrapper = mountOffers(6)

    await findLoadMoreButton(wrapper)!.trigger('click')

    expect(wrapper.findAll('h3').length).toBe(6)
    expect(findLoadMoreButton(wrapper)).toBeUndefined()
  })
})