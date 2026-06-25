import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import GoogleSvg from '@/Components/Common/GoogleSvg.vue'

describe('GoogleSvg', () => {
  it('renders correctly as an svg element', () => {
    const wrapper = mount(GoogleSvg)
    
    expect(wrapper.find('svg').exists()).toBe(true)
  })

  it('applies default size and shrinking classes', () => {
    const wrapper = mount(GoogleSvg)
    
    expect(wrapper.classes()).toContain('w-5')
    expect(wrapper.classes()).toContain('h-5')
    expect(wrapper.classes()).toContain('shrink-0')
  })
})