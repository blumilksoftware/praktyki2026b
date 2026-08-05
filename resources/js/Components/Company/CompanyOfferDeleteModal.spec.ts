import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi } from 'vitest'
import CompanyOfferDeleteModal from '@/Components/Company/CompanyOfferDeleteModal.vue'
import en from '@/lang/en.json'

const formDelete = vi.fn()

vi.mock('@inertiajs/vue3', () => ({
  useForm: () => ({
    processing: false,
    delete: formDelete,
  }),
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const mountModal = (props = {}) => mount(CompanyOfferDeleteModal, {
  props: { open: true, offerId: '42', offerTitle: 'Backend Internship', ...props },
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

describe('CompanyOfferDeleteModal', () => {
  it('shows the offer title in the confirmation text', () => {
    const wrapper = mountModal()

    expect(wrapper.text()).toContain('Backend Internship')
  })

  it('submits a delete request to the offer endpoint', async () => {
    formDelete.mockClear()
    const wrapper = mountModal()

    await wrapper.find('form').trigger('submit.prevent')

    expect(formDelete).toHaveBeenCalledTimes(1)
    expect(formDelete.mock.calls[0][0]).toBe('/company/offers/42')
  })

  it('emits close when the cancel button is clicked', async () => {
    const wrapper = mountModal()

    const buttons = wrapper.findAll('button')
    const cancelButton = buttons.find((btn) => btn.text() === en.company.offer.delete.cancel)
    await cancelButton!.trigger('click')

    expect(wrapper.emitted('close')).toHaveLength(1)
  })
})
