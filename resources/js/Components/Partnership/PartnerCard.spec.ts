import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PartnerCard from '@/Components/Partnership/PartnerCard.vue'

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
  Link: {
    props: ['href'],
    template: '<a :href="href"><slot /></a>',
  },
}))

const basePartner = {
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
    props: ['open', 'partnerName', 'processing', 'action', 'namespace', 'error'],
    template: '<div v-if="open" class="stub-confirm-modal" :data-action="action"><p v-if="error">{{ error }}</p><button @click="$emit(\'confirm\')">confirm</button></div>',
  },
}

describe('PartnerCard.vue', () => {
  const createWrapper = (status = 'none', overrides = {}) => mount(PartnerCard, {
    props: {
      partner: { ...basePartner, partnership_status: status, ...overrides },
      namespace: 'university.companies',
      actionUrl: '/university/companies/abc-123/partnership',
      acceptUrl: '/university/companies/abc-123/partnership/accept',
    },
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

  it('falls back to the partner initial when there is no logo path', () => {
    const wrapper = createWrapper()

    expect(wrapper.find('img').exists()).toBe(false)
    expect(wrapper.text()).toContain('A')
  })

  it('hides the offers count when the partner has no offers field', () => {
    const wrapper = createWrapper('none', { active_offers_count: undefined })

    expect(wrapper.text()).not.toContain('offersCount')
  })

  it('offers a propose action when there is no partnership', () => {
    expect(buttons(createWrapper('none'))).toEqual(['university.companies.card.propose'])
  })

  it('offers a cancel action for a request we sent', () => {
    expect(buttons(createWrapper('pending_outgoing'))).toEqual(['university.companies.card.cancel'])
  })

  it('offers decline before accept for an incoming request', () => {
    expect(buttons(createWrapper('pending_incoming'))).toEqual([
      'university.companies.card.decline',
      'university.companies.card.accept',
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

  it('posts to the action url after confirming the proposal', async () => {
    const wrapper = createWrapper('none')

    await wrapper.find('button').trigger('click')
    await wrapper.find('.stub-confirm-modal button').trigger('click')

    expect(routerPost).toHaveBeenCalledTimes(1)
    expect(routerPost.mock.calls[0][0]).toBe('/university/companies/abc-123/partnership')
  })

  it('accepts an incoming request immediately, without a confirmation modal', async () => {
    const wrapper = createWrapper('pending_incoming')

    await wrapper.findAll('button')[1].trigger('click')

    expect(wrapper.find('.stub-confirm-modal').exists()).toBe(false)
    expect(routerPatch).toHaveBeenCalledTimes(1)
    expect(routerPatch.mock.calls[0][0]).toBe('/university/companies/abc-123/partnership/accept')
  })

  it('asks for confirmation before declining an incoming request', async () => {
    const wrapper = createWrapper('pending_incoming')

    await wrapper.findAll('button')[0].trigger('click')

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

    await wrapper.setProps({ partner: { ...basePartner, partnership_status: 'active' } })

    expect(wrapper.text()).toContain('university.companies.status.active')
    expect(buttons(wrapper)).toEqual(['university.companies.card.end'])
  })

  it('shows a validation error when the propose request fails', async () => {
    const wrapper = createWrapper('none')

    await wrapper.find('button').trigger('click')
    await wrapper.find('.stub-confirm-modal button').trigger('click')

    const options = routerPost.mock.calls[0][2]
    options.onError({ company: 'Already partners.' })
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Already partners.')
  })

  it('shows a validation error when accepting fails', async () => {
    const wrapper = createWrapper('pending_incoming')

    await wrapper.findAll('button')[1].trigger('click')

    const options = routerPatch.mock.calls[0][2]
    options.onError({ company: 'Request no longer pending.' })
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Request no longer pending.')
  })
})
