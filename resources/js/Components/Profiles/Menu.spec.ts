import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import Menu from '@/Components/Profiles/Menu.vue'

const DummyIcon = {
  template: '<svg class="dummy-icon"></svg>'
}

describe('Menu.vue', () => {
  const globalStubs = {
    Link: {
      template: '<a :href="$attrs.href"><slot /></a>'
    },
  }

  const mockItems = [
    { label: 'Profile', href: '/profile', icon: DummyIcon, isActive: true },
    { label: 'Settings', href: '/settings', icon: DummyIcon, isActive: false }
  ]

  it('renders navigation links', () => {
    const wrapper = mount(Menu, {
      props: { items: mockItems },
      global: { stubs: globalStubs }
    })

    expect(wrapper.text()).toContain('Profile')
    expect(wrapper.text()).toContain('Settings')
    
    const links = wrapper.findAll('a')
    expect(links[0].attributes('href')).toBe('/profile')
    expect(links[1].attributes('href')).toBe('/settings')
  })

  it('applies the correct styling based on the isActive state', () => {
    const wrapper = mount(Menu, {
      props: { items: mockItems },
      global: { stubs: globalStubs }
    })

    const links = wrapper.findAll('a')
    
    expect(links[0].classes()).toContain('text-primary')
    expect(links[0].attributes('aria-current')).toBe('page')
    
    expect(links[1].classes()).toContain('text-additional')
  })
})
