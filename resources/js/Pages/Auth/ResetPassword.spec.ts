import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import ResetPassword from '@/Pages/Auth/ResetPassword.vue'
import { ROUTES } from '@/Helpers/routes'
import en from '@/lang/en.json'

const i18n = createI18n({
  legacy: false,
  locale: 'en',
  messages: { en },
})

const post = vi.fn()
const reset = vi.fn()

const mockFormState = {
  errors: {} as Record<string, string>,
  data: {} as Record<string, unknown>,
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
    usePage: () => ({
      props: { status: null, flash: { status: null } },
      component: 'Auth/ResetPassword',
    }),
    useForm: (data: Record<string, unknown>) => {
      mockFormState.data = data
      return {
        ...data,
        get errors() {
          return mockFormState.errors
        },
        processing: false,
        post,
        reset,
      }
    },
  }
})

const mountPage = (props: Record<string, unknown> = {}) => mount(ResetPassword, {
  props: { token: 'reset-token-abc', email: 'user@example.com', ...props },
  global: { plugins: [i18n] },
})

describe('ResetPassword', () => {
  beforeEach(() => {
    mockFormState.errors = {}
    mockFormState.data = {}
    post.mockClear()
    reset.mockClear()
  })

  it('carries the token and email from the reset link into the form', () => {
    mountPage()

    expect(mockFormState.data).toMatchObject({
      token: 'reset-token-abc',
      email: 'user@example.com',
    })
  })

  it('prefills the email field so the user does not retype it', () => {
    const email = mountPage().find('#email').element as HTMLInputElement

    expect(email.value).toBe('user@example.com')
  })

  it('renders both password fields', () => {
    const wrapper = mountPage()

    expect(wrapper.find('#password').exists()).toBe(true)
    expect(wrapper.find('#password_confirmation').exists()).toBe(true)
  })

  it('submits the new password to the backend', async () => {
    const wrapper = mountPage()

    await wrapper.find('form').trigger('submit')

    expect(post).toHaveBeenCalledWith(ROUTES.RESET_PASSWORD_STORE, expect.objectContaining({
      preserveScroll: true,
    }))
  })

  it('clears the password fields once the request finishes', async () => {
    const wrapper = mountPage()

    await wrapper.find('form').trigger('submit')
    post.mock.calls[0][1].onFinish()

    expect(reset).toHaveBeenCalledWith('password', 'password_confirmation')
  })

  it('shows the error for an expired or already used token', () => {
    mockFormState.errors = { email: 'This password reset token is invalid.' }

    expect(mountPage().text()).toContain('This password reset token is invalid.')
  })

  it('shows password validation errors next to the field', () => {
    mockFormState.errors = { password: 'The password field must be at least 8 characters.' }

    expect(mountPage().text()).toContain('The password field must be at least 8 characters.')
  })

  it('hides the form when the link is no longer active', () => {
    const wrapper = mountPage({ valid: false })

    expect(wrapper.find('form').exists()).toBe(false)
    expect(wrapper.text()).toContain(en.auth.resetPassword.expired.heading)
    expect(wrapper.text()).toContain(en.auth.resetPassword.expired.message)
  })

  it('offers a way to request a new link when the old one is dead', () => {
    const link = mountPage({ valid: false }).find(`a[href="${ROUTES.FORGOT_PASSWORD}"]`)

    expect(link.exists()).toBe(true)
    expect(link.text()).toBe(en.auth.resetPassword.expired.requestNew)
  })
})
