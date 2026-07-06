import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import RegisterUniversity from '@/Pages/Auth/RegisterUniversity.vue'
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
      university_name: '',
      email: '',
      domain: '',
      password: '',
      password_confirmation: '',
      address: '',
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

describe('RegisterUniversity', () => {
  beforeEach(() => {
    mockFormState.errors = {}
    post.mockClear()
  })

  it('renders all required registration fields', () => {
    const wrapper = mount(RegisterUniversity, {
      global: { plugins: [i18n] },
    })

    expect(wrapper.find('#university_name').exists()).toBe(true)
    expect(wrapper.find('#email').exists()).toBe(true)
    expect(wrapper.find('#domain').exists()).toBe(true)
    expect(wrapper.find('#password').exists()).toBe(true)
    expect(wrapper.find('#password_confirmation').exists()).toBe(true)
    expect(wrapper.find('#address').exists()).toBe(true)
    expect(wrapper.find('#phone').exists()).toBe(true)
    expect(wrapper.find('#website').exists()).toBe(true)
    expect(wrapper.find('#terms').exists()).toBe(true)
  })

  it('marks university tab as active', () => {
    const wrapper = mount(RegisterUniversity, {
      global: { plugins: [i18n] },
    })

    const activeTab = wrapper.find('[aria-current="page"]')
    expect(activeTab.exists()).toBe(true)
    expect(activeTab.text()).toContain('University')
  })

  it('links company tab to company registration', () => {
    const wrapper = mount(RegisterUniversity, {
      global: { plugins: [i18n] },
    })

    expect(wrapper.find(`a[href="${ROUTES.REGISTER_COMPANY}"]`).exists()).toBe(true)
  })

  it('links student tab to student registration', () => {
    const wrapper = mount(RegisterUniversity, {
      global: { plugins: [i18n] },
    })

    expect(wrapper.find(`a[href="${ROUTES.REGISTER_STUDENT}"]`).exists()).toBe(true)
  })

  it('does not render Google sign-up', () => {
    const wrapper = mount(RegisterUniversity, {
      global: { plugins: [i18n] },
    })

    expect(wrapper.find(`a[href="${ROUTES.GOOGLE_AUTH}"]`).exists()).toBe(false)
  })

  it('submits the registration form to the backend', async () => {
    const wrapper = mount(RegisterUniversity, {
      global: { plugins: [i18n] },
    })

    await wrapper.find('form').trigger('submit')

    expect(post).toHaveBeenCalledWith(ROUTES.REGISTER_UNIVERSITY, {
      preserveScroll: true,
    })
  })

  it('shows server-side errors next to the relevant fields', () => {
    mockFormState.errors = {
      university_name: 'The university name field is required.',
      email: 'The email address field must be a valid email address.',
      domain: 'The domain field must be a valid domain.',
      terms: 'The terms field must be accepted.',
    }

    const wrapper = mount(RegisterUniversity, {
      global: { plugins: [i18n] },
    })

    expect(wrapper.text()).toContain('The university name field is required.')
    expect(wrapper.text()).toContain('The email address field must be a valid email address.')
    expect(wrapper.text()).toContain('The domain field must be a valid domain.')
    expect(wrapper.text()).toContain('The terms field must be accepted.')
  })
})