import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi } from 'vitest'
import OfferCard from '@/Components/Offer/OfferCard.vue'
import en from '@/lang/en.json'

vi.mock('@inertiajs/vue3', () => ({
  Link: { template: '<a><slot /></a>' },
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const baseOffer = {
  id: 'offer-1',
  title: 'Frontend Intern',
  city: 'Warszawa',
  work_mode: 'remote',
  status: 'published',
  start_date: '2026-09-01',
  end_date: '2026-10-01',
  remaining_spots: 3,
  company: {
    name: 'Blumilk',
    logo_path: null,
    is_verified: true,
  },
}

function mountCard(props = {}) {
  return mount(OfferCard, {
    props: {
      offer: baseOffer,
      isFavorite: false,
      ...props,
    },
    global: {
      plugins: [i18n],
    },
  })
}

describe('OfferCard', () => {
  it('renders offer and company details', () => {
    const wrapper = mountCard()

    expect(wrapper.text()).toContain('Frontend Intern')
    expect(wrapper.text()).toContain('Blumilk')
    expect(wrapper.text()).toContain('Warszawa')
    expect(wrapper.text()).toContain('Verified')
  })

  it('shows an empty bookmark and "Add to favorites" label when not saved', () => {
    const wrapper = mountCard({ isFavorite: false })

    expect(wrapper.find('button[aria-pressed="false"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Add to favorites')
  })

  it('shows a filled bookmark and "Remove from favorites" label when saved', () => {
    const wrapper = mountCard({ isFavorite: true })

    expect(wrapper.find('button[aria-pressed="true"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Remove from favorites')
  })

  it('emits toggle-favorite with the offer id when the bookmark is clicked', async () => {
    const wrapper = mountCard()

    await wrapper.find('button[aria-pressed="false"]').trigger('click')

    expect(wrapper.emitted('toggle-favorite')).toEqual([['offer-1']])
  })

  it('shows the expired badge and hides the apply button for expired offers', () => {
    const wrapper = mountCard({ offer: { ...baseOffer, status: 'expired' } })

    expect(wrapper.text()).toContain('Expired')
    expect(wrapper.text()).toContain('No longer active')
    expect(wrapper.text()).not.toContain('Apply now')
  })

  it('shows the apply action for active offers', () => {
    const wrapper = mountCard({ offer: { ...baseOffer, status: 'published' } })

    expect(wrapper.text()).not.toContain('Expired')
    expect(wrapper.text()).toContain('Apply now')
  })
})
