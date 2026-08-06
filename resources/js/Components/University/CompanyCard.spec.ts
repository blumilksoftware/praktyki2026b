import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import CompanyCard from '@/Components/University/CompanyCard.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key: string, params?: Record<string, unknown>) => (params ? `${key}:${JSON.stringify(params)}` : key),
  }),
}))

const { routerPost, routerDelete } = vi.hoisted(() => ({
  routerPost: vi.fn(),
  routerDelete: vi.fn(),
}))

vi.mock('@inertiajs/vue3', () => ({
  router: { post: routerPost, delete: routerDelete },
}))

const baseCompany = {
  id: 'abc-123',
  name: 'Acme Corp',
  city: 'Wrocław',
  description: 'We build things.',
  tags: ['IT', 'Laravel'],
  logo_path: null,
  active_offers_count: 4,
  partnership_status: 'none',
}

const stubs = {
  PartnerConfirmModal: {
    props: ['open', 'companyName', 'processing', 'action'],
    template: '<div v-if="open" class="stub-confirm-modal" :data-action="action"><button @click="$emit(\'confirm\')">confirm</button></div>',
  },
}

describe('CompanyCard.vue', () => {
  const createWrapper = (props = {}) => mount(CompanyCard, {
    props: { company: baseCompany, ...props },
    global: { stubs },
  })

  beforeEach(() => {
    routerPost.mockClear()
    routerDelete.mockClear()
  })

  it('renders name, city, description, tags and offers count', () => {
    const wrapper = createWrapper()
    const text = wrapper.text()

    expect(text).toContain('Acme Corp')
    expect(text).toContain('Wrocław')
    expect(text).toContain('We build things.')
    expect(text).toContain('IT')
    expect(text).toContain('Laravel')
    expect(text).toContain('university.companies.card.offersCount:{"count":4}')
  })

  it('falls back to the company initial when there is no logo path', () => {
    const wrapper = createWrapper()

    expect(wrapper.find('img').exists()).toBe(false)
    expect(wrapper.text()).toContain('A')
  })

  it('shows the company logo image when a logo path is present', () => {
    const wrapper = createWrapper({ company: { ...baseCompany, logo_path: '/logos/acme.png' } })

    const img = wrapper.find('img')
    expect(img.exists()).toBe(true)
    expect(img.attributes('src')).toBe('/logos/acme.png')
  })

  it('shows an "add partner" button when there is no partnership yet', () => {
    const wrapper = createWrapper()

    expect(wrapper.text()).toContain('university.companies.card.addPartner')
    expect(wrapper.text()).not.toContain('university.companies.card.removePartner')
  })

  it('shows a "remove partner" button when the company is already a partner', () => {
    const wrapper = createWrapper({ company: { ...baseCompany, partnership_status: 'active' } })

    expect(wrapper.text()).toContain('university.companies.card.removePartner')
  })

  it('opens the confirmation modal instead of adding straight away', async () => {
    const wrapper = createWrapper()

    await wrapper.find('button').trigger('click')

    const modal = wrapper.find('.stub-confirm-modal')
    expect(modal.exists()).toBe(true)
    expect(modal.attributes('data-action')).toBe('add')
    expect(routerPost).not.toHaveBeenCalled()
  })

  it('posts to the partnership endpoint after confirming the add', async () => {
    const wrapper = createWrapper()

    await wrapper.find('button').trigger('click')
    await wrapper.find('.stub-confirm-modal button').trigger('click')

    expect(routerPost).toHaveBeenCalledTimes(1)
    const [url, data] = routerPost.mock.calls[0]
    expect(url).toBe('/university/companies/abc-123/partnership')
    expect(data).toEqual({})
  })

  it('reflects the partnership status returned by the server after adding', async () => {
    const wrapper = createWrapper()

    await wrapper.find('button').trigger('click')
    await wrapper.find('.stub-confirm-modal button').trigger('click')
    routerPost.mock.calls[0][2].onFinish()
    await wrapper.setProps({ company: { ...baseCompany, partnership_status: 'active' } })

    expect(wrapper.text()).toContain('university.companies.card.removePartner')
    expect(wrapper.text()).toContain('university.companies.status.active')
  })

  it('opens the confirmation modal instead of deleting straight away', async () => {
    const wrapper = createWrapper({ company: { ...baseCompany, partnership_status: 'active' } })

    await wrapper.find('button').trigger('click')

    const modal = wrapper.find('.stub-confirm-modal')
    expect(modal.exists()).toBe(true)
    expect(modal.attributes('data-action')).toBe('remove')
    expect(routerDelete).not.toHaveBeenCalled()
  })

  it('deletes the partnership after confirming in the modal', async () => {
    const wrapper = createWrapper({ company: { ...baseCompany, partnership_status: 'active' } })

    await wrapper.find('button').trigger('click')
    await wrapper.find('.stub-confirm-modal button').trigger('click')

    expect(routerDelete).toHaveBeenCalledTimes(1)
    expect(routerDelete.mock.calls[0][0]).toBe('/university/companies/abc-123/partnership')
  })

  it('closes the modal and shows the add button once the server drops the partnership', async () => {
    const wrapper = createWrapper({ company: { ...baseCompany, partnership_status: 'active' } })

    await wrapper.find('button').trigger('click')
    await wrapper.find('.stub-confirm-modal button').trigger('click')

    const options = routerDelete.mock.calls[0][1]
    options.onSuccess()
    options.onFinish()
    await wrapper.setProps({ company: { ...baseCompany, partnership_status: 'none' } })

    expect(wrapper.text()).toContain('university.companies.card.addPartner')
    expect(wrapper.text()).toContain('university.companies.status.none')
    expect(wrapper.find('.stub-remove-modal').exists()).toBe(false)
  })
})
