import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import AccountSettingsSection from '@/Components/Profile/AccountSettingsSection.vue'
import en from '@/lang/en.json'

const formPut = vi.fn()
const formPatch = vi.fn()
const formReset = vi.fn()
const routerPost = vi.fn()

vi.mock('@inertiajs/vue3', () => ({
  useForm: (initial) => ({
    ...initial,
    errors: {},
    processing: false,
    isDirty: true,
    put: formPut,
    patch: formPatch,
    reset: formReset,
  }),
  router: { post: routerPost },
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const mountSection = (props = {}) => mount(AccountSettingsSection, {
  props: {
    email: 'jane@example.com',
    emailVerifiedAt: '2026-01-01T00:00:00Z',
    pendingEmail: null,
    i18nPrefix: 'company.profile',
    passwordUpdateRoute: '/company/password',
    emailUpdateRoute: '/company/email',
    accountDeleteRoute: '/company/account',
    ...props,
  },
  global: {
    plugins: [i18n],
    stubs: {
      DeleteAccountModal: {
        props: ['open', 'i18nPrefix', 'accountDeleteRoute'],
        template: '<div v-if="open" data-testid="delete-modal" />',
      },
    },
  },
})

describe('AccountSettingsSection', () => {
  beforeEach(() => {
    formPut.mockClear()
    formPatch.mockClear()
    routerPost.mockClear()
  })

  it('renders section titles for the given i18n prefix', () => {
    const wrapper = mountSection()

    expect(wrapper.text()).toContain(en.company.profile.password.title)
    expect(wrapper.text()).toContain(en.company.profile.email.title)
    expect(wrapper.text()).toContain(en.company.profile.delete.title)
  })

  it('submits the password form to passwordUpdateRoute', async () => {
    const wrapper = mountSection()

    await wrapper.findAll('form')[0].trigger('submit.prevent')

    expect(formPut).toHaveBeenCalledTimes(1)
    expect(formPut.mock.calls[0][0]).toBe('/company/password')
  })

  it('submits the email form to emailUpdateRoute', async () => {
    const wrapper = mountSection()

    await wrapper.findAll('form')[1].trigger('submit.prevent')

    expect(formPatch).toHaveBeenCalledTimes(1)
    expect(formPatch.mock.calls[0][0]).toBe('/company/email')
  })

  it('opens the delete modal when the delete button is clicked', async () => {
    const wrapper = mountSection()

    const deleteButton = wrapper.findAll('button').find((btn) => btn.text() === en.company.profile.delete.openModal)
    await deleteButton.trigger('click')

    expect(wrapper.find('[data-testid="delete-modal"]').exists()).toBe(true)
  })

  it('hides the delete account section when showDeleteAccount is false', () => {
    const wrapper = mountSection({
      showDeleteAccount: false,
      i18nPrefix: 'admin.profile',
      accountDeleteRoute: null,
    })

    expect(wrapper.find('[data-testid="delete-modal"]').exists()).toBe(false)
    expect(wrapper.text()).not.toContain(en.company.profile.delete.title)
  })
})
