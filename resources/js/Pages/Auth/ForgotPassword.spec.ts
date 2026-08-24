import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import ForgotPassword from '@/Pages/Auth/ForgotPassword.vue'
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
}

const mockPageProps = {
  status: null as string | null,
  flash: { status: null as string | null },
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
    usePage: () => ({ props: mockPageProps, component: 'Auth/ForgotPassword' }),
    useForm: (data: Record<string, unknown>) => ({
      ...data,
      get errors() {
        return mockFormState.errors
      },
      processing: false,
      post,
      reset,
    }),
  }
})

const mountPage = () => mount(ForgotPassword, {
  global: { plugins: [i18n] },
})

describe('ForgotPassword', () => {
  beforeEach(() => {
    mockFormState.errors = {}
    mockPageProps.status = null
    mockPageProps.flash.status = null
    post.mockClear()
    reset.mockClear()
  })

  it('renders the email field', () => {
    expect(mountPage().find('#email').exists()).toBe(true)
  })

  it('requests a reset link from the backend', async () => {
    const wrapper = mountPage()

    await wrapper.find('form').trigger('submit')

    expect(post).toHaveBeenCalledWith(ROUTES.FORGOT_PASSWORD, expect.objectContaining({
      preserveScroll: true,
    }))
  })

  it('clears the email once the link has been sent', async () => {
    const wrapper = mountPage()

    await wrapper.find('form').trigger('submit')
    post.mock.calls[0][1].onSuccess()

    expect(reset).toHaveBeenCalled()
  })

  it('shows the server-side error', () => {
    mockFormState.errors = { email: 'The email address field must be a valid email address.' }

    expect(mountPage().text()).toContain('The email address field must be a valid email address.')
  })

  it('shows the confirmation flashed by the backend', () => {
    mockPageProps.flash.status = 'We have emailed your password reset link.'

    expect(mountPage().text()).toContain('We have emailed your password reset link.')
  })

  it('links back to the login page', () => {
    expect(mountPage().find(`a[href="${ROUTES.LOGIN}"]`).exists()).toBe(true)
  })
})
