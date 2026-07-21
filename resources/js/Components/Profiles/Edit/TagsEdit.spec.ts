import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import TagsEdit from '@/Components/Profiles/Edit/TagsEdit.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

describe('TagsEdit.vue', () => {
  const globalStubs = {
    IconX: true,
    IconSearch: true
  }

  it('renders correctly with initial active tags', () => {
    const wrapper = mount(TagsEdit, {
      props: {
        modelValue: ['IT', 'Vue.js']
      },
      global: { stubs: globalStubs }
    })

    const text = wrapper.text()
    
    expect(text).toContain('IT')
    expect(text).toContain('Vue.js')
    
    expect(text).toContain('2 / 20')
  })

  it('filters available tags based on search query', async () => {
    const wrapper = mount(TagsEdit, {
      props: { modelValue: [] },
      global: { stubs: globalStubs }
    })

    const searchInput = wrapper.find('input[type="text"]')
    
    await searchInput.setValue('Wroc')
    
    const text = wrapper.text()
    expect(text).toContain('Wrocław')
    expect(text).not.toContain('Warszawa')
  })

  it('clears the search query when the clear button (IconX) is clicked', async () => {
    const wrapper = mount(TagsEdit, {
      props: { modelValue: [] },
      global: { stubs: globalStubs }
    })

    const searchInput = wrapper.find<HTMLInputElement>('input[type="text"]')    
    await searchInput.setValue('Python')
    
    const clearButton = wrapper.find('button')
    await clearButton.trigger('click')

    expect(searchInput.element.value).toBe('')
  })

  it('emits update:modelValue when a tag is added', async () => {
    const wrapper = mount(TagsEdit, {
      props: { modelValue: ['Warszawa'] },
      global: { stubs: globalStubs }
    })

    const availableTag = wrapper.findAll('span').find(span => span.text() === 'Vue.js')
    await availableTag!.trigger('click')

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')![0]).toEqual([['Warszawa', 'Vue.js']])
  })

  it('emits update:modelValue when a tag is removed', async () => {
    const wrapper = mount(TagsEdit, {
      props: { modelValue: ['IT', 'Python'] },
      global: { stubs: globalStubs }
    })

    const activeTag = wrapper.findAll('span').find(span => span.text().includes('Python'))
    await activeTag!.trigger('click')

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')![0]).toEqual([['IT']])
  })

  it('does not exceed the maxTags limit', async () => {
    const wrapper = mount(TagsEdit, {
      props: { 
        modelValue: ['Tag1', 'Tag2'],
        maxTags: 2
      },
      global: { stubs: globalStubs }
    })

    expect(wrapper.text()).toContain('2 / 2')

    const availableTag = wrapper.findAll('span').find(span => span.text() === 'IT')
    await availableTag!.trigger('click')

    expect(wrapper.emitted('update:modelValue')).toBeFalsy()
    expect(availableTag?.classes()).toContain('cursor-not-allowed')
  })
})