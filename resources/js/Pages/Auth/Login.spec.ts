import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createInertiaFormMock } from '@/test/createInertiaFormMock'
import Login from './Login.vue'
import pl from '@/lang/pl.json'

const form = createInertiaFormMock()

vi.mock('@inertiajs/vue3', () => ({
  Head: { template: '<div />' },
  Link: {
    props: ['href', 'method', 'as'],
    template: '<a :href="href"><slot /></a>',
  },
  useForm: () => form,
}))

const i18n = createI18n({
  legacy: false,
  locale: 'pl',
  messages: { pl },
})

const mountLogin = () =>
  mount(Login, {
    global: {
      plugins: [i18n],
      stubs: {
        AuthLayout: {
          props: ['title'],
          template: '<div><h1>{{ title }}</h1><slot /></div>',
        },
        BaseInput: {
          props: ['id', 'label', 'type', 'error'],
          template: '<div class="base-input"><label>{{ label }}</label></div>',
        },
        BaseButton: {
          template: '<button type="submit"><slot /></button>',
        },
      },
    },
  })

beforeEach(() => {
  form.email = ''
  form.password = ''
  form.errors = {}
  form.processing = false
  form.post.mockClear()
})

describe('Login.vue', () => {
  it('renders heading and email/password fields', () => {
    const wrapper = mountLogin()

    expect(wrapper.text()).toContain('Zaloguj się')
    expect(wrapper.text()).toContain('E-mail')
    expect(wrapper.text()).toContain('Hasło')
  })

  it('shows auth error in alert box, not on BaseInput', async () => {
    form.errors = { email: 'These credentials do not match our records.' }

    const wrapper = mountLogin()
    await flushPromises()

    const alert = wrapper.find('[role="alert"]')
    expect(alert.exists()).toBe(true)
    expect(alert.text()).toContain('credentials')

    const inputs = wrapper.findAll('.base-input')
    inputs.forEach((input) => {
      expect(input.text()).not.toContain('credentials')
    })
  })

  it('shows resend link for unverified account', async () => {
    form.errors = {
      email: pl.auth.verification.notVerified,
    }

    const wrapper = mountLogin()
    await flushPromises()

    expect(wrapper.text()).toContain('Wyślij ponownie link weryfikacyjny')
  })

  it('does not show resend link for wrong password', async () => {
    form.errors = { email: 'These credentials do not match our records.' }

    const wrapper = mountLogin()
    await flushPromises()

    expect(wrapper.text()).not.toContain('Wyślij ponownie link weryfikacyjny')
  })

  it('submits form on submit', async () => {
    const wrapper = mountLogin()

    await wrapper.find('form').trigger('submit.prevent')

    expect(form.post).toHaveBeenCalledWith('/login', {
      preserveScroll: true,
    })
  })

  it('shows register and forgot password links', () => {
    const wrapper = mountLogin()

    expect(wrapper.text()).toContain('Nie pamiętasz hasła?')
    expect(wrapper.text()).toContain('Zarejestruj się')
    expect(wrapper.html()).toContain('href="/register/student"')
    expect(wrapper.html()).toContain('href="/forgot-password"')
  })

  it('shows Google sign-in label', () => {
    const wrapper = mountLogin()

    expect(wrapper.text()).toContain('Zaloguj się przy pomocy Google')
  })
})
