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

  it('keeps the filtered company selectable when it has no offers left', () => {
    const wrapper = mountTable({
      filters: { status: 'all', search: '', company: 'c9' },
      filterCompany: { id: 'c9', name: 'Gone Quiet' },
      companies: [{ id: 'c1', name: 'Acme' }],
    })

    const companyDropdown = wrapper.findAllComponents({ name: 'FilterDropdown' })[1]

    expect(companyDropdown.props('options')).toEqual([
      { value: '', label: en.admin.offers.allCompanies },
      { value: 'c9', label: 'Gone Quiet' },
      { value: 'c1', label: 'Acme' },
    ])
  })

  it('does not repeat the filtered company when it is already listed', () => {
    const wrapper = mountTable({
      filters: { status: 'all', search: '', company: 'c1' },
      filterCompany: { id: 'c1', name: 'Acme' },
      companies: [{ id: 'c1', name: 'Acme' }],
    })

    const companyDropdown = wrapper.findAllComponents({ name: 'FilterDropdown' })[1]

    expect(companyDropdown.props('options')).toEqual([
      { value: '', label: en.admin.offers.allCompanies },
      { value: 'c1', label: 'Acme' },
    ])
  })
  it('offers every company that has offers in the company filter', () => {
    const wrapper = mountTable({
      companies: [{ id: 'c1', name: 'Acme' }, { id: 'c2', name: 'Beta Soft' }],
    })

    const companyDropdown = wrapper.findAllComponents({ name: 'FilterDropdown' })[1]

    expect(companyDropdown.props('options')).toEqual([
      { value: '', label: en.admin.offers.allCompanies },
      { value: 'c1', label: 'Acme' },
      { value: 'c2', label: 'Beta Soft' },
    ])
    expect(companyDropdown.props('searchable')).toBe(true)
  })

  it('narrows the query to the company chosen in the filter', async () => {
    mockGet.mockClear()

    const wrapper = mountTable({ companies: [{ id: 'c2', name: 'Beta Soft' }] })

    wrapper.findAllComponents({ name: 'FilterDropdown' })[1].vm.$emit('update:modelValue', 'c2')
    await wrapper.vm.$nextTick()

    expect(mockGet).toHaveBeenCalledWith(
      '/admin/offers',
      expect.objectContaining({ company: 'c2' }),
      expect.anything(),
    )
  })
})
