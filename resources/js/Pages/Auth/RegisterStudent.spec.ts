import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import RegisterStudent from '@/Pages/Auth/RegisterStudent.vue'
import { ROUTES } from '@/Helpers/routes'
import en from '@/lang/en.json'

const validation = {
  messages: {
    required: 'The :attribute field is required.',
    email: 'The :attribute field must be a valid email address.',
    confirmed: 'The :attribute field confirmation does not match.',
    accepted: 'The :attribute field must be accepted.',
  },
  attributes: {
    first_name: 'first name',
    last_name: 'last name',
    email: 'email address',
    password: 'password',
    password_confirmation: 'password confirmation',
    terms: 'terms',
  },
}

const i18n = createI18n({
  legacy: false,
  locale: 'en',
  messages: {
    en: en,
  },
})

const post = vi.fn()

vi.mock('@inertiajs/vue3', async () => {
  const actual = await vi.importActual<typeof import('@inertiajs/vue3')>('@inertiajs/vue3')
  return {
    ...actual,
    Head: { template: '<div />' },
    Link: {
      props: ['href'],
      template: '<a :href="href"><slot /></a>',
    },
    usePage: () => ({
      props: { validation },
    }),
    useForm: () => ({
      first_name: '',
      last_name: '',
      email: '',
      password: '',
      password_confirmation: '',
      university: '',
      terms: false,
      errors: {},
      processing: false,
      clearErrors: vi.fn(),
      post,
    }),
  }
})

describe('RegisterStudent', () => {
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
    expect(wrapper.find(`a[href="${ROUTES.GOOGLE_REDIRECT}"]`).exists()).toBe(true)
  })

  it('shows client-side validation errors for empty required fields', async () => {
    const wrapper = mount(RegisterStudent, {
      global: { plugins: [i18n] },
    })
    await wrapper.find('form').trigger('submit')
    expect(wrapper.text()).toContain('The first name field is required.')
    expect(wrapper.text()).toContain('The last name field is required.')
    expect(wrapper.text()).toContain('The email address field is required.')
    expect(wrapper.text()).toContain('The password field is required.')
    expect(wrapper.text()).toContain('The password confirmation field is required.')
    expect(wrapper.text()).toContain('The terms field must be accepted.')
    expect(post).not.toHaveBeenCalled()
  })

  it('shows client-side validation error for invalid email', async () => {
    const wrapper = mount(RegisterStudent, {
      global: { plugins: [i18n] },
    })
    await wrapper.find('#first_name').setValue('John')
    await wrapper.find('#last_name').setValue('Doe')
    await wrapper.find('#email').setValue('invalid-email')
    await wrapper.find('#password').setValue('Password123!')
    await wrapper.find('#password_confirmation').setValue('Password123!')
    await wrapper.find('#terms').setValue(true)
    await wrapper.find('form').trigger('submit')
    expect(wrapper.text()).toContain('The email address field must be a valid email address.')
    expect(post).not.toHaveBeenCalled()
  })

  it('renders the shortened Google sign-up button text', () => {
    const wrapper = mount(RegisterStudent, {
      global: { plugins: [i18n] },
    })
    const googleLink = wrapper.find(`a[href="${ROUTES.GOOGLE_REDIRECT}"]`)
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
