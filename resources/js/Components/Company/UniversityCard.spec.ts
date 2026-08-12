import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import UniversityCard from '@/Components/Company/UniversityCard.vue'

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

const baseUniversity = {
  id: 'uni-1',
  name: 'Politechnika Testowa',
  city: 'Legnica',
  logo_path: null,
  partnership_status: 'none',
}

const stubs = {
  PartnerConfirmModal: {
    props: ['open', 'partnerName', 'processing', 'action', 'namespace', 'error'],
    template: '<div v-if="open" class="stub-confirm-modal" :data-action="action"><p v-if="error">{{ error }}</p><button @click="$emit(\'confirm\')">confirm</button></div>',
  },
}

describe('UniversityCard.vue', () => {
  const createWrapper = (status = 'none') => mount(UniversityCard, {
    props: { university: { ...baseUniversity, partnership_status: status } },
    global: { stubs },
  })

  const buttons = (wrapper) => wrapper.findAll('button').map((el) => el.text())

  beforeEach(() => {
    routerPost.mockClear()
    routerDelete.mockClear()
    routerPatch.mockClear()
  })

  it('renders the university name and city', () => {
    const text = createWrapper().text()

    expect(text).toContain('Politechnika Testowa')
    expect(text).toContain('Legnica')
  })

  it('falls back to the university initial when there is no logo path', () => {
    const wrapper = createWrapper()

    expect(wrapper.find('img').exists()).toBe(false)
    expect(wrapper.text()).toContain('P')
  })

  it('offers a propose action when there is no partnership', () => {
    expect(buttons(createWrapper('none'))).toEqual(['company.universities.card.propose'])
  })

  it('offers a cancel action for a request we sent', () => {
    expect(buttons(createWrapper('pending_outgoing'))).toEqual(['company.universities.card.cancel'])
  })

  it('offers accept and decline actions for an incoming request', () => {
    expect(buttons(createWrapper('pending_incoming'))).toEqual([
      'company.universities.card.accept',
      'company.universities.card.decline',
    ])
  })

  it('offers an end action for an active partnership', () => {
    expect(buttons(createWrapper('active'))).toEqual(['company.universities.card.end'])
  })

  it('posts to the partnership endpoint after confirming the proposal', async () => {
    const wrapper = createWrapper('none')

    await wrapper.find('button').trigger('click')
    expect(routerPost).not.toHaveBeenCalled()

    await wrapper.find('.stub-confirm-modal button').trigger('click')

    expect(routerPost).toHaveBeenCalledTimes(1)
    expect(routerPost.mock.calls[0][0]).toBe('/company/universities/uni-1/partnership')
  })

  it('accepts an incoming request immediately, without a confirmation modal', async () => {
    const wrapper = createWrapper('pending_incoming')

    await wrapper.findAll('button')[0].trigger('click')

    expect(wrapper.find('.stub-confirm-modal').exists()).toBe(false)
    expect(routerPatch).toHaveBeenCalledTimes(1)
    expect(routerPatch.mock.calls[0][0]).toBe('/company/universities/uni-1/partnership/accept')
  })

  it('asks for confirmation before declining an incoming request', async () => {
    const wrapper = createWrapper('pending_incoming')

    await wrapper.findAll('button')[1].trigger('click')

    const modal = wrapper.find('.stub-confirm-modal')
    expect(modal.attributes('data-action')).toBe('decline')

    await modal.find('button').trigger('click')

    expect(routerDelete).toHaveBeenCalledTimes(1)
    expect(routerDelete.mock.calls[0][0]).toBe('/company/universities/uni-1/partnership')
  })

  it('shows a validation error when the propose request fails', async () => {
    const wrapper = createWrapper('none')

    await wrapper.find('button').trigger('click')
    await wrapper.find('.stub-confirm-modal button').trigger('click')

    const options = routerPost.mock.calls[0][2]
    options.onError({ university: 'Already partners.' })
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Already partners.')
  })

  it('shows a validation error when accepting fails', async () => {
    const wrapper = createWrapper('pending_incoming')

    await wrapper.findAll('button')[0].trigger('click')

    const options = routerPatch.mock.calls[0][2]
    options.onError({ university: 'Request no longer pending.' })
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Request no longer pending.')
  })

  it('reflects the partnership status returned by the server', async () => {
    const wrapper = createWrapper('none')

    await wrapper.setProps({ university: { ...baseUniversity, partnership_status: 'active' } })

    expect(wrapper.text()).toContain('company.universities.status.active')
    expect(buttons(wrapper)).toEqual(['company.universities.card.end'])
  })
})
