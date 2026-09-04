import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi, afterEach } from 'vitest'
import DataTable from '@/Components/Common/DataTable.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const items = [
  { id: '9b2f1c7a-0d3e-4f21-8a6b-5c7d8e9f0a1b', title: 'Backend Internship', city: 'Riverton', status: 'published' },
]

const columns = [
  { key: 'title', label: 'Offer' },
  { key: 'city', label: 'City' },
  { key: 'status', label: 'Status' },
]

const mountTable = (props = {}, slots = {}) => mount(DataTable, {
  props: { items, columns, rowKey: 'id', cardTitleKey: 'title', ...props },
  slots,
  global: { plugins: [i18n] },
})

describe('DataTable mobile cards', () => {
  it('titles the card with the configured column instead of the row key', () => {
    const card = mountTable().find('article')

    expect(card.text()).toContain('Backend Internship')
    expect(card.text()).not.toContain('9b2f1c7a-0d3e-4f21-8a6b-5c7d8e9f0a1b')
  })

  it('does not repeat the title and badge columns in the card details', () => {
    const card = mountTable().find('article')

    const labels = card.findAll('dt').map((dt) => dt.text())

    expect(labels).toEqual(['City'])
  })

  it('renders the badge slot without wrapping it in another badge', () => {
    const card = mountTable({}, {
      'cell-status': '<span class="inline-flex px-2.5 py-1 rounded-full">Published</span>',
    }).find('article')

    expect(card.findAll('.rounded-full')).toHaveLength(1)
  })

  it('omits the badge when the row has no value for the badge key', () => {
    const card = mountTable({ items: [{ id: 'x', title: 'Draft', city: 'Riverton' }] }).find('article')

    expect(card.text()).toBe('Draft' + 'City' + 'Riverton')
  })

  it('titles the card through the cell slot of the title column', () => {
    const card = mountTable({}, { 'cell-title': '<span>Custom heading</span>' }).find('article')

    expect(card.find('p').text()).toBe('Custom heading')
  })

  it('supports a badge key other than status', () => {
    const wrapper = mountTable({
      items: [{ id: 'u1', name: 'Northgate University', city: 'Riverton', verification_status: 'pending' }],
      columns: [
        { key: 'name', label: 'Name' },
        { key: 'city', label: 'City' },
        { key: 'verification_status', label: 'Status' },
      ],
      cardTitleKey: 'name',
      cardBadgeKey: 'verification_status',
    })
    const card = wrapper.find('article')

    expect(card.find('p').text()).toBe('Northgate University')
    expect(card.findAll('dt').map((dt) => dt.text())).toEqual(['City'])
    expect(card.text()).toContain('pending')
  })
})

describe('DataTable desktop rows', () => {
  it('renders the status cell without an extra badge wrapper', () => {
    const wrapper = mountTable({}, {
      'cell-status': '<span class="inline-flex px-2.5 py-1 rounded-full">Published</span>',
    })

    const statusCell = wrapper.findAll('tbody td')[2]

    expect(statusCell.findAll('.rounded-full')).toHaveLength(1)
  })
})

describe('DataTable keyboard accessibility', () => {
  afterEach(() => {
    vi.restoreAllMocks()
  })

  it('makes rows focusable and links when rowHref is provided', () => {
    const wrapper = mountTable({ rowHref: () => '/companies/1' })

    const row = wrapper.find('tbody tr')
    expect(row.attributes('tabindex')).toBe('0')
    expect(row.attributes('role')).toBe('link')
  })

  it('does not make rows focusable when rowHref is not provided', () => {
    const wrapper = mountTable()

    const row = wrapper.find('tbody tr')
    expect(row.attributes('tabindex')).toBeUndefined()
    expect(row.attributes('role')).toBeUndefined()
  })

  it('opens the row href on Enter key press', async () => {
    const openSpy = vi.spyOn(window, 'open').mockImplementation(() => null)

    const wrapper = mountTable({ rowHref: () => '/companies/1' })

    await wrapper.find('tbody tr').trigger('keydown.enter')

    expect(openSpy).toHaveBeenCalledWith('/companies/1', '_blank')
  })

  it('falls back to a translated caption when none is provided', () => {
    const wrapper = mountTable()

    expect(wrapper.find('caption').text()).toBe('Data table')
  })
})
