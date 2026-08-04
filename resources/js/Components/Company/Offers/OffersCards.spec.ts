import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it } from 'vitest'
import OffersCards from '@/Components/Company/Offers/OffersCards.vue'
import OfferActionsMenu from '@/Components/Company/Offers/OfferActionsMenu.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const offers = [
  { id: 'o1', title: 'Frontend Intern', status: 'published', spots: 3, applications_count: 7 },
  { id: 'o2', title: 'Backend Intern', status: 'draft', spots: 1, applications_count: 0 },
]

function mountCards(props = {}) {
  return mount(OffersCards, {
    props: {
      offers,
      ...props,
    },
    global: {
      plugins: [i18n],
    },
  })
}

describe('OffersCards', () => {
  it('renders one card per offer with title, spots and applications count', () => {
    const wrapper = mountCards()

    expect(wrapper.findAllComponents(OfferActionsMenu)).toHaveLength(2)
    expect(wrapper.text()).toContain('Frontend Intern')
    expect(wrapper.text()).toContain('Backend Intern')
    expect(wrapper.text()).toContain('Spots')
    expect(wrapper.text()).toContain('Applications')
    expect(wrapper.text()).toContain('7')
  })

  it('renders no cards when the offers list is empty', () => {
    const wrapper = mountCards({ offers: [] })

    expect(wrapper.findAllComponents(OfferActionsMenu)).toHaveLength(0)
    expect(wrapper.findAll('a')).toHaveLength(0)
  })

  it('is hidden on desktop viewports', () => {
    const wrapper = mountCards()

    expect(wrapper.classes()).toContain('md:hidden')
  })

  it('renders a translated status badge with a status specific colour', () => {
    const wrapper = mountCards()
    const badges = wrapper.findAll('span.rounded-full')

    expect(badges[0].text()).toBe('Published')
    expect(badges[0].classes()).toContain('bg-green-100')
    expect(badges[1].text()).toBe('Draft')
    expect(badges[1].classes()).toContain('bg-gray-100')
  })

  it('falls back to neutral colours for an unknown status', () => {
    const wrapper = mountCards({
      offers: [{ id: 'o3', title: 'Archived Intern', status: 'archived', spots: 1, applications_count: 0 }],
    })
    const badge = wrapper.find('span.rounded-full')

    expect(badge.classes()).toContain('bg-gray-100')
    expect(badge.classes()).toContain('text-gray-700')
  })

  it('links the title to the offer page and the counter to filtered applications', () => {
    const wrapper = mountCards()
    const links = wrapper.findAll('a')

    expect(links[0].attributes('href')).toBe('/offers/o1')
    expect(links[1].attributes('href')).toBe('/company/applications?offer=o1')
    expect(links[2].attributes('href')).toBe('/offers/o2')
    expect(links[3].attributes('href')).toBe('/company/applications?offer=o2')
  })

  it('emits go-to-offer with the click event and the offer id', async () => {
    const wrapper = mountCards()

    await wrapper.findAll('a')[2].trigger('click')

    const emitted = wrapper.emitted('go-to-offer')
    expect(emitted).toHaveLength(1)
    expect(emitted?.[0][0]).toBeInstanceOf(Event)
    expect(emitted?.[0][1]).toBe('o2')
  })

  it('emits go-to-applications with the click event and the offer id', async () => {
    const wrapper = mountCards()

    await wrapper.findAll('a')[1].trigger('click')

    const emitted = wrapper.emitted('go-to-applications')
    expect(emitted).toHaveLength(1)
    expect(emitted?.[0][0]).toBeInstanceOf(Event)
    expect(emitted?.[0][1]).toBe('o1')
  })

  it('opens the actions menu only for the offer matching openMenuId', () => {
    const wrapper = mountCards({ openMenuId: 'o2' })
    const menus = wrapper.findAllComponents(OfferActionsMenu)

    expect(menus[0].props('isOpen')).toBe(false)
    expect(menus[1].props('isOpen')).toBe(true)
  })

  it('keeps every menu closed when openMenuId is null', () => {
    const wrapper = mountCards({ openMenuId: null })

    for (const menu of wrapper.findAllComponents(OfferActionsMenu)) {
      expect(menu.props('isOpen')).toBe(false)
    }
  })

  it('re-emits toggle-menu from the actions menu', async () => {
    const wrapper = mountCards()

    await wrapper.findAllComponents(OfferActionsMenu)[1].vm.$emit('toggle', 'o2')

    expect(wrapper.emitted('toggle-menu')).toEqual([['o2']])
  })

  it.each([
    ['edit', 'edit'],
    ['toggle-status', 'toggle-status'],
    ['delete', 'delete'],
  ])('re-emits %s from the actions menu with the offer', async (source, target) => {
    const wrapper = mountCards()

    await wrapper.findAllComponents(OfferActionsMenu)[0].vm.$emit(source, offers[0])

    expect(wrapper.emitted(target)).toEqual([[offers[0]]])
  })
})
