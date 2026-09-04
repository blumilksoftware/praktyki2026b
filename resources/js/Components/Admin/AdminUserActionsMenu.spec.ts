import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it } from 'vitest'
import AdminUserActionsMenu from '@/Components/Admin/AdminUserActionsMenu.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const mountMenu = (status: string) => mount(AdminUserActionsMenu, {
  props: { user: { id: '1', email: 'john@example.com', status } },
  global: { plugins: [i18n] },
})

const openMenuItems = async (wrapper: ReturnType<typeof mountMenu>) => {
  await wrapper.find('button').trigger('click')
  return wrapper.findAll('[role="menuitem"]')
}

describe('AdminUserActionsMenu', () => {
  it('labels the trigger with the user email', () => {
    const wrapper = mountMenu('active')

    expect(wrapper.find('button').attributes('aria-label')).toContain('john@example.com')
  })

  it('offers block for an active user', async () => {
    const wrapper = mountMenu('active')

    const items = await openMenuItems(wrapper)

    expect(items.map((item) => item.text())).toEqual([
      en.admin.users.changeRole,
      en.admin.users.block,
      en.admin.users.deleteModal.confirmDelete,
    ])
  })

  it('offers unblock for a blocked user', async () => {
    const wrapper = mountMenu('blocked')

    const items = await openMenuItems(wrapper)

    expect(items[1].text()).toBe(en.admin.users.unblock)
  })

  it('emits change-role when the first action is clicked', async () => {
    const wrapper = mountMenu('active')
    const items = await openMenuItems(wrapper)

    await items[0].trigger('click')

    expect(wrapper.emitted('change-role')).toHaveLength(1)
  })

  it('emits toggle-block when the block action is clicked', async () => {
    const wrapper = mountMenu('active')
    const items = await openMenuItems(wrapper)

    await items[1].trigger('click')

    expect(wrapper.emitted('toggle-block')).toHaveLength(1)
  })

  it('emits delete-user when the delete action is clicked', async () => {
    const wrapper = mountMenu('active')
    const items = await openMenuItems(wrapper)

    await items[2].trigger('click')

    expect(wrapper.emitted('delete-user')).toHaveLength(1)
  })
})
