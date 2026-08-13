import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import ContactCard from '@/Components/Profiles/ContactCard.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

describe('ContactCard.vue', () => {
  it('renders all contact information when props are provided', () => {
    const wrapper = mount(ContactCard, {
      props: {
        website: 'https://example.com',
        phone: '+48 123 456 789',
        street: 'Main Street 10A',
        postalCode: '00-001',
        city: 'Warsaw',
        nip: '1234567890'
      },
      global: {
        stubs: ['IconWorld', 'IconMapPin', 'IconPhone']
      }
    })

    const text = wrapper.text()

    expect(text).toContain('example.com')
    expect(text).toContain('+48 123 456 789')
    expect(text).toContain('Main Street 10A, 00-001 Warsaw')
    expect(text).toContain('1234567890')
    expect(text).not.toContain('profiles.noContactInfo')
  })

  it('generates the correct Google Maps URL based on the address', () => {
    const wrapper = mount(ContactCard, {
      props: {
        street: 'Baker Street 221B',
        city: 'London'
      },
      global: {
        stubs: ['IconWorld', 'IconMapPin', 'IconPhone']
      }
    })

    const mapsLink = wrapper.find('a[href^="https://www.google.com/maps/search/"]')
    expect(mapsLink.exists()).toBe(true)

    const expectedQuery = encodeURIComponent('Baker Street 221B, London')
    expect(mapsLink.attributes('href')).toContain(expectedQuery)
  })

  it('renders a fallback message when no contact information is provided', () => {
    const wrapper = mount(ContactCard, {
      props: {},
      global: {
        stubs: ['IconWorld', 'IconMapPin', 'IconPhone']
      }
    })

    expect(wrapper.text()).toContain('profiles.noContactInfo')
  })

  it('does not render the fallback message when only an email is provided', () => {
    const wrapper = mount(ContactCard, {
      props: { email: 'contact@example.com' },
      global: {
        stubs: ['IconWorld', 'IconMapPin', 'IconPhone', 'IconMail']
      }
    })

    expect(wrapper.text()).toContain('contact@example.com')
    expect(wrapper.text()).not.toContain('profiles.noContactInfo')
  })

  it('partially formats the address if some address props are missing', () => {
    const wrapper = mount(ContactCard, {
      props: {
        city: 'Cracow'
      },
      global: {
        stubs: ['IconWorld', 'IconMapPin', 'IconPhone']
      }
    })
    expect(wrapper.text()).toContain('Cracow')
  })
})