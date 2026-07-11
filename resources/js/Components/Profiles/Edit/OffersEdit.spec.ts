import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import OffersEdit from '@/Components/Profiles/Edit/OffersEdit.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

describe('OffersEdit.vue', () => {
  const generateMockOffers = (count: number) => {
    return Array.from({ length: count }, (_, i) => ({
      id: i + 1,
      title: `Offer Title ${i + 1}`,
      description: `Description for offer ${i + 1}`
    }))
  }

  const globalStubs = {
    IconBriefcase2Filled: true
  }

  it('renders a fallback message when there are no offers', () => {
    const wrapper = mount(OffersEdit, {
      props: { offers: [] },
      global: { stubs: globalStubs }
    })

    expect(wrapper.text()).toContain('profiles.noOffers')
  })

  it('renders up to 4 offers in the list', () => {
    const offers = generateMockOffers(3)
    const wrapper = mount(OffersEdit, {
      props: { offers },
      global: { stubs: globalStubs }
    })

    const offerTitles = wrapper.findAll('h3')
    expect(offerTitles.length).toBe(3)
    
    expect(wrapper.text()).toContain('Offer Title 1')
    expect(wrapper.text()).toContain('Description for offer 1')
    
    expect(wrapper.text()).not.toContain('profiles.noOffers')
  })

  it('truncates the view and renders exactly 4 offers even if more are provided', () => {
    const offers = generateMockOffers(10)
    const wrapper = mount(OffersEdit, {
      props: { offers },
      global: { stubs: globalStubs }
    })

    const offerTitles = wrapper.findAll('h3')
    expect(offerTitles.length).toBe(4)
    
    expect(wrapper.text()).not.toContain('Offer Title 5')
  })
})