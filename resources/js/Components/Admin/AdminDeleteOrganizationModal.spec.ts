import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi } from 'vitest'
import AdminDeleteOrganizationModal from '@/Components/Admin/AdminDeleteOrganizationModal.vue'
import en from '@/lang/en.json'

const { mockDelete } = vi.hoisted(() => ({ mockDelete: vi.fn() }))

vi.mock('@inertiajs/vue3', () => ({
  useForm: () => ({ processing: false, delete: mockDelete }),
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const mountModal = (entityType: string) => mount(AdminDeleteOrganizationModal, {
  props: {
    open: true,
    organizationId: '1',
    organizationName: 'Acme',
    entityType,
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

describe('AdminDeleteOrganizationModal', () => {
  it('deletes a company at the company endpoint', async () => {
    const wrapper = mountModal('company')

    await wrapper.find('form').trigger('submit')

    expect(mockDelete).toHaveBeenCalledWith('/admin/companies/1', expect.any(Object))
  })

  it('deletes a university at the university endpoint', async () => {
    const wrapper = mountModal('university')

    await wrapper.find('form').trigger('submit')

    expect(mockDelete).toHaveBeenCalledWith('/admin/universities/1', expect.any(Object))
  })
})
