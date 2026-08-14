import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi } from 'vitest'
import AdminUsersTable from '@/Components/Admin/AdminUsersTable.vue'
import en from '@/lang/en.json'

const { mockGet, pageProps } = vi.hoisted(() => ({
  mockGet: vi.fn(),
  pageProps: {
    auth: { user: { id: 'current-admin-id' } },
  },
}))

vi.mock('@inertiajs/vue3', () => ({
  usePage: () => ({ props: pageProps }),
  router: { get: mockGet },
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const users = {
  data: [
    { id: 'current-admin-id', first_name: 'Jane', last_name: 'Admin', email: 'jane@example.com', role: 'superAdmin' },
    { id: 'other-user-id', first_name: 'John', last_name: 'Student', email: 'john@example.com', role: 'student' },
  ],
  links: {},
}

const mountTable = (props = {}) => mount(AdminUsersTable, {
  props: {
    users,
    filters: { role: 'all', search: '' },
    roles: ['student', 'superAdmin', 'companyAdmin'],
    ...props,
  },
  global: {
    plugins: [i18n],
    stubs: {
      AdminChangeRoleModal: { props: ['open'], template: '<div />' },
    },
  },
})

describe('AdminUsersTable', () => {
  it('does not show a change role button for the current admin', () => {
    const wrapper = mountTable()

    const rows = wrapper.findAll('tbody tr')
    const currentAdminRow = rows.find((row) => row.text().includes('jane@example.com'))

    expect(currentAdminRow!.text()).not.toContain(en.admin.users.changeRole)
  })

  it('shows a change role button for other users', () => {
    const wrapper = mountTable()

    const rows = wrapper.findAll('tbody tr')
    const otherUserRow = rows.find((row) => row.text().includes('john@example.com'))

    expect(otherUserRow!.text()).toContain(en.admin.users.changeRole)
  })
})
