import { mount } from '@vue/test-utils'
import { describe, expect, it, vi, afterEach } from 'vitest'
import DataTable from '@/Components/Common/DataTable.vue'

const columns = [
  { key: 'name', label: 'Name', sortable: true },
]

const items = [
  { id: '1', name: 'Acme' },
]

describe('DataTable.vue', () => {
  afterEach(() => {
    vi.restoreAllMocks()
  })

  it('makes rows focusable and links when rowHref is provided', () => {
    const wrapper = mount(DataTable, {
      props: { items, columns, rowKey: 'id', rowHref: () => '/companies/1' },
    })

    const row = wrapper.find('tbody tr')
    expect(row.attributes('tabindex')).toBe('0')
    expect(row.attributes('role')).toBe('link')
  })

  it('does not make rows focusable when rowHref is not provided', () => {
    const wrapper = mount(DataTable, {
      props: { items, columns, rowKey: 'id' },
    })

    const row = wrapper.find('tbody tr')
    expect(row.attributes('tabindex')).toBeUndefined()
    expect(row.attributes('role')).toBeUndefined()
  })

  it('opens the row href on Enter key press', async () => {
    const openSpy = vi.spyOn(window, 'open').mockImplementation(() => null)

    const wrapper = mount(DataTable, {
      props: { items, columns, rowKey: 'id', rowHref: () => '/companies/1' },
    })

    await wrapper.find('tbody tr').trigger('keydown.enter')

    expect(openSpy).toHaveBeenCalledWith('/companies/1', '_blank')
  })

  it('falls back to a translated caption when none is provided', () => {
    const wrapper = mount(DataTable, {
      props: { items, columns, rowKey: 'id' },
    })

    expect(wrapper.find('caption').text()).toBe('Data table')
  })
})
