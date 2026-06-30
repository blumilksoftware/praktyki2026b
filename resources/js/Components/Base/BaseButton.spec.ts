import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BaseButton from '@/Components/Base/BaseButton.vue'

describe('BaseButton', () => {
  it('renders slot content', () => {
    const wrapper = mount(BaseButton, {
      slots: { default: 'Sign in' },
    })

    expect(wrapper.text()).toBe('Sign in')
  })

  it('disables the button when disabled prop is true', () => {
    const wrapper = mount(BaseButton, {
      props: { disabled: true },
    })

    expect(wrapper.find('button').attributes('disabled')).toBeDefined()
  })

  it('uses submit type when passed', () => {
    const wrapper = mount(BaseButton, {
      props: { type: 'submit' },
    })

    expect(wrapper.find('button').attributes('type')).toBe('submit')
  })
})
