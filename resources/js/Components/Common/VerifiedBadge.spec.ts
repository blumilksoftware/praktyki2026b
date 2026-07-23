import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import VerifiedBadge from '@/Components/Common/VerifiedBadge.vue'

describe('VerifiedBadge', () => {
  it('does not render when account is not verified', () => {
    const wrapper = mount(VerifiedBadge, {
      props: { verified: false },
    })

    expect(wrapper.find('span').exists()).toBe(false)
  })

  it('renders an icon when account is verified', () => {
    const wrapper = mount(VerifiedBadge, {
      props: { verified: true },
    })

    expect(wrapper.find('span').exists()).toBe(true)
    expect(wrapper.find('svg').exists()).toBe(true)
  })

  it('uses default accessible label from i18n', () => {
    const wrapper = mount(VerifiedBadge, {
      props: { verified: true },
    })

    expect(wrapper.attributes('aria-label')).toBe('Account verified by an administrator')
    expect(wrapper.attributes('title')).toBe('Account verified by an administrator')
  })

  it('uses custom accessible label when provided', () => {
    const wrapper = mount(VerifiedBadge, {
      props: {
        verified: true,
        label: 'Verified university',
      },
    })

    expect(wrapper.attributes('aria-label')).toBe('Verified university')
    expect(wrapper.attributes('title')).toBe('Verified university')
  })

  it('uses medium icon size when size is md', () => {
    const wrapper = mount(VerifiedBadge, {
      props: {
        verified: true,
        size: 'md',
      },
    })

    expect(wrapper.find('svg').classes()).toContain('h-5')
    expect(wrapper.find('svg').classes()).toContain('w-5')
  })
})
