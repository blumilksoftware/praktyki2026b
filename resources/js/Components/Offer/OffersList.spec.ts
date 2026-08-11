import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import OffersList from '@/Components/Offer/OffersList.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key }),
}))

vi.mock('@inertiajs/vue3', () => ({
  Link: {
    props: ['href', 'method', 'as'],
    template: '<a :href="href"><slot /></a>',
  },
}))

const generateOffers = (count: number) => Array.from({ length: count }, (_, i) => ({
  id: i + 1,
  title: `Offer ${i + 1}`,
  city: 'Wrocław',
  work_mode: 'remote',
  start_date: '2026-09-01',
  end_date: '2026-12-01',
  spots: 5,
  remaining_spots: 5,
  company: { name: `Company ${i + 1}`, logo_path: null, is_verified: false },
}))

describe('OffersList.vue', () => {
  it('renders a card for every offer', () => {
    const offers = generateOffers(3)
    const wrapper = mount(OffersList, { props: { offers } })

    expect(wrapper.findAll('article').length).toBe(3)
    expect(wrapper.text()).toContain('Offer 1')
    expect(wrapper.text()).toContain('Offer 3')
  })

  it('shows the empty state when there are no offers', () => {
    const wrapper = mount(OffersList, { props: { offers: [] } })

    expect(wrapper.findAll('article').length).toBe(0)
    expect(wrapper.text()).toContain('student.offers.empty.title')
    expect(wrapper.text()).toContain('student.offers.empty.description')
  })

  it('allows overriding the empty state copy', () => {
    const wrapper = mount(OffersList, {
      props: {
        offers: [],
        emptyTitle: 'No favourites yet',
        emptyDescription: 'Save an offer to see it here.',
      },
    })

    expect(wrapper.text()).toContain('No favourites yet')
    expect(wrapper.text()).toContain('Save an offer to see it here.')
  })

  it('marks offers as favorite based on their is_favorite field', () => {
    const offers = generateOffers(2).map((offer, index) => ({ ...offer, is_favorite: index === 1 }))
    const wrapper = mount(OffersList, {
      props: { offers, canApply: true },
    })

    const favoriteButtons = wrapper.findAll('button').filter((btn) => btn.attributes('aria-pressed') !== undefined)
    expect(favoriteButtons[0].attributes('aria-pressed')).toBe('false')
    expect(favoriteButtons[1].attributes('aria-pressed')).toBe('true')
  })
})
