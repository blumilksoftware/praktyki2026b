import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi } from 'vitest'
import AdminOffersTable from '@/Components/Admin/AdminOffersTable.vue'
import en from '@/lang/en.json'

const { mockGet } = vi.hoisted(() => ({ mockGet: vi.fn() }))

vi.mock('@inertiajs/vue3', () => ({
  router: { get: mockGet },
  useForm: () => ({ processing: false, errors: {}, patch: vi.fn() }),
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const offers = {
  data: [
    { id: '1', title: 'Published Offer', city: 'Wroclaw', status: 'published', company: { id: 'c1', name: 'Acme' } },
    { id: '2', title: 'Draft Offer', city: 'Krakow', status: 'draft', company: { id: 'c1', name: 'Acme' } },
  ],
  links: {},
}

const mountTable = () => mount(AdminOffersTable, {
  props: {
    offers,
    filters: { status: 'all', search: '' },
    statuses: ['draft', 'published', 'closed', 'expired'],
  },
  global: {
    plugins: [i18n],
    stubs: {
      AdminTakeDownOfferModal: { props: ['open'], template: '<div />' },
      Pagination: true,
      FilterDropdown: true,
    },
  },
})

describe('AdminOffersTable', () => {
  it('offers a take down action for a published offer', () => {
    const wrapper = mountTable()

    const row = wrapper.findAll('tbody tr').find((r) => r.text().includes('Published Offer'))

    expect(row!.text()).toContain(en.admin.offers.takeDown)
  })

  it('does not offer take down for an offer that is not published', () => {
    const wrapper = mountTable()

    const row = wrapper.findAll('tbody tr').find((r) => r.text().includes('Draft Offer'))

    expect(row!.text()).not.toContain(en.admin.offers.takeDown)
  })

  it('shows the company name for each offer', () => {
    const wrapper = mountTable()

    expect(wrapper.find('tbody').text()).toContain('Acme')
  })
})
