import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BaseInput from '@/Components/Base/BaseInput.vue'

describe('BaseInput', () => {
  it('renders label and binds v-model', async () => {
    const wrapper = mount(BaseInput, {
      props: {
        id: 'email',
        label: 'E-mail',
        modelValue: '',
      },
    })

    expect(wrapper.text()).toContain('E-mail')

    const input = wrapper.find('input')
    await input.setValue('user@example.com')

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([
      'user@example.com',
    ])
  })

  it('shows validation error with alert role', () => {
    const wrapper = mount(BaseInput, {
      props: {
        id: 'email',
        label: 'E-mail',
        modelValue: '',
        error: 'Invalid email format',
      },
    })

    const alert = wrapper.find('[role="alert"]')
    expect(alert.exists()).toBe(true)
    expect(alert.text()).toBe('Invalid email format')
  })

  it('shows password toggle only for password type', async () => {
    const wrapper = mount(BaseInput, {
      props: {
        id: 'password',
        label: 'Password',
        type: 'password',
        modelValue: '',
      },
    })

    expect(wrapper.find('button[type="button"]').exists()).toBe(true)

    await wrapper.setProps({ type: 'email' })
    expect(wrapper.find('button[type="button"]').exists()).toBe(false)
  })

  it('toggles input type when eye button is clicked', async () => {
    const wrapper = mount(BaseInput, {
      props: {
        id: 'password',
        label: 'Password',
        type: 'password',
        modelValue: 'secret',
      },
    })

    const input = wrapper.find('input')
    expect(input.attributes('type')).toBe('password')

    await wrapper.find('button[type="button"]').trigger('click')
    expect(input.attributes('type')).toBe('text')
  })
})
