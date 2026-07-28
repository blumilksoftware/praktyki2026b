import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import OfferCard from '@/Components/Offer/OfferCard.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key: string, params?: Record<string, unknown>) => (params ? `${key}:${JSON.stringify(params)}` : key),
  }),
}))

vi.mock('@inertiajs/vue3', () => ({
  Link: {
    props: ['href', 'method', 'as'],
    template: '<a :href="href"><slot /></a>',
  },
}))

const baseOffer = {
  id: 42,
  title: 'Frontend Developer Intern',
  city: 'Wrocław',
  work_mode: 'remote',
  start_date: '2026-09-01',
  end_date: '2026-12-01',
  spots: 5,
  remaining_spots: 3,
  company: {
    name: 'Acme Corp',
    logo_path: null,
    is_verified: true,
  },
}

describe('OfferCard.vue', () => {
  const createWrapper = (props = {}) => mount(OfferCard, {
    props: { offer: baseOffer, ...props },
  })

  it('renders company name, title, city, date range and remaining spots', () => {
    const wrapper = createWrapper()
    const text = wrapper.text()

    expect(text).toContain('Acme Corp')
    expect(text).toContain('Frontend Developer Intern')
    expect(text).toContain('Wrocław')
    expect(text).toContain('2026-09-01 - 2026-12-01')
    expect(text).toContain('3')
  })

  it('shows the translated work mode label', () => {
    const wrapper = createWrapper()

    expect(wrapper.text()).toContain('student.workModes.remote')
  })

  it('shows the verified badge when the company is verified', () => {
    const wrapper = createWrapper()

    expect(wrapper.text()).toContain('student.offers.card.verified')
  })

  it('hides the verified badge when the company is not verified', () => {
    const wrapper = createWrapper({
      offer: { ...baseOffer, company: { ...baseOffer.company, is_verified: false } },
    })

    expect(wrapper.text()).not.toContain('student.offers.card.verified')
  })

  it('shows the company logo image when a logo path is present', () => {
    const wrapper = createWrapper({
      offer: { ...baseOffer, company: { ...baseOffer.company, logo_path: '/logos/acme.png' } },
    })

    const img = wrapper.find('img')
    expect(img.exists()).toBe(true)
    expect(img.attributes('src')).toBe('/logos/acme.png')
  })

  it('falls back to the company initial when there is no logo path', () => {
    const wrapper = createWrapper()

    expect(wrapper.find('img').exists()).toBe(false)
    expect(wrapper.text()).toContain('A')
  })

  it('emits toggle-favorite with the offer id when the favorite button is clicked', async () => {
    const wrapper = createWrapper()

    const favoriteButton = wrapper.findAll('button').find((btn) => btn.attributes('aria-pressed') !== undefined)
    await favoriteButton!.trigger('click')

    expect(wrapper.emitted('toggle-favorite')).toBeTruthy()
    expect(wrapper.emitted('toggle-favorite')![0]).toEqual([42])
  })

  it('reflects the isFavorite prop on the favorite button', () => {
    const wrapper = createWrapper({ isFavorite: true })

    const favoriteButton = wrapper.findAll('button').find((btn) => btn.attributes('aria-pressed') !== undefined)
    expect(favoriteButton!.attributes('aria-pressed')).toBe('true')
  })

  it('links the apply action to the correct offer endpoint', () => {
    const wrapper = createWrapper()

    expect(wrapper.find('a').attributes('href')).toBe('/student/offers/42/apply')
  })

  it('keeps the disabled "show on map" button focusable and explained via aria-disabled instead of the native disabled attribute', () => {
    const wrapper = createWrapper()

    const mapButton = wrapper.findAll('button').find((btn) => btn.text() === 'student.offers.card.showOnMap')
    expect(mapButton!.attributes('disabled')).toBeUndefined()
    expect(mapButton!.attributes('aria-disabled')).toBe('true')
    expect(mapButton!.attributes('title')).toBe('student.offers.card.mapComingSoon')
  })
})
