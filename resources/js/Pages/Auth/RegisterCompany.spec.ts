import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import RegisterCompany from '@/Pages/Auth/RegisterCompany.vue'
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
      clearErrors: vi.fn(),
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

    expect(wrapper.find('a[href="/register/student"]').exists()).toBe(true)
  })

  it('does not render Google sign-up', () => {
    const wrapper = mount(RegisterCompany, {
      global: { plugins: [i18n] },
    })

    expect(wrapper.find('a[href="/auth/google/redirect"]').exists()).toBe(false)
  })

  it('shows client-side validation errors for empty required fields', async () => {
    const wrapper = mount(RegisterCompany, {
      global: { plugins: [i18n] },
    })

    await wrapper.find('form').trigger('submit')

    expect(wrapper.text()).toContain('Company name is required.')
    expect(wrapper.text()).toContain('NIP is required.')
    expect(wrapper.text()).toContain('E-mail address is required.')
    expect(wrapper.text()).toContain('Password is required.')
    expect(wrapper.text()).toContain('You must accept the terms and conditions.')
    expect(post).not.toHaveBeenCalled()
  })

  it('shows inline NIP validation error for invalid checksum', async () => {
    const wrapper = mount(RegisterCompany, {
      global: { plugins: [i18n] },
    })

    await wrapper.find('#company_name').setValue('Acme Sp. z o.o.')
    await wrapper.find('#nip').setValue('1234563219')
    await wrapper.find('#email').setValue('company@example.com')
    await wrapper.find('#password').setValue('Password123!')
    await wrapper.find('#password_confirmation').setValue('Password123!')
    await wrapper.find('#street').setValue('Flower Street')
    await wrapper.find('#building_number').setValue('1')
    await wrapper.find('#postal_code').setValue('00-001')
    await wrapper.find('#city').setValue('Warsaw')
    await wrapper.find('#phone').setValue('123456789')
    await wrapper.find('#terms').setValue(true)

    await wrapper.find('form').trigger('submit')

    expect(wrapper.text()).toContain('Enter a valid NIP number.')
    expect(post).not.toHaveBeenCalled()
  })

  it('shows server-side NIP error next to the field', () => {
    mockFormState.errors = { nip: 'The NIP has already been taken.' }

    const wrapper = mount(RegisterCompany, {
      global: { plugins: [i18n] },
    })

    const nipError = wrapper.find('#nip-error')
    expect(nipError.exists()).toBe(true)
    expect(nipError.text()).toBe('The NIP has already been taken.')
  })
})