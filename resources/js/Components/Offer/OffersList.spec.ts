import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi } from 'vitest'
import OffersList from '@/Components/Offer/OffersList.vue'
import en from '@/lang/en.json'

vi.mock('@inertiajs/vue3', () => ({
  Link: { template: '<a><slot /></a>' },
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

function makeOffer(id: string, overrides = {}) {
  return {
    id,
    title: `Offer ${id}`,
    city: 'Warszawa',
    work_mode: 'remote',
    status: 'published',
    start_date: '2026-09-01',
    end_date: '2026-10-01',
    remaining_spots: 2,
    company: { name: 'Blumilk', logo_path: null, is_verified: false },
    ...overrides,
  }
}

function mountList(props = {}) {
  return mount(OffersList, {
    props: {
      offers: [],
      favoriteIds: [],
      ...props,
    },
    global: {
      plugins: [i18n],
    },
  })
}

describe('OffersList', () => {
  it('shows the default empty state when there are no offers', () => {
    const wrapper = mountList()

    expect(wrapper.text()).toContain('No offers match the filters')
  })

  it('shows a custom empty state when provided', () => {
    const wrapper = mountList({
      emptyTitle: 'No favorites yet',
      emptyDescription: 'Save an offer to see it here.',
    })

    expect(wrapper.text()).toContain('No favorites yet')
    expect(wrapper.text()).toContain('Save an offer to see it here.')
  })

  it('renders one card per offer', () => {
    const wrapper = mountList({
      offers: [makeOffer('1'), makeOffer('2'), makeOffer('3')],
    })

    expect(wrapper.text()).toContain('Offer 1')
    expect(wrapper.text()).toContain('Offer 2')
    expect(wrapper.text()).toContain('Offer 3')
  })

  it('marks only offers included in favoriteIds as saved', () => {
    const wrapper = mountList({
      offers: [makeOffer('1'), makeOffer('2')],
      favoriteIds: ['2'],
    })

    const buttons = wrapper.findAll('button[aria-pressed]')
    expect(buttons[0].attributes('aria-pressed')).toBe('false')
    expect(buttons[1].attributes('aria-pressed')).toBe('true')
  })

  it('re-emits toggle-favorite from a card with the offer id', async () => {
    const wrapper = mountList({
      offers: [makeOffer('1')],
    })

    await wrapper.find('button[aria-pressed]').trigger('click')

    expect(wrapper.emitted('toggle-favorite')).toEqual([['1']])
  })
})
