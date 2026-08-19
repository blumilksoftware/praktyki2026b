import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import Team from '@/Pages/Organization/Team.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const deleteMock = vi.fn()
const postMock = vi.fn()

vi.mock('@inertiajs/vue3', async () => {
  const actual = await vi.importActual('@inertiajs/vue3')

  return {
    ...actual,
    Head: { template: '<div />' },
    useForm: () => ({
      processing: false,
      errors: {},
      delete: deleteMock,
      post: postMock,
      reset: vi.fn(),
      clearErrors: vi.fn(),
    }),
  }
})

describe('Organization/Team', () => {
  const mountTeam = () => mount(Team, {
    props: {
      organization: { id: 'org-1', name: 'Example Labs', type: 'company' },
      members: [
        { id: 'member-1', name: 'Jan Kowalski', email: 'jan@example.com', role: 'companyAdmin', joinedAt: '2025-01-15T00:00:00.000Z' },
      ],
      invitations: [
        { id: 'invitation-1', email: 'anna@example.com' },
      ],
    },
    global: {
      plugins: [i18n],
      stubs: {
        BaseLayout: { template: '<div><slot /></div>' },
        BaseModal: {
          props: ['open', 'title'],
          template: '<section v-if="open"><h2>{{ title }}</h2><slot /></section>',
        },
        BaseButton: { template: '<button><slot /></button>' },
      },
    },
  })

  it('renders members and pending invitations with translated labels', () => {
    const wrapper = mountTeam()

    expect(wrapper.text()).toContain('Current team members')
    expect(wrapper.text()).toContain('Jan Kowalski')
    expect(wrapper.text()).toContain('Administrator')
    expect(wrapper.text()).toContain('Pending invitations')
    expect(wrapper.text()).toContain('anna@example.com')
  })

  it('opens a confirmation modal before removing a member', async () => {
    const wrapper = mountTeam()

    await wrapper.find('button[aria-label="Remove"]').trigger('click')

    expect(wrapper.text()).toContain('Remove member')
    expect(wrapper.text()).toContain('Are you sure you want to remove Jan Kowalski from the team?')
  })

  it('sends a delete request when the confirmation is accepted', async () => {
    deleteMock.mockClear()
    const wrapper = mountTeam()

    await wrapper.find('button[aria-label="Remove"]').trigger('click')
    await wrapper.findAll('button').find((button) => button.text() === 'Remove member')?.trigger('click')

    expect(deleteMock).toHaveBeenCalledWith('/company/team/members/member-1', expect.objectContaining({ preserveScroll: true }))
  })

  it('sends a revoke request for pending invitations', async () => {
    deleteMock.mockClear()
    const wrapper = mountTeam()

    await wrapper.findAll('button').find((button) => button.text() === 'Revoke')?.trigger('click')

    expect(deleteMock).toHaveBeenCalledWith('/company/team/invitations/invitation-1', expect.objectContaining({ preserveScroll: true }))
  })

  it('opens an invite modal and submits an invitation email', async () => {
    postMock.mockClear()
    const wrapper = mountTeam()

    await wrapper.findAll('button').find((button) => button.text() === 'Invite member')?.trigger('click')

    const input = wrapper.find('#team-invite-email')
    await input.setValue('new-member@example.com')
    await wrapper.findAll('button').find((button) => button.text() === 'Send invitation')?.trigger('click')

    expect(postMock).toHaveBeenCalledWith('/company/team/invitations', expect.objectContaining({ preserveScroll: true }))
  })
})
