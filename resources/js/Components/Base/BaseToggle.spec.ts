import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BaseToggle from '@/Components/Base/BaseToggle.vue'

describe('BaseToggle', () => {
  it('reflects the current model value via aria-checked', () => {
    const wrapper = mount(BaseToggle, {
      props: { id: 'is-paid', modelValue: false },
    })

    expect(wrapper.find('button').attributes('aria-checked')).toBe('false')
  })

  it('toggles the model value when clicked', async () => {
    const wrapper = mount(BaseToggle, {
      props: { id: 'is-paid', modelValue: false },
    })

    await wrapper.find('button').trigger('click')

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([true])
  })

  it('renders the provided label', () => {
    const wrapper = mount(BaseToggle, {
      props: { id: 'is-paid', modelValue: false, label: 'Paid internship' },
    })

    expect(wrapper.text()).toContain('Paid internship')
  })

  it('does not toggle when disabled', async () => {
    const wrapper = mount(BaseToggle, {
      props: { id: 'is-paid', modelValue: false, disabled: true },
    })

    await wrapper.find('button').trigger('click')

    expect(wrapper.emitted('update:modelValue')).toBeUndefined()
    expect(wrapper.find('button').attributes('aria-disabled')).toBe('true')
  })
})
