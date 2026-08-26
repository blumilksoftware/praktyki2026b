import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi } from 'vitest'
import VerificationTable from '@/Components/Admin/VerificationTable.vue'
import en from '@/lang/en.json'

const { mockGet } = vi.hoisted(() => ({ mockGet: vi.fn() }))

vi.mock('@inertiajs/vue3', () => ({
  router: { get: mockGet },
  useForm: () => ({
    processing: false,
    transform: vi.fn().mockReturnThis(),
    post: vi.fn(),
  }),
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const universities = {
  data: [
    { id: 'u1', name: 'AGH', city: 'Krakow', email: 'agh@example.com', phone: '123456789', created_at: '2026-01-01', verification_status: 'pending' },
  ],
  current_page: 1,
  links: {},
  meta: {},
}

const companies = {
  data: [
    { id: 'c1', name: 'Acme', city: 'Wroclaw', email: 'acme@example.com', phone: '987654321', created_at: '2026-01-02', verification_status: 'verified' },
  ],
  current_page: 1,
  links: {},
  meta: {},
}

const mountTable = () => mount(VerificationTable, {
  props: {
    companies,
    universities,
    companyStats: { pending: 0, verified: 1, rejected: 0 },
    universityStats: { pending: 1, verified: 0, rejected: 0 },
    filters: { status: 'all', search: '', sort_key: 'created_at', sort_dir: 'asc' },
  },
  global: {
    plugins: [i18n],
    stubs: {
      AdminDeleteOrganizationModal: true,
      Pagination: true,
      FilterDropdown: true,
    },
  },
})

describe('VerificationTable', () => {
  it('links a university row to its public profile page', () => {
    const wrapper = mountTable()

    const row = wrapper.findAll('tbody tr').find((r) => r.text().includes('AGH'))

    expect(row!.attributes('class')).toContain('cursor-pointer')
  })

  it('does not render a details button in the row actions', () => {
    const wrapper = mountTable()

    expect(wrapper.find('button[title="Details"]').exists()).toBe(false)
  })

  it('opens the university profile in a new tab when a row is clicked', async () => {
    const openSpy = vi.spyOn(window, 'open').mockImplementation(() => null)
    const wrapper = mountTable()

    const row = wrapper.findAll('tbody tr').find((r) => r.text().includes('AGH'))
    await row!.trigger('click')

    expect(openSpy).toHaveBeenCalledWith('/universities/u1', '_blank')
    openSpy.mockRestore()
  })

  it('opens the company profile in a new tab when a row is clicked', async () => {
    const openSpy = vi.spyOn(window, 'open').mockImplementation(() => null)
    const wrapper = mountTable()

    const companiesTab = wrapper.findAll('button').find((b) => b.text() === en.admin.verification.companies)
    await companiesTab!.trigger('click')
    const row = wrapper.findAll('tbody tr').find((r) => r.text().includes('Acme'))
    await row!.trigger('click')

    expect(openSpy).toHaveBeenCalledWith('/companies/c1', '_blank')
    openSpy.mockRestore()
  })

  it('shows the verification status as the mobile card badge', () => {
    const card = mountTable().find('article')

    expect(card.find('p').text()).toBe('AGH')
    expect(card.find('div').text()).toContain(en.admin.verification.pending)
  })

  it('does not repeat the name and verification status in the mobile card details', () => {
    const card = mountTable().find('article')

    expect(card.findAll('dt').map((dt) => dt.text())).toEqual([
      en.admin.verification.city,
      en.admin.verification.email,
      en.admin.verification.phone,
      en.admin.verification.submittedAt,
      '',
    ])
  })

  it('does not open the row link when clicking an action button', async () => {
    const openSpy = vi.spyOn(window, 'open').mockImplementation(() => null)
    const wrapper = mountTable()

    const row = wrapper.findAll('tbody tr').find((r) => r.text().includes('AGH'))
    await row!.find('button[title="Delete"]').trigger('click')

    expect(openSpy).not.toHaveBeenCalled()
    openSpy.mockRestore()
  })
})
