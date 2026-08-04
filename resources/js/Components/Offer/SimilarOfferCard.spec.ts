import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import SimilarOfferCard from '@/Components/Offer/SimilarOfferCard.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

vi.mock('@inertiajs/vue3', () => ({
  Link: {
    props: ['href'],
    template: '<a :href="href"><slot /></a>',
  },
}))

const offer = {
  id: 'similar-1',
  title: 'Backend Intern',
  city: 'Kraków',
  work_mode: 'hybrid',
  start_date: '2026-09-01',
  end_date: '2026-12-01',
  company: {
    id: 'company-2',
    name: 'Beta Soft',
    logo_path: null,
    is_verified: true,
  },
}

describe('SimilarOfferCard.vue', () => {
  it('links to the offer detail page and shows company, title and meta', () => {
    const wrapper = mount(SimilarOfferCard, {
      props: { offer },
      global: {
        plugins: [i18n],
        stubs: {
          VerifiedBadge: {
            props: ['verified'],
            template: '<span v-if="verified" data-testid="verified-badge" />',
          },
        },
      },
    })

    const link = wrapper.find('a[href="/offers/similar-1"]')

    expect(link.exists()).toBe(true)
    expect(wrapper.text()).toContain('Beta Soft')
    expect(wrapper.text()).toContain('Backend Intern')
    expect(wrapper.text()).toContain('Kraków')
    expect(wrapper.text()).toContain('Hybrid')
    expect(wrapper.find('[data-testid="verified-badge"]').exists()).toBe(true)
  })
})
