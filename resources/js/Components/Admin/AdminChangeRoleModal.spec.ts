import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi } from 'vitest'
import AdminChangeRoleModal from '@/Components/Admin/AdminChangeRoleModal.vue'
import en from '@/lang/en.json'

const formPatch = vi.fn()

vi.mock('@inertiajs/vue3', () => ({
  useForm: () => ({
    processing: false,
    errors: {},
    role: 'student',
    patch: formPatch,
  }),
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const roles = ['student', 'superAdmin', 'companyAdmin']

const mountModal = (props = {}) => mount(AdminChangeRoleModal, {
  props: {
    open: true,
    userId: '42',
    userName: 'Jane Doe',
    currentRole: 'student',
    roles,
    ...props,
  },
  global: {
    plugins: [i18n],
    stubs: {
      BaseModal: {
        props: ['open'],
        template: '<section v-if="open"><slot /></section>',
      },
    },
  },
})

describe('AdminChangeRoleModal', () => {
  it('shows the user name in the confirmation text', () => {
    const wrapper = mountModal()

    expect(wrapper.text()).toContain('Jane Doe')
  })

  it('submits a role change request to the user endpoint', async () => {
    formPatch.mockClear()
    const wrapper = mountModal()

    await wrapper.find('form').trigger('submit.prevent')

    expect(formPatch).toHaveBeenCalledTimes(1)
    expect(formPatch.mock.calls[0][0]).toBe('/admin/users/42/role')
  })

  it('emits close when the cancel button is clicked', async () => {
    const wrapper = mountModal()

    const buttons = wrapper.findAll('button')
    const cancelButton = buttons.find((btn) => btn.text() === en.admin.users.modal.cancel)
    await cancelButton!.trigger('click')

    expect(wrapper.emitted('close')).toHaveLength(1)
  })
})
