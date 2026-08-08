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
    OFFER_SHOW: '/offers/{offer}',
    COMPANY_OFFERS: '/company-offers'
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

  beforeEach(() => {
    mockRouterGet.mockClear()
  })

  it('renders up to 4 offers and hides the "Show All" button if 4 or fewer', () => {
    const offers = generateMockOffers(3)
    const wrapper = mount(Offers, {
      props: { offers },
      global: { stubs: globalStubs }
    })

    const offerTitles = wrapper.findAll('h3')
    expect(offerTitles.length).toBe(3)
    
    expect(wrapper.text()).not.toContain('common.empty.noOffers')
    
    expect(wrapper.text()).not.toContain('common.actions.showAll')
  })

  it('slices the array to render exactly 4 offers and shows the "Show All" button if more than 4', () => {
    const offers = generateMockOffers(6)
    const wrapper = mount(Offers, {
      props: { offers },
      global: { stubs: globalStubs }
    })

    const offerTitles = wrapper.findAll('h3')
    expect(offerTitles.length).toBe(4)
    
    expect(wrapper.text()).toContain('common.actions.showAll')
  })

  it('renders a fallback message when the offers array is empty', () => {
    const wrapper = mount(Offers, {
      props: { offers: [] },
      global: { stubs: globalStubs }
    })

    expect(wrapper.text()).toContain('common.empty.noOffers')
    expect(wrapper.findAll('h3').length).toBe(0)
  })

  it('navigates to the specific offer page when "View" is clicked', async () => {
    const offers = generateMockOffers(1)
    const wrapper = mount(Offers, {
      props: { offers },
      global: { stubs: globalStubs }
    })

    const viewButton = wrapper.findAll('button').find(b => b.text().includes('common.actions.view'))
    await viewButton!.trigger('click')

    expect(mockRouterGet).toHaveBeenCalledTimes(1)
    expect(mockRouterGet).toHaveBeenCalledWith('/offers/1', undefined)
  })

  it('navigates to the company offers list when "Show All" is clicked', async () => {
    const offers = generateMockOffers(6)
    const wrapper = mount(Offers, {
      props: { 
        id: 99,
        offers 
      },
      global: { stubs: globalStubs }
    })

    const showAllButton = wrapper.findAll('button').find(b => b.text().includes('common.actions.showAll'))
    await showAllButton!.trigger('click')

    expect(mockRouterGet).toHaveBeenCalledTimes(1)
    expect(mockRouterGet).toHaveBeenCalledWith('/company-offers', { company_id: 99 })
  })
})