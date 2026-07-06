import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import RegisterStudent from '@/Pages/Auth/RegisterStudent.vue'
import { ROUTES } from '@/Helpers/routes'
import en from '@/lang/en.json'

const i18n = createI18n({
  legacy: false,
  locale: 'en',
  messages: {
    en: en,
  },
})

const post = vi.fn()

const mockFormState = {
  errors: {} as Record<string, string>,
}

vi.mock('@inertiajs/vue3', async () => {
  const actual = await vi.importActual<typeof import('@inertiajs/vue3')>('@inertiajs/vue3')
  return {
    ...actual,
    Head: { template: '<div />' },
    Link: {
      props: ['href'],
      template: '<a :href="href"><slot /></a>',
    },
    useForm: () => ({
      first_name: '',
      last_name: '',
      email: '',
      password: '',
      password_confirmation: '',
      university: '',
      terms: false,
      get errors() {
        return mockFormState.errors
      },
      processing: false,
      post,
    }),
  }
})

describe('RegisterStudent', () => {
  beforeEach(() => {
    mockFormState.errors = {}
    post.mockClear()
  })

  it('renders all required registration fields', () => {
    const wrapper = mount(RegisterStudent, {
      global: { plugins: [i18n] },
    })
    expect(wrapper.find('#first_name').exists()).toBe(true)
    expect(wrapper.find('#last_name').exists()).toBe(true)
    expect(wrapper.find('#email').exists()).toBe(true)
    expect(wrapper.find('#password').exists()).toBe(true)
    expect(wrapper.find('#password_confirmation').exists()).toBe(true)
    expect(wrapper.find('#university').exists()).toBe(true)
    expect(wrapper.find('#terms').exists()).toBe(true)
  })

  it('renders Google sign-up link', () => {
    const wrapper = mount(RegisterStudent, {
      global: { plugins: [i18n] },
    })
    expect(wrapper.find(`a[href="${ROUTES.GOOGLE_AUTH}"]`).exists()).toBe(true)
  })

  it('submits the registration form to the backend', async () => {
    const wrapper = mount(RegisterStudent, {
      global: { plugins: [i18n] },
    })

    await wrapper.find('form').trigger('submit')

    expect(post).toHaveBeenCalledWith(ROUTES.REGISTER_STUDENT, {
      preserveScroll: true,
    })
  })

  it('shows server-side errors next to the relevant fields', () => {
    mockFormState.errors = {
      first_name: 'The first name field is required.',
      email: 'The email address field must be a valid email address.',
      terms: 'The terms field must be accepted.',
    }

    const wrapper = mount(RegisterStudent, {
      global: { plugins: [i18n] },
    })

    expect(wrapper.text()).toContain('The first name field is required.')
    expect(wrapper.text()).toContain('The email address field must be a valid email address.')
    expect(wrapper.text()).toContain('The terms field must be accepted.')
  })

  it('renders the shortened Google sign-up button text', () => {
    const wrapper = mount(RegisterStudent, {
      global: { plugins: [i18n] },
    })
    const googleLink = wrapper.find(`a[href="${ROUTES.GOOGLE_AUTH}"]`)
    expect(googleLink.exists()).toBe(true)
    expect(googleLink.text()).toContain('Google')
    expect(googleLink.text()).not.toContain('Sign up with Google')
  })

  it('links company tab to company registration', () => {
    const wrapper = mount(RegisterStudent, {
      global: { plugins: [i18n] },
    })

    expect(wrapper.find(`a[href="${ROUTES.REGISTER_COMPANY}"]`).exists()).toBe(true)
  })

  it('marks student tab as active', () => {
    const wrapper = mount(RegisterStudent, {
      global: { plugins: [i18n] },
    })

    const activeTab = wrapper.find('[aria-current="page"]')
    expect(activeTab.exists()).toBe(true)
    expect(activeTab.text()).toContain('Student')
  })
})
