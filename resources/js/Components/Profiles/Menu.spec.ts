import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import Sidebar from '@/Components/Profiles/Menu.vue'
import { IconMenu2, IconX } from '@tabler/icons-vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

const DummyIcon = {
  template: '<svg class="dummy-icon"></svg>'
}

describe('Sidebar.vue', () => {
  const globalStubs = {
    Link: {
      template: '<a :href="$attrs.href"><slot /></a>'
    },
    IconMenu2: true,
    IconX: true
  }

  const mockItems = [
    { label: 'Profile', href: '/profile', icon: DummyIcon, isActive: true },
    { label: 'Settings', href: '/settings', icon: DummyIcon, isActive: false }
  ]

  it('renders the navigation menu and list items correctly', () => {
    const wrapper = mount(Sidebar, {
      props: { items: mockItems },
      global: { stubs: globalStubs }
    })

    expect(wrapper.text()).toContain('profiles.navMenu')
    
    expect(wrapper.text()).toContain('Profile')
    expect(wrapper.text()).toContain('Settings')
    
    const links = wrapper.findAll('a')
    expect(links[0].attributes('href')).toBe('/profile')
    expect(links[1].attributes('href')).toBe('/settings')
  })

  it('applies the correct styling based on the isActive state', () => {
    const wrapper = mount(Sidebar, {
      props: { items: mockItems },
      global: { stubs: globalStubs }
    })

    const links = wrapper.findAll('a')
    
    expect(links[0].classes()).toContain('bg-background')
    expect(links[0].classes()).toContain('text-secondary')
    
    expect(links[1].classes()).toContain('text-additional')
    expect(links[1].classes()).toContain('hover:bg-gray-50')
  })

  it('toggles the mobile menu visibility when the header is clicked', async () => {
    const wrapper = mount(Sidebar, {
      props: { items: mockItems },
      global: { stubs: globalStubs }
    })

    const toggleArea = wrapper.find('.cursor-pointer')
    
    const menuContainer = wrapper.findAll('div')[1]

    expect(menuContainer.classes()).toContain('hidden')
    expect(menuContainer.classes()).not.toContain('block')

    await toggleArea.trigger('click')
    
    expect(menuContainer.classes()).toContain('block')
    expect(menuContainer.classes()).not.toContain('hidden')

    await toggleArea.trigger('click')
    expect(menuContainer.classes()).toContain('hidden')
  })

  it('switches between the hamburger icon and the close icon when toggled', async () => {
    const wrapper = mount(Sidebar, {
      props: { items: mockItems },
      global: { stubs: globalStubs }
    })

    const toggleArea = wrapper.find('.cursor-pointer')

    expect(wrapper.findComponent(IconMenu2).exists()).toBe(true)
    expect(wrapper.findComponent(IconX).exists()).toBe(false)

    await toggleArea.trigger('click')

    expect(wrapper.findComponent(IconMenu2).exists()).toBe(false)
    expect(wrapper.findComponent(IconX).exists()).toBe(true)
  })
})