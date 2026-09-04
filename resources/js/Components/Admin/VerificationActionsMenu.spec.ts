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

const openMenuItems = async (wrapper: ReturnType<typeof mountMenu>) => {
  await wrapper.find('button').trigger('click')
  return wrapper.findAll('[role="menuitem"]')
}

describe('VerificationActionsMenu', () => {
  it('labels the trigger with the item name', () => {
    const wrapper = mountMenu('pending')

    expect(wrapper.find('button').attributes('aria-label')).toContain('Test University')
  })

  it('shows all actions for a pending item', async () => {
    const wrapper = mountMenu('pending')

    const items = await openMenuItems(wrapper)

    expect(items.map((item) => item.text())).toEqual([
      en.admin.verification.accept,
      en.admin.verification.reject,
      en.admin.verification.delete,
    ])
  })

  it('hides accept and reject once the item is verified', async () => {
    const wrapper = mountMenu('verified')

    const items = await openMenuItems(wrapper)

    expect(items.map((item) => item.text())).toEqual([en.admin.verification.delete])
  })

  it('emits accept when the accept action is clicked', async () => {
    const wrapper = mountMenu('pending')
    const items = await openMenuItems(wrapper)

    await items[0].trigger('click')

    expect(wrapper.emitted('accept')).toHaveLength(1)
  })

  it('emits delete when the delete action is clicked', async () => {
    const wrapper = mountMenu('pending')
    const items = await openMenuItems(wrapper)

    await items[2].trigger('click')

    expect(wrapper.emitted('delete')).toHaveLength(1)
  })
})
