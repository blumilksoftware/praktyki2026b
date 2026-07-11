import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import Header from '@/Components/Profiles/Header.vue'
import { IconUser } from '@tabler/icons-vue'

describe('Header.vue', () => {
  it('renders the company name correctly', () => {
    const wrapper = mount(Header, {
      props: {
        name: 'Acme Corporation'
      },
      global: {
        stubs: ['IconUser']
      }
    })

    expect(wrapper.text()).toContain('Acme Corporation')
  })

  it('renders the fallback user icon when no logoUrl is provided', () => {
    const wrapper = mount(Header, {
      props: {
        name: 'Acme Corporation'
      },
      global: {
        stubs: {
          IconUser: true
        }
      }
    })

    expect(wrapper.find('img').exists()).toBe(false)
    
    const fallbackIcon = wrapper.findComponent(IconUser) 
    
    expect(fallbackIcon.exists()).toBe(true)
  })

  it('renders the logo image when logoUrl is provided and formats the path', () => {
    const wrapper = mount(Header, {
      props: {
        name: 'Acme Corporation',
        logoUrl: 'images/company-logo.png'
      },
      global: {
        stubs: ['IconUser']
      }
    })

    const image = wrapper.find('img')
    expect(image.exists()).toBe(true)
    
    expect(image.attributes('src')).toBe('/images/company-logo.png')
  })

  it('does not prepend an extra slash if logoUrl already has one', () => {
    const wrapper = mount(Header, {
      props: {
        name: 'Acme Corporation',
        logoUrl: '/images/company-logo.png'
      },
      global: {
        stubs: ['IconUser']
      }
    })

    const image = wrapper.find('img')
    expect(image.attributes('src')).toBe('/images/company-logo.png')
  })
})