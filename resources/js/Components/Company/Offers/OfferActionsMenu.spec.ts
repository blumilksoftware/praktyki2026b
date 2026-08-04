import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it } from 'vitest'
import OfferActionsMenu from '@/Components/Company/Offers/OfferActionsMenu.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const offer = { id: 'o1', title: 'Frontend Intern', status: 'published', spots: 3, applications_count: 7 }

function mountMenu(props = {}) {
  return mount(OfferActionsMenu, {
    props: {
      offer,
      ...props,
    },
    global: {
      plugins: [i18n],
    },
  })
}

const menuItems = (wrapper: ReturnType<typeof mountMenu>) => wrapper.findAll('button').slice(1)

describe('OfferActionsMenu', () => {
  it('renders only the trigger button while closed', () => {
    const wrapper = mountMenu()

    expect(wrapper.findAll('button')).toHaveLength(1)
    expect(wrapper.find('button').attributes('aria-label')).toBe('Open actions menu')
    expect(wrapper.text()).not.toContain('Delete')
  })

  it('marks the root as a menu container so outside-click detection can skip it', () => {
    const wrapper = mountMenu()

    expect(wrapper.attributes('data-offer-menu')).toBeDefined()
  })

  it('renders the action items when open', () => {
    const wrapper = mountMenu({ isOpen: true })

    expect(menuItems(wrapper)).toHaveLength(3)
    expect(wrapper.text()).toContain('Edit')
    expect(wrapper.text()).toContain('Deactivate')
    expect(wrapper.text()).toContain('Delete')
  })

  it('emits toggle with the offer id when the trigger is clicked', async () => {
    const wrapper = mountMenu()

    await wrapper.find('button').trigger('click')

    expect(wrapper.emitted('toggle')).toEqual([['o1']])
  })

  it('emits edit with the whole offer', async () => {
    const wrapper = mountMenu({ isOpen: true })

    await menuItems(wrapper)[0].trigger('click')

    expect(wrapper.emitted('edit')).toEqual([[offer]])
  })

  it('emits delete with the whole offer', async () => {
    const wrapper = mountMenu({ isOpen: true })

    await menuItems(wrapper)[2].trigger('click')

    expect(wrapper.emitted('delete')).toEqual([[offer]])
  })

  it('offers deactivation for a published offer', async () => {
    const wrapper = mountMenu({ isOpen: true })
    const statusItem = menuItems(wrapper)[1]

    expect(statusItem.text()).toBe('Deactivate')
    expect(statusItem.attributes('disabled')).toBeUndefined()

    await statusItem.trigger('click')

    expect(wrapper.emitted('toggle-status')).toEqual([[offer]])
  })

  it.each(['draft', 'expired'])('offers activation for a %s offer', (status) => {
    const wrapper = mountMenu({ offer: { ...offer, status }, isOpen: true })

    expect(menuItems(wrapper)[1].text()).toBe('Activate')
  })

  it('disables the status action for a closed offer', async () => {
    const wrapper = mountMenu({ offer: { ...offer, status: 'closed' }, isOpen: true })
    const statusItem = menuItems(wrapper)[1]

    expect(statusItem.attributes('disabled')).toBeDefined()
    expect(statusItem.classes()).toContain('cursor-not-allowed')

    await statusItem.trigger('click')

    expect(wrapper.emitted('toggle-status')).toBeUndefined()
  })
})
