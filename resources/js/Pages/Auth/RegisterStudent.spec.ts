import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import RegisterStudent from '@/Pages/Auth/RegisterStudent.vue'
import en from '@/lang/en.json'

const i18n = createI18n({
  legacy: false,
  locale: 'en',
  messages: {
    en: en
  }
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
      global: { plugins: [i18n] }
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
      global: { plugins: [i18n] }
    })
    expect(wrapper.find('a[href="/auth/google/redirect"]').exists()).toBe(true)
  })

  it('shows client-side validation errors for empty required fields', async () => {
    const wrapper = mount(RegisterStudent, {
      global: { plugins: [i18n] }
    })
    await wrapper.find('form').trigger('submit')
    expect(wrapper.text()).toContain('First name is required.')
    expect(wrapper.text()).toContain('Last name is required.')
    expect(wrapper.text()).toContain('E-mail address is required.')
    expect(wrapper.text()).toContain('Password is required.')
    expect(wrapper.text()).toContain('Password confirmation is required.')
    expect(wrapper.text()).toContain('You must accept the terms and conditions.')
    expect(post).not.toHaveBeenCalled()
  })

  it('shows client-side validation error for invalid email', async () => {
    const wrapper = mount(RegisterStudent, {
      global: { plugins: [i18n] }
    })
    await wrapper.find('#first_name').setValue('John')
    await wrapper.find('#last_name').setValue('Doe')
    await wrapper.find('#email').setValue('invalid-email')
    await wrapper.find('#password').setValue('Password123!')
    await wrapper.find('#password_confirmation').setValue('Password123!')
    await wrapper.find('#terms').setValue(true)
    await wrapper.find('form').trigger('submit')
    expect(wrapper.text()).toContain('Enter a valid e-mail address.')
    expect(post).not.toHaveBeenCalled()
  })
})