import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import AboutEdit from '@/Components/Profiles/Edit/AboutEdit.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

describe('AboutEdit.vue', () => {
  it('correctly loads the initial value and calculates the character limit', () => {
    const initialText = 'To jest testowy opis firmy.'
    
    const wrapper = mount(AboutEdit, {
      props: { modelValue: initialText }
    })
    const textarea = wrapper.find('textarea')

    expect(textarea.element.value).toBe(initialText)
    expect(wrapper.text()).toContain(`${initialText.length}/2500`)
  })

  it('emits the update:modelValue event when text is entered', async () => {
    const wrapper = mount(AboutEdit, {
      props: { modelValue: '' }
    })
    const textarea = wrapper.find('textarea')
    const newText = 'Nowy opis'
    
    await textarea.setValue(newText)
    expect(wrapper.emitted()).toHaveProperty('update:modelValue')   
    expect(wrapper.emitted('update:modelValue')![0]).toEqual([newText])
  })

  it('limits the length of the textarea to 2500 characters', () => {
    const wrapper = mount(AboutEdit, {
      props: { modelValue: '' }
    })
    const textarea = wrapper.find('textarea')
    
    expect(textarea.attributes('maxlength')).toBe('2500')
  })
})