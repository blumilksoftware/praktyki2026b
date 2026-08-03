import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi } from 'vitest'
import CompanyOfferUnpublishModal from '@/Components/Company/CompanyOfferUnpublishModal.vue'
import en from '@/lang/en.json'

const formPatch = vi.fn()

vi.mock('@inertiajs/vue3', () => ({
  useForm: () => ({
    processing: false,
    patch: formPatch,
  }),
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const mountModal = (props = {}) => mount(CompanyOfferUnpublishModal, {
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

describe('CompanyOfferUnpublishModal', () => {
  it('shows the offer title and the irreversible-action notice', () => {
    const wrapper = mountModal()

    expect(wrapper.text()).toContain('Backend Internship')
    expect(wrapper.text()).toContain(en.company.offer.unpublish.irreversibleNotice)
  })

  it('submits a deactivate request to the offer endpoint', async () => {
    formPatch.mockClear()
    const wrapper = mountModal()

    await wrapper.find('form').trigger('submit.prevent')

    expect(formPatch).toHaveBeenCalledTimes(1)
    expect(formPatch.mock.calls[0][0]).toBe('/company/offers/42/deactivate')
  })

  it('emits close when the cancel button is clicked', async () => {
    const wrapper = mountModal()

    const buttons = wrapper.findAll('button')
    const cancelButton = buttons.find((btn) => btn.text() === en.company.offer.unpublish.cancel)
    await cancelButton!.trigger('click')

    expect(wrapper.emitted('close')).toHaveLength(1)
  })
})
