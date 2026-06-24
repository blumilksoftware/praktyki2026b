import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import EmailVerificationWaiting from './EmailVerificationWaiting.vue'
import pl from '@/lang/pl.json'

const post = vi.fn()

vi.mock('@inertiajs/vue3', () => ({
  Head: { template: '<div />' },
  Link: {
    props: ['href'],
    template: '<a :href="href"><slot /></a>',
  },
  useForm: (data: { email: string }) => ({
    email: data.email,
    processing: false,
    post: (url: string, options?: { onSuccess?: () => void }) => {
      post(url, options)
      options?.onSuccess?.()
    },
  }),
}))

const i18n = createI18n({
  legacy: false,
  locale: 'pl',
  messages: { pl },
})

const mountWaiting = (email = 'user@example.com') =>
  mount(EmailVerificationWaiting, {
    props: { email },
    global: {
      plugins: [i18n],
      stubs: {
        AuthLayout: { template: '<div><slot /></div>' },
      },
    },
  })

beforeEach(() => {
  post.mockClear()
  vi.useFakeTimers()
})

afterEach(() => {
  vi.useRealTimers()
})

describe('EmailVerificationWaiting.vue', () => {
  it('displays the email address from props', () => {
    const wrapper = mountWaiting('student@example.com')
    expect(wrapper.text()).toContain('student@example.com')
  })

  it('states that the link expires after 24 hours', () => {
    const wrapper = mountWaiting()
    expect(wrapper.text()).toContain('24 godziny')
  })

  it('shows resend button label', () => {
    const wrapper = mountWaiting()
    expect(wrapper.text()).toContain('Wyślij link ponownie')
  })

  it('posts resend request with email', async () => {
    const wrapper = mountWaiting('user@example.com')
    await wrapper.find('button').trigger('click')

    expect(post).toHaveBeenCalledWith('/email/resend', {
      preserveScroll: true,
      onSuccess: expect.any(Function),
    })
  })

  it('disables resend during cooldown', async () => {
    const wrapper = mountWaiting()
    await wrapper.find('button').trigger('click')
    await flushPromises()

    const button = wrapper.find('button')
    expect(button.attributes('disabled')).toBeDefined()
    expect(wrapper.text()).toMatch(/Wyślij ponownie za \d+ s/)
  })

  it('shows link back to login', () => {
    const wrapper = mountWaiting()
    expect(wrapper.text()).toContain('Wróć do logowania')
    expect(wrapper.html()).toContain('href="/login"')
  })
})
