import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import AboutEdit from '@/Components/Profiles/Edit/AboutEdit.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

const mountAboutEdit = (modelValue = '') => mount(AboutEdit, {
  props: { id: 'organisation-description', modelValue }
})

describe('AboutEdit.vue', () => {
  it('correctly loads the initial value and calculates the character limit', () => {
    const initialText = 'This is a test description.'

    const wrapper = mountAboutEdit(initialText)
    const textarea = wrapper.find('textarea')

    expect(textarea.element.value).toBe(initialText)
    expect(wrapper.text()).toContain(`${initialText.length}/2500`)
  })

  it('emits the update:modelValue event when text is entered', async () => {
    const wrapper = mountAboutEdit()
    const textarea = wrapper.find('textarea')
    const newText = 'A new description'

    await textarea.setValue(newText)
    expect(wrapper.emitted()).toHaveProperty('update:modelValue')
    expect(wrapper.emitted('update:modelValue')![0]).toEqual([newText])
  })

  it('limits the length of the textarea to 2500 characters', () => {
    const wrapper = mountAboutEdit()
    const textarea = wrapper.find('textarea')

    expect(textarea.attributes('maxlength')).toBe('2500')
  })

  it('labels the textarea with the heading and describes it with the character counter', () => {
    const wrapper = mountAboutEdit()
    const textarea = wrapper.find('textarea')

    expect(textarea.attributes('id')).toBe('organisation-description')
    expect(textarea.attributes('aria-labelledby')).toBe('organisation-description-label')
    expect(textarea.attributes('aria-describedby')).toBe('organisation-description-counter')
    expect(wrapper.find('#organisation-description-label').exists()).toBe(true)
    expect(wrapper.find('#organisation-description-counter').text()).toBe('0/2500')
  })
})
