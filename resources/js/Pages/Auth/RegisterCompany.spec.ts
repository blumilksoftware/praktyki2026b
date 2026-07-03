import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import RegisterCompany from '@/Pages/Auth/RegisterCompany.vue'
import { ROUTES } from '@/Helpers/routes'
import en from '@/lang/en.json'

const i18n = createI18n({
  legacy: false,
  locale: 'en',
  messages: { en },
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
      company_name: '',
      nip: '',
      email: '',
      password: '',
      password_confirmation: '',
      street: '',
      building_number: '',
      postal_code: '',
      city: '',
      phone: '',
      website: '',
      terms: false,
      get errors() {
        return mockFormState.errors
      },
      processing: false,
      post,
    }),
  }
})

describe('RegisterCompany', () => {
  beforeEach(() => {
    mockFormState.errors = {}
    post.mockClear()
  })

  it('renders all required registration fields', () => {
    const wrapper = mount(RegisterCompany, {
      global: { plugins: [i18n] },
    })

    expect(wrapper.find('#company_name').exists()).toBe(true)
    expect(wrapper.find('#nip').exists()).toBe(true)
    expect(wrapper.find('#email').exists()).toBe(true)
    expect(wrapper.find('#password').exists()).toBe(true)
    expect(wrapper.find('#password_confirmation').exists()).toBe(true)
    expect(wrapper.find('#street').exists()).toBe(true)
    expect(wrapper.find('#building_number').exists()).toBe(true)
    expect(wrapper.find('#postal_code').exists()).toBe(true)
    expect(wrapper.find('#city').exists()).toBe(true)
    expect(wrapper.find('#phone').exists()).toBe(true)
    expect(wrapper.find('#website').exists()).toBe(true)
    expect(wrapper.find('#terms').exists()).toBe(true)
  })

  it('marks company tab as active', () => {
    const wrapper = mount(RegisterCompany, {
      global: { plugins: [i18n] },
    })

    const activeTab = wrapper.find('[aria-current="page"]')
    expect(activeTab.exists()).toBe(true)
    expect(activeTab.text()).toContain('Company')
  })

  it('links student tab to student registration', () => {
    const wrapper = mount(RegisterCompany, {
      global: { plugins: [i18n] },
    })

    expect(wrapper.find(`a[href="${ROUTES.REGISTER_STUDENT}"]`).exists()).toBe(true)
  })

  it('does not render Google sign-up', () => {
    const wrapper = mount(RegisterCompany, {
      global: { plugins: [i18n] },
    })

    expect(wrapper.find(`a[href="${ROUTES.GOOGLE_AUTH}"]`).exists()).toBe(false)
  })

  it('submits the registration form to the backend', async () => {
    const wrapper = mount(RegisterCompany, {
      global: { plugins: [i18n] },
    })

    await wrapper.find('form').trigger('submit')

    expect(post).toHaveBeenCalledWith(ROUTES.REGISTER_COMPANY, {
      preserveScroll: true,
    })
  })

  it('shows server-side NIP validation error next to the field', () => {
    mockFormState.errors = { nip: 'The NIP field must be a valid NIP number.' }

    const wrapper = mount(RegisterCompany, {
      global: { plugins: [i18n] },
    })

    const nipError = wrapper.find('#nip-error')
    expect(nipError.exists()).toBe(true)
    expect(nipError.text()).toBe('The NIP field must be a valid NIP number.')
  })

  it('shows server-side duplicate NIP error next to the field', () => {
    mockFormState.errors = { nip: 'The NIP has already been taken.' }

    const wrapper = mount(RegisterCompany, {
      global: { plugins: [i18n] },
    })

    const nipError = wrapper.find('#nip-error')
    expect(nipError.exists()).toBe(true)
    expect(nipError.text()).toBe('The NIP has already been taken.')
  })
})
