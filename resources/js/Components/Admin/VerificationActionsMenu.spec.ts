import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it } from 'vitest'
import VerificationActionsMenu from '@/Components/Admin/VerificationActionsMenu.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const mountMenu = (status: string) => mount(VerificationActionsMenu, {
  props: { item: { id: '1', name: 'Test University', verification_status: status } },
  global: { plugins: [i18n] },
})

describe('VerificationActionsMenu', () => {
  it('shows all actions inline for a pending item', () => {
    const wrapper = mountMenu('pending')

    const titles = wrapper.findAll('button').map((b) => b.attributes('title'))

    expect(titles).toEqual([
      en.admin.verification.accept,
      en.admin.verification.reject,
      en.admin.verification.delete,
    ])
  })

  it('hides accept and reject once the item is verified', () => {
    const wrapper = mountMenu('verified')

    const titles = wrapper.findAll('button').map((b) => b.attributes('title'))

    expect(titles).toEqual([en.admin.verification.delete])
  })

  it('gives every action an aria-label with the item name', () => {
    const wrapper = mountMenu('pending')

    wrapper.findAll('button').forEach((button) => {
      expect(button.attributes('aria-label')).toContain('Test University')
    })
  })

  it('emits the matching event when an action is clicked', async () => {
    const wrapper = mountMenu('pending')
    const buttons = wrapper.findAll('button')

    await buttons[0].trigger('click')
    await buttons[2].trigger('click')

    expect(wrapper.emitted('accept')).toHaveLength(1)
    expect(wrapper.emitted('delete')).toHaveLength(1)
  })
})
