import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createStudentRegistrationFormMock } from '@/test/createStudentRegistrationFormMock'
import RegisterStudent from './RegisterStudent.vue'
import pl from '@/lang/pl.json'

const form = createStudentRegistrationFormMock()

vi.mock('@inertiajs/vue3', () => ({
  Head: { template: '<div />' },
  Link: {
    props: ['href'],
    template: '<a :href="href"><slot /></a>',
  },
  useForm: () => form,
}))

const i18n = createI18n({
  legacy: false,
  locale: 'pl',
  messages: { pl },
})

const mountRegister = () =>
  mount(RegisterStudent, {
    global: {
      plugins: [i18n],
      stubs: {
        AuthLayout: {
          template: '<div><slot /></div>',
        },
        BaseInput: {
          props: ['id', 'label', 'type', 'error', 'required'],
          template: `
            <div class="base-input" :id="id" :data-error="error || ''">
              <label>{{ label }}</label>
              <input :required="required" :type="type" />
            </div>
          `,
        },
        BaseButton: {
          props: ['disabled'],
          template: '<button type="submit" :disabled="disabled"><slot /></button>',
        },
        BaseCheckbox: {
          template: '<label><input type="checkbox" /><slot /></label>',
        },
      },
    },
  })

beforeEach(() => {
  form.first_name = ''
  form.last_name = ''
  form.email = ''
  form.password = ''
  form.password_confirmation = ''
  form.university = ''
  form.terms = false
  form.errors = {}
  form.processing = false
  form.post.mockClear()
})

describe('RegisterStudent.vue', () => {
  it('renders all required fields', () => {
    const wrapper = mountRegister()

    expect(wrapper.text()).toContain('Imię')
    expect(wrapper.text()).toContain('Nazwisko')
    expect(wrapper.text()).toContain('E-mail')
    expect(wrapper.text()).toContain('Hasło')
    expect(wrapper.text()).toContain('Powtórz hasło')
    expect(wrapper.text()).toContain('regulamin')
  })

  it('marks required inputs for client-side validation', () => {
    const wrapper = mountRegister()
    const requiredInputs = wrapper.findAll('input[required]')

    expect(requiredInputs.length).toBeGreaterThanOrEqual(4)
  })

  it('shows server error on the relevant field, not a global alert', async () => {
    form.errors = { email: 'The email has already been taken.' }

    const wrapper = mountRegister()
    await flushPromises()

    const emailField = wrapper.find('#register-email')
    expect(emailField.attributes('data-error')).toContain('already been taken')

    expect(wrapper.findAll('form [role="alert"]').length).toBe(0)
  })

  it('shows terms error below checkbox', async () => {
    form.errors = { terms: 'The terms field must be accepted.' }

    const wrapper = mountRegister()
    await flushPromises()

    expect(wrapper.text()).toContain('terms field must be accepted')
  })

  it('submits to register endpoint', async () => {
    const wrapper = mountRegister()

    await wrapper.find('form').trigger('submit.prevent')

    expect(form.post).toHaveBeenCalledWith('/register/student', {
      preserveScroll: true,
    })
  })

  it('disables submit while processing', async () => {
    form.processing = true

    const wrapper = mountRegister()
    await flushPromises()

    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined()
  })

  it('shows login link and Google button', () => {
    const wrapper = mountRegister()

    expect(wrapper.text()).toContain('Zaloguj się')
    expect(wrapper.text()).toContain('Zarejestruj się przy pomocy Google')
    expect(wrapper.html()).toContain('href="/login"')
  })
})
