import { mount, enableAutoUnmount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, beforeAll, afterEach } from 'vitest'
import { nextTick } from 'vue'
import AdminUserActionsMenu from '@/Components/Admin/AdminUserActionsMenu.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

enableAutoUnmount(afterEach)

beforeAll(() => {
  Element.prototype.getBoundingClientRect = () => ({
    width: 36, height: 36, top: 10, left: 10, bottom: 46, right: 46, x: 10, y: 10, toJSON: () => ({}),
  })
})

const mountMenu = (status: string) => mount(AdminUserActionsMenu, {
  props: {
    user: { id: '1', email: 'john@example.com', status },
    isOpen: false,
  },
  global: { plugins: [i18n] },
})

async function open(wrapper: ReturnType<typeof mountMenu>) {
  await wrapper.setProps({ isOpen: true })
  await nextTick()
}

describe('AdminUserActionsMenu', () => {
  it('does not render the dropdown while closed', () => {
    mountMenu('active')

    expect(document.querySelector('[data-user-menu-dropdown]')).toBeNull()
  })

  it('offers block for an active user', async () => {
    await open(mountMenu('active'))

    const dropdown = document.querySelector('[data-user-menu-dropdown]')

    expect(dropdown!.textContent).toContain(en.admin.users.block)
    expect(dropdown!.textContent).not.toContain(en.admin.users.unblock)
  })

  it('offers unblock for a blocked user', async () => {
    await open(mountMenu('blocked'))

    expect(document.querySelector('[data-user-menu-dropdown]')!.textContent).toContain(en.admin.users.unblock)
  })

  it('emits toggle when the trigger is clicked', async () => {
    const wrapper = mountMenu('active')

    await wrapper.find('button').trigger('click')

    expect(wrapper.emitted('toggle')).toEqual([['1']])
  })
})
