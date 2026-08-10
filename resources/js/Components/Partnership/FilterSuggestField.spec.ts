import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import { IconMapPin } from '@tabler/icons-vue'
import FilterSuggestField from '@/Components/Partnership/FilterSuggestField.vue'

const cities = ['Legnica', 'Lublin', 'Wrocław', 'Warszawa']

describe('FilterSuggestField.vue', () => {
  const createWrapper = (props = {}) => mount(FilterSuggestField, {
    props: {
      id: 'city-field',
      modelValue: '',
      options: cities,
      icon: IconMapPin,
      placeholder: 'City',
      ariaLabel: 'City',
      ...props,
    },
  })

  it('does not show a dropdown when the field is empty', async () => {
    const wrapper = createWrapper()

    await wrapper.find('input').trigger('focus')

    expect(wrapper.find('ul').exists()).toBe(false)
  })

  it('shows matching options once focused and typing', async () => {
    const wrapper = createWrapper({ modelValue: 'Leg' })

    await wrapper.find('input').trigger('focus')

    const items = wrapper.findAll('li').map((el) => el.text())
    expect(items).toEqual(['Legnica'])
  })

  it('matches case-insensitively anywhere in the option, not just as a prefix', async () => {
    const wrapper = createWrapper({ modelValue: 'lub' })

    await wrapper.find('input').trigger('focus')

    const items = wrapper.findAll('li').map((el) => el.text())
    expect(items).toEqual(['Lublin'])
  })

  it('hides the dropdown when nothing matches', async () => {
    const wrapper = createWrapper({ modelValue: 'zzz' })

    await wrapper.find('input').trigger('focus')

    expect(wrapper.find('ul').exists()).toBe(false)
  })

  it('emits the typed value on input', async () => {
    const wrapper = createWrapper()

    await wrapper.find('input').setValue('Wro')

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['Wro'])
  })

  it('selects an option on click and closes the dropdown', async () => {
    const wrapper = createWrapper({ modelValue: 'Leg' })

    await wrapper.find('input').trigger('focus')
    await wrapper.find('li').trigger('mousedown')

    expect(wrapper.emitted('update:modelValue')?.at(-1)).toEqual(['Legnica'])
    expect(wrapper.find('ul').exists()).toBe(false)
  })

  it('navigates and selects with the keyboard', async () => {
    const wrapper = createWrapper({ modelValue: 'l' })

    await wrapper.find('input').trigger('focus')
    await wrapper.find('input').trigger('keydown', { key: 'ArrowDown' })
    await wrapper.find('input').trigger('keydown', { key: 'ArrowDown' })
    await wrapper.find('input').trigger('keydown', { key: 'Enter' })

    expect(wrapper.emitted('update:modelValue')?.at(-1)).toEqual(['Lublin'])
  })

  it('closes the dropdown on Escape', async () => {
    const wrapper = createWrapper({ modelValue: 'Leg' })

    await wrapper.find('input').trigger('focus')
    expect(wrapper.find('ul').exists()).toBe(true)

    await wrapper.find('input').trigger('keydown', { key: 'Escape' })

    expect(wrapper.find('ul').exists()).toBe(false)
  })
})
