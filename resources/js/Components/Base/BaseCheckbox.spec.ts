import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'

describe('BaseCheckbox', () => {
  it('renders slot content', () => {
    const wrapper = mount(BaseCheckbox, {
      props: { 
        id: 'remember-me',
        modelValue: false 
      },
      slots: { 
        default: 'Zapamiętaj mnie' 
      },
    })

    expect(wrapper.text()).toContain('Zapamiętaj mnie')
  })

  it('binds id correctly to label and input', () => {
    const wrapper = mount(BaseCheckbox, {
      props: { 
        id: 'test-checkbox',
        modelValue: false 
      },
    })

    expect(wrapper.find('label').attributes('for')).toBe('test-checkbox')
    expect(wrapper.find('input').attributes('id')).toBe('test-checkbox')
  })

  it('reflects the initial modelValue state', () => {
    const wrapper = mount(BaseCheckbox, {
      props: { 
        id: 'checked-box',
        modelValue: true 
      },
    })

    const input = wrapper.find('input')
    expect((input.element as HTMLInputElement).checked).toBe(true)
  })

  it('emits update:modelValue when checked state changes', async () => {
    const wrapper = mount(BaseCheckbox, {
      props: { 
        id: 'toggle-box',
        modelValue: false 
      },
    })

    const input = wrapper.find('input')
    await input.setValue(true)

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([true])
  })
})