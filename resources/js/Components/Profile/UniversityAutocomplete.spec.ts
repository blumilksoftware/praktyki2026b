import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import { createI18n } from 'vue-i18n'
import UniversityAutocomplete from '@/Components/Profile/UniversityAutocomplete.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const suggestions = ref<{ id: string, name: string }[]>([])
const fetchSuggestions = vi.fn((query: string) => {
  suggestions.value = query.length >= 2
    ? [{ id: 'uni-1', name: 'Politechnika Wrocławska' }, { id: 'uni-2', name: 'Uniwersytet Wrocławski' }]
    : []
})
const clearSuggestions = vi.fn(() => { suggestions.value = [] })

vi.mock('@/Composables/useUniversitySearch', () => ({
  useUniversitySearch: () => ({ suggestions, fetchSuggestions, clearSuggestions }),
}))

describe('UniversityAutocomplete.vue', () => {
  const mountField = () => mount(UniversityAutocomplete, {
    global: { plugins: [i18n] },
  })

  it('calls the search composable as the user types', async () => {
    fetchSuggestions.mockClear()
    const wrapper = mountField()

    await wrapper.find('input').setValue('Poli')

    expect(fetchSuggestions).toHaveBeenCalledWith('Poli')
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['Poli'])
  })

  it('shows the returned suggestions as a dropdown', async () => {
    const wrapper = mountField()

    await wrapper.find('input').setValue('Poli')
    await wrapper.find('input').trigger('focus')

    expect(wrapper.text()).toContain('Politechnika Wrocławska')
    expect(wrapper.text()).toContain('Uniwersytet Wrocławski')
  })

  it('emits update:modelValue and select with the full university object on click', async () => {
    const wrapper = mountField()

    await wrapper.find('input').setValue('Poli')
    await wrapper.find('input').trigger('focus')
    await wrapper.findAll('li')[0].trigger('mousedown')

    expect(wrapper.emitted('update:modelValue')?.at(-1)).toEqual(['Politechnika Wrocławska'])
    expect(wrapper.emitted('select')?.at(-1)).toEqual([{ id: 'uni-1', name: 'Politechnika Wrocławska' }])
  })

  it('shows no dropdown when there are no matches', async () => {
    const wrapper = mountField()

    await wrapper.find('input').setValue('x')
    await wrapper.find('input').trigger('focus')

    expect(wrapper.find('ul').exists()).toBe(false)
  })
})
