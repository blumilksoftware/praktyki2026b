import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import Error from './Error.vue'

vi.mock('@inertiajs/vue3', () => ({
  Head: { template: '<slot />' },
}))

const mountError = (status: number, role: string | null = null) =>
  mount(Error, {
    props: { status, role: role ?? undefined },
    global: { stubs: { AppLayout: { template: '<div><slot /></div>' } } },
  })

describe('Error.vue', () => {
  it('displays the status code', () => {
    expect(mountError(404).text()).toContain('404')
  })

  it('shows 404 message', () => {
    expect(mountError(404).text()).toContain('Page not found')
  })

  it('shows 403 message', () => {
    expect(mountError(403).text()).toContain('Access denied')
  })

  it('shows 401 message', () => {
    expect(mountError(401).text()).toContain('Login required')
  })

  it('shows 413 message', () => {
    expect(mountError(413).text()).toContain('File too large')
  })

  it('shows 500 message', () => {
    expect(mountError(500).text()).toContain('Server error')
  })

  it('links to /admin/dashboard for superAdmin', () => {
    expect(mountError(403, 'superAdmin').find('a').attributes('href')).toBe('/admin/dashboard')
  })

  it('links to /company/dashboard for companyAdmin', () => {
    expect(mountError(403, 'companyAdmin').find('a').attributes('href')).toBe('/company/dashboard')
  })

  it('links to /company/dashboard for companyMember', () => {
    expect(mountError(403, 'companyMember').find('a').attributes('href')).toBe('/company/dashboard')
  })

  it('links to /university/dashboard for universityAdmin', () => {
    expect(mountError(403, 'universityAdmin').find('a').attributes('href')).toBe('/university/dashboard')
  })

  it('links to /university/dashboard for universityMember', () => {
    expect(mountError(403, 'universityMember').find('a').attributes('href')).toBe('/university/dashboard')
  })

  it('links to / for guest', () => {
    expect(mountError(401, null).find('a').attributes('href')).toBe('/')
  })

  it('links to / for student', () => {
    expect(mountError(403, 'student').find('a').attributes('href')).toBe('/')
  })
})