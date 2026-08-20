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

describe('AdminUserActionsMenu', () => {
  it('offers block for an active user', () => {
    const wrapper = mountMenu('active')

    const titles = wrapper.findAll('button').map((b) => b.attributes('title'))

    expect(titles).toEqual([en.admin.users.changeRole, en.admin.users.block])
  })

  it('offers unblock for a blocked user', () => {
    const wrapper = mountMenu('blocked')

    const titles = wrapper.findAll('button').map((b) => b.attributes('title'))

    expect(titles).toEqual([en.admin.users.changeRole, en.admin.users.unblock])
  })

  it('gives every action an aria-label with the user email', () => {
    const wrapper = mountMenu('active')

    wrapper.findAll('button').forEach((button) => {
      expect(button.attributes('aria-label')).toContain('john@example.com')
    })
  })

  it('emits the matching event when an action is clicked', async () => {
    const wrapper = mountMenu('active')
    const buttons = wrapper.findAll('button')

    await buttons[0].trigger('click')
    await buttons[1].trigger('click')

    expect(wrapper.emitted('change-role')).toHaveLength(1)
    expect(wrapper.emitted('toggle-block')).toHaveLength(1)
  })
})
