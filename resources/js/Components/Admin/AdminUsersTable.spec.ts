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
  useForm: () => ({ processing: false, errors: {}, patch: vi.fn() }),
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const users = {
  data: [
    { id: 'current-admin-id', first_name: 'Jane', last_name: 'Admin', email: 'jane@example.com', role: 'superAdmin', status: 'active' },
    { id: 'other-user-id', first_name: 'John', last_name: 'Student', email: 'john@example.com', role: 'student', status: 'active' },
    { id: 'blocked-user-id', first_name: 'Anna', last_name: 'Blocked', email: 'anna@example.com', role: 'student', status: 'blocked' },
  ],
  links: {},
}

const mountTable = () => mount(AdminUsersTable, {
  props: {
    users,
    filters: { role: 'all', search: '' },
    roles: ['student', 'superAdmin', 'companyAdmin'],
    companies: [{ id: 'c1', name: 'Acme' }],
    universities: [{ id: 'u1', name: 'PWr' }],
  },
  global: {
    plugins: [i18n],
    stubs: {
      AdminChangeRoleModal: { props: ['open'], template: '<div />' },
      AdminBlockUserModal: { props: ['open'], template: '<div />' },
      Pagination: true,
      FilterDropdown: true,
    },
  },
})

const rowFor = (wrapper: ReturnType<typeof mountTable>, email: string) =>
  wrapper.findAll('tbody tr').find((row) => row.text().includes(email))

describe('AdminUsersTable', () => {
  it('does not show an actions menu for the current admin', () => {
    const wrapper = mountTable()

    expect(rowFor(wrapper, 'jane@example.com')!.find('[data-user-menu]').exists()).toBe(false)
  })

  it('shows an actions menu for other users', () => {
    const wrapper = mountTable()

    expect(rowFor(wrapper, 'john@example.com')!.find('[data-user-menu]').exists()).toBe(true)
  })

  it('shows the user status in its own column', () => {
    const wrapper = mountTable()

    expect(rowFor(wrapper, 'john@example.com')!.text()).toContain(en.admin.users.statuses.active)
    expect(rowFor(wrapper, 'anna@example.com')!.text()).toContain(en.admin.users.statuses.blocked)
  })

  it('labels the actions menu with the user email', () => {
    const wrapper = mountTable()

    const trigger = rowFor(wrapper, 'john@example.com')!.find('[data-user-menu] button')

    expect(trigger.attributes('aria-label')).toContain('john@example.com')
  })
})
