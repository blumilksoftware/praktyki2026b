import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import CompanyCard from '@/Components/University/CompanyCard.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key: string, params?: Record<string, unknown>) => (params ? `${key}:${JSON.stringify(params)}` : key),
  }),
}))

const { routerPost, routerDelete, routerPatch } = vi.hoisted(() => ({
  routerPost: vi.fn(),
  routerDelete: vi.fn(),
  routerPatch: vi.fn(),
}))

vi.mock('@inertiajs/vue3', () => ({
  router: { post: routerPost, delete: routerDelete, patch: routerPatch },
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
    props: ['open', 'partnerName', 'processing', 'action', 'namespace'],
    template: '<div v-if="open" class="stub-confirm-modal" :data-action="action"><button @click="$emit(\'confirm\')">confirm</button></div>',
  },
}

describe('CompanyCard.vue', () => {
  const createWrapper = (status = 'none') => mount(CompanyCard, {
    props: { company: { ...baseCompany, partnership_status: status } },
    global: { stubs },
  })

  const buttons = (wrapper) => wrapper.findAll('button').map((el) => el.text())

  beforeEach(() => {
    routerPost.mockClear()
    routerDelete.mockClear()
    routerPatch.mockClear()
  })

  it('renders name, city, description, tags and offers count', () => {
    const text = createWrapper().text()

    expect(text).toContain('Acme Corp')
    expect(text).toContain('Wrocław')
    expect(text).toContain('We build things.')
    expect(text).toContain('IT')
    expect(text).toContain('university.companies.card.offersCount:{"count":4}')
  })

  it('falls back to the company initial when there is no logo path', () => {
    const wrapper = createWrapper()

    expect(wrapper.find('img').exists()).toBe(false)
    expect(wrapper.text()).toContain('A')
  })

  it('offers a propose action when there is no partnership', () => {
    expect(buttons(createWrapper('none'))).toEqual(['university.companies.card.propose'])
  })

  it('offers a cancel action for a request we sent', () => {
    expect(buttons(createWrapper('pending_outgoing'))).toEqual(['university.companies.card.cancel'])
  })

  it('offers accept and decline actions for an incoming request', () => {
    expect(buttons(createWrapper('pending_incoming'))).toEqual([
      'university.companies.card.accept',
      'university.companies.card.decline',
    ])
  })

  it('offers an end action for an active partnership', () => {
    expect(buttons(createWrapper('active'))).toEqual(['university.companies.card.end'])
  })

  it('opens the confirmation modal instead of proposing straight away', async () => {
    const wrapper = createWrapper('none')

    await wrapper.find('button').trigger('click')

    const modal = wrapper.find('.stub-confirm-modal')
    expect(modal.exists()).toBe(true)
    expect(modal.attributes('data-action')).toBe('propose')
    expect(routerPost).not.toHaveBeenCalled()
  })

  it('posts to the partnership endpoint after confirming the proposal', async () => {
    const wrapper = createWrapper('none')

    await wrapper.find('button').trigger('click')
    await wrapper.find('.stub-confirm-modal button').trigger('click')

    expect(routerPost).toHaveBeenCalledTimes(1)
    expect(routerPost.mock.calls[0][0]).toBe('/university/companies/abc-123/partnership')
  })

  it('accepts an incoming request immediately, without a confirmation modal', async () => {
    const wrapper = createWrapper('pending_incoming')

    await wrapper.findAll('button')[0].trigger('click')

    expect(wrapper.find('.stub-confirm-modal').exists()).toBe(false)
    expect(routerPatch).toHaveBeenCalledTimes(1)
    expect(routerPatch.mock.calls[0][0]).toBe('/university/companies/abc-123/partnership/accept')
  })

  it('asks for confirmation before declining an incoming request', async () => {
    const wrapper = createWrapper('pending_incoming')

    await wrapper.findAll('button')[1].trigger('click')

    const modal = wrapper.find('.stub-confirm-modal')
    expect(modal.attributes('data-action')).toBe('decline')
    expect(routerDelete).not.toHaveBeenCalled()

    await modal.find('button').trigger('click')

    expect(routerDelete).toHaveBeenCalledTimes(1)
    expect(routerDelete.mock.calls[0][0]).toBe('/university/companies/abc-123/partnership')
  })

  it('deletes the partnership after confirming the end action', async () => {
    const wrapper = createWrapper('active')

    await wrapper.find('button').trigger('click')
    expect(wrapper.find('.stub-confirm-modal').attributes('data-action')).toBe('end')

    await wrapper.find('.stub-confirm-modal button').trigger('click')

    expect(routerDelete).toHaveBeenCalledTimes(1)
  })

  it('reflects the partnership status returned by the server', async () => {
    const wrapper = createWrapper('none')

    await wrapper.setProps({ company: { ...baseCompany, partnership_status: 'active' } })

    expect(wrapper.text()).toContain('university.companies.status.active')
    expect(buttons(wrapper)).toEqual(['university.companies.card.end'])
  })
})
