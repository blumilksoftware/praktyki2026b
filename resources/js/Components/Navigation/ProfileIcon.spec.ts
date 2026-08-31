import { mount } from '@vue/test-utils'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import ProfileIcon from '@/Components/Navigation/ProfileIcon.vue'

const { pageProps } = vi.hoisted(() => {
  const { reactive } = require('vue')

  return {
    pageProps: reactive({
      auth: { user: { role: 'student', photo_path: null, company: null, university_organization: null } },
    }),
  }
})

vi.mock('@inertiajs/vue3', () => ({
  usePage: () => ({ props: pageProps, component: 'Student/Dashboard' }),
  Link: {
    props: ['href'],
    template: '<a :href="href"><slot /></a>',
  },
}))

describe('ProfileIcon.vue', () => {
  beforeEach(() => {
    pageProps.auth = { user: { role: 'student', photo_path: null, company: null, university_organization: null } }
  })

  it('renders the trigger as a native button so it is keyboard operable', () => {
    const wrapper = mount(ProfileIcon, { global: { stubs: ['IconUser'] } })

    expect(wrapper.find('button').exists()).toBe(true)
  })

  it('toggles the menu when the trigger button is clicked', async () => {
    const wrapper = mount(ProfileIcon, { global: { stubs: ['IconUser'] } })

    expect(wrapper.find('button').attributes('aria-expanded')).toBe('false')

    await wrapper.find('button').trigger('click')

    expect(wrapper.find('button').attributes('aria-expanded')).toBe('true')
  })
})
