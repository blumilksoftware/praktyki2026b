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

const mountTable = (data = users.data) => mount(AdminUsersTable, {
  props: {
    users: { ...users, data },
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
  it('does not show actions for the current admin', () => {
    const wrapper = mountTable()

    expect(rowFor(wrapper, 'jane@example.com')!.findAll('button')).toHaveLength(0)
  })

  it('shows actions for other users', () => {
    const wrapper = mountTable()

    expect(rowFor(wrapper, 'john@example.com')!.findAll('button')).toHaveLength(2)
  })

  it('shows the user status in its own column', () => {
    const wrapper = mountTable()

    expect(rowFor(wrapper, 'john@example.com')!.text()).toContain(en.admin.users.statuses.active)
    expect(rowFor(wrapper, 'anna@example.com')!.text()).toContain(en.admin.users.statuses.blocked)
  })

  it('titles the mobile card with the user name instead of the user id', () => {
    const card = mountTable().findAll('article')[1]

    expect(card.find('p').text()).toBe('John Student')
    expect(card.text()).not.toContain('other-user-id')
  })

  it('falls back to the email in the mobile card title when the user has no name', () => {
    const wrapper = mountTable([
      { id: 'nameless-id', first_name: null, last_name: null, email: 'nameless@example.com', role: 'student', status: 'active' },
    ])

    expect(wrapper.find('article p').text()).toBe('nameless@example.com')
  })

  it('labels a deleted account instead of showing its anonymised email', () => {
    const wrapper = mountTable([
      { id: 'deleted-user-id', first_name: null, last_name: null, email: 'deleted-deleted-user-id-a1b2c3d4@deleted.local', role: 'student', status: 'deleted' },
    ])

    expect(wrapper.find('article p').text()).toBe(en.admin.users.deletedAccount)
    expect(wrapper.findAll('tbody td')[0].text()).toBe(en.admin.users.deletedAccount)
  })

  it('does not repeat the name and status in the mobile card details', () => {
    const card = mountTable().findAll('article')[1]

    expect(card.findAll('dt').map((dt) => dt.text()))
      .toEqual([en.admin.users.email, en.admin.users.role, ''])
  })

  it('labels the actions with the user email', () => {
    const wrapper = mountTable()

    const trigger = rowFor(wrapper, 'john@example.com')!.find('[aria-haspopup="true"]')

    expect(trigger.attributes('aria-label')).toContain('john@example.com')
  })
})
