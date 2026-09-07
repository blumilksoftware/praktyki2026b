import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import DeleteAccountModal from '@/Components/Profile/DeleteAccountModal.vue'
import en from '@/lang/en.json'

const formDelete = vi.fn()

vi.mock('@inertiajs/vue3', () => ({
  useForm: () => ({
    password: '',
    confirmation: false,
    errors: {},
    processing: false,
    delete: formDelete,
  }),
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const mountModal = (props = {}) => mount(DeleteAccountModal, {
  props: {
    open: true,
    i18nPrefix: 'student.profile',
    accountDeleteRoute: '/student/account',
    ...props,
  },
  global: {
    plugins: [i18n],
    stubs: {
      BaseModal: {
        props: ['open', 'title'],
        template: '<section v-if="open"><slot /></section>',
      },
    },
  },
})

describe('DeleteAccountModal', () => {
  beforeEach(() => {
    formDelete.mockClear()
  })

  it('submits a delete request to accountDeleteRoute', async () => {
    const wrapper = mountModal()

    await wrapper.find('form').trigger('submit.prevent')

    expect(formDelete).toHaveBeenCalledTimes(1)
    expect(formDelete.mock.calls[0][0]).toBe('/student/account')
  })

  it('emits close when the cancel button is clicked', async () => {
    const wrapper = mountModal()

    const cancelButton = wrapper.findAll('button').find((btn) => btn.text() === en.student.profile.delete.cancel)
    await cancelButton.trigger('click')

    expect(wrapper.emitted('close')).toHaveLength(1)
  })
})
