import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi } from 'vitest'
import AdminOffersTable from '@/Components/Admin/AdminOffersTable.vue'
import en from '@/lang/en.json'

const { mockGet } = vi.hoisted(() => ({ mockGet: vi.fn() }))

vi.mock('@inertiajs/vue3', () => ({
  router: { get: mockGet },
  useForm: () => ({ processing: false, errors: {}, patch: vi.fn() }),
  Link: { props: ['href'], template: '<a :href="href"><slot /></a>' },
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const offers = {
  data: [
    { id: '1', title: 'Published Offer', city: 'Wroclaw', status: 'published', company: { id: 'c1', name: 'Acme' } },
    { id: '2', title: 'Draft Offer', city: 'Krakow', status: 'draft', company: { id: 'c1', name: 'Acme' } },
  ],
  links: {},
}

const mountTable = (props = {}) => mount(AdminOffersTable, {
  props: {
    offers,
    filters: { status: 'all', search: '', company: '' },
    statuses: ['draft', 'published', 'closed', 'expired'],
    ...props,
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

    expect(row!.find('button').attributes('title')).toBe(en.admin.offers.takeDown)
  })

  it('does not offer take down for an offer that is not published', () => {
    const wrapper = mountTable()

    const row = wrapper.findAll('tbody tr').find((r) => r.text().includes('Draft Offer'))

    expect(row!.find('button').exists()).toBe(false)
  })

  it('shows the company name for each offer', () => {
    const wrapper = mountTable()

    expect(wrapper.find('tbody').text()).toContain('Acme')
  })

  it('titles the mobile card with the offer title instead of its id', () => {
    const card = mountTable().find('article')

    expect(card.find('p').text()).toBe('Published Offer')
  })

  it('does not repeat the offer title and status in the mobile card details', () => {
    const card = mountTable().find('article')

    expect(card.findAll('dt').map((dt) => dt.text()))
      .toEqual([en.admin.offers.company, en.admin.offers.city, ''])
  })

  it('links the company cell to the offers of that company', () => {
    const wrapper = mountTable()

    const link = wrapper.find('tbody a')

    expect(link.attributes('href')).toBe('/admin/offers?company=c1')
    expect(link.text()).toBe('Acme')
  })

  it('names the company the list is narrowed to', () => {
    const wrapper = mountTable({ filterCompany: { id: 'c1', name: 'Acme' } })

    expect(wrapper.text()).toContain('Company: Acme')
  })

  it('does not name a company when the list is not narrowed', () => {
    expect(mountTable().text()).not.toContain('Company: Acme')
  })

  it('drops the company from the query when the filter is cleared', async () => {
    mockGet.mockClear()

    const wrapper = mountTable({
      filters: { status: 'all', search: '', company: 'c1' },
      filterCompany: { id: 'c1', name: 'Acme' },
    })

    await wrapper.get(`button[aria-label="${en.admin.offers.clearCompanyFilter}"]`).trigger('click')

    expect(mockGet).toHaveBeenCalledWith(
      '/admin/offers',
      expect.objectContaining({ company: '' }),
      expect.anything(),
    )
  })
})
