import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi } from 'vitest'
import OffersTable from '@/Components/Company/Offers/OffersTable.vue'
import OfferActionsMenu from '@/Components/Company/Offers/OfferActionsMenu.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const offers = [
  { id: 'o1', title: 'Frontend Intern', status: 'published', spots: 3, applications_count: 7 },
  { id: 'o2', title: 'Backend Intern', status: 'closed', spots: 1, applications_count: 0 },
]

function mountTable(props = {}) {
  return mount(OffersTable, {
    props: {
      offers,
      sortIcon: () => 'span',
      ...props,
    },
    global: {
      plugins: [i18n],
    },
  })
}

describe('OffersTable', () => {
  it('renders the translated column headers', () => {
    const wrapper = mountTable()
    const headers = wrapper.findAll('th').map((th) => th.text())

    expect(headers).toEqual(['Offer', 'Status', 'Spots', 'Applications', 'Actions'])
  })

  it('is hidden on mobile viewports', () => {
    const wrapper = mountTable()

    expect(wrapper.classes()).toContain('hidden')
    expect(wrapper.classes()).toContain('md:block')
  })

  it('renders one row per offer', () => {
    const wrapper = mountTable()
    const rows = wrapper.findAll('tbody tr')

    expect(rows).toHaveLength(2)
    expect(rows[0].text()).toContain('Frontend Intern')
    expect(rows[0].text()).toContain('Published')
    expect(rows[0].text()).toContain('3')
    expect(rows[0].text()).toContain('7')
    expect(rows[1].text()).toContain('Closed')
  })

  it('renders an empty body when there are no offers', () => {
    const wrapper = mountTable({ offers: [] })

    expect(wrapper.findAll('tbody tr')).toHaveLength(0)
    expect(wrapper.findAll('th')).toHaveLength(5)
  })

  it('colours the status badge per status and falls back for unknown ones', () => {
    const wrapper = mountTable({
      offers: [...offers, { id: 'o3', title: 'Other', status: 'archived', spots: 0, applications_count: 0 }],
    })
    const badges = wrapper.findAll('tbody span')

    expect(badges[0].classes()).toContain('bg-green-100')
    expect(badges[1].classes()).toContain('bg-red-100')
    expect(badges[2].classes()).toContain('bg-gray-100')
  })

  it('asks for a sort icon for every sortable column', () => {
    const sortIcon = vi.fn((column: string) => 'span')

    mountTable({ sortIcon })

    expect(sortIcon.mock.calls.map(([column]) => column)).toEqual(['title', 'spots', 'applications_count'])
  })

  it.each([
    [0, 'title'],
    [1, 'spots'],
    [2, 'applications_count'],
  ])('emits sort with the column when header %i is clicked', async (index, column) => {
    const wrapper = mountTable()

    await wrapper.findAll('th button')[index].trigger('click')

    expect(wrapper.emitted('sort')).toEqual([[column]])
  })

  it('links the title to the offer page and the counter to filtered applications', () => {
    const wrapper = mountTable()
    const links = wrapper.findAll('tbody a')

    expect(links[0].attributes('href')).toBe('/offers/o1')
    expect(links[1].attributes('href')).toBe('/company/applications?offer=o1')
  })

  it('emits go-to-offer with the click event and the offer id', async () => {
    const wrapper = mountTable()

    await wrapper.findAll('tbody a')[0].trigger('click')

    const emitted = wrapper.emitted('go-to-offer')
    expect(emitted?.[0][0]).toBeInstanceOf(Event)
    expect(emitted?.[0][1]).toBe('o1')
  })

  it('emits go-to-applications with the click event and the offer id', async () => {
    const wrapper = mountTable()

    await wrapper.findAll('tbody a')[3].trigger('click')

    const emitted = wrapper.emitted('go-to-applications')
    expect(emitted?.[0][0]).toBeInstanceOf(Event)
    expect(emitted?.[0][1]).toBe('o2')
  })

  it('opens the actions menu only for the offer matching openMenuId', () => {
    const wrapper = mountTable({ openMenuId: 'o1' })
    const menus = wrapper.findAllComponents(OfferActionsMenu)

    expect(menus[0].props('isOpen')).toBe(true)
    expect(menus[1].props('isOpen')).toBe(false)
  })

  it('re-emits toggle-menu from the actions menu', async () => {
    const wrapper = mountTable()

    await wrapper.findAllComponents(OfferActionsMenu)[1].vm.$emit('toggle', 'o2')

    expect(wrapper.emitted('toggle-menu')).toEqual([['o2']])
  })

  it.each(['edit', 'toggle-status', 'delete'])('re-emits %s from the actions menu with the offer', async (event) => {
    const wrapper = mountTable()

    await wrapper.findAllComponents(OfferActionsMenu)[0].vm.$emit(event, offers[0])

    expect(wrapper.emitted(event)).toEqual([[offers[0]]])
  })
})
