import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import ProfileIcon from '@/Components/Navigation/ProfileIcon.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key: string) => key,
  }),
}))

interface AuthUser {
  role: string
  first_name: string
  last_name: string
  photo_path?: string | null
  company?: { logo_path: string | null }
  university_organization?: { logo_path: string | null }
}

const { page } = vi.hoisted(() => {
  const { reactive } = require('vue')

  return {
    page: reactive({
      component: 'Company/Dashboard',
      props: { auth: { user: null as AuthUser | null } },
    }),
  }
})

vi.mock('@inertiajs/vue3', () => ({
  usePage: () => page,
  Link: {
    props: ['href'],
    template: '<a :href="href"><slot /></a>',
  },
}))

const companyUser: AuthUser = {
  role: 'companyAdmin',
  first_name: 'Nora',
  last_name: 'Ashford',
}

describe('ProfileIcon', () => {
  beforeEach(() => {
    page.props.auth.user = { ...companyUser }
  })

  it('falls back to the initials of the signed in user', () => {
    const wrapper = mount(ProfileIcon)

    expect(wrapper.text()).toContain('NA')
    expect(wrapper.find('img').exists()).toBe(false)
  })

  it('uses the company logo as the avatar', () => {
    page.props.auth.user = { ...companyUser, company: { logo_path: '/storage/logos/acme.png' } }

    const wrapper = mount(ProfileIcon)

    expect(wrapper.find('img').attributes('src')).toBe('/storage/logos/acme.png')
  })

  it('uses the university logo as the avatar', () => {
    page.props.auth.user = {
      role: 'universityAdmin',
      first_name: 'Peter',
      last_name: 'Vance',
      university_organization: { logo_path: '/storage/logos/riverside.png' },
    }

    const wrapper = mount(ProfileIcon)

    expect(wrapper.find('img').attributes('src')).toBe('/storage/logos/riverside.png')
  })

  it('prefers the photo of the user over the logo of the organization', () => {
    page.props.auth.user = {
      ...companyUser,
      photo_path: 'photos/nora.jpg',
      company: { logo_path: '/storage/logos/acme.png' },
    }

    const wrapper = mount(ProfileIcon)

    expect(wrapper.find('img').attributes('src')).toBe('/student/profile/photo')
  })

  it('opens the menu from the keyboard and reports its state', async () => {
    const wrapper = mount(ProfileIcon)
    const trigger = wrapper.find('button')

    expect(trigger.attributes('aria-expanded')).toBe('false')

    await trigger.trigger('click')

    expect(trigger.attributes('aria-expanded')).toBe('true')
    expect(wrapper.findAll('a').length).toBeGreaterThan(0)

    await wrapper.trigger('keydown.escape')

    expect(trigger.attributes('aria-expanded')).toBe('false')
  })
})
