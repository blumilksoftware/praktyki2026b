import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it } from 'vitest'
import OffersToolbar from '@/Components/Company/Offers/OffersToolbar.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

function mountToolbar(props = {}) {
  return mount(OffersToolbar, {
    props: {
      search: '',
      status: '',
      ...props,
    },
    global: {
      plugins: [i18n],
    },
  })
}

describe('OffersToolbar', () => {
  it('renders the search input with a translated placeholder', () => {
    const wrapper = mountToolbar()

    expect(wrapper.find('input').attributes('placeholder')).toBe('Search offers')
  })

  it('renders the initial search and status values', () => {
    const wrapper = mountToolbar({ search: 'intern', status: 'draft' })

    expect((wrapper.find('input').element as HTMLInputElement).value).toBe('intern')
    expect((wrapper.find('select').element as HTMLSelectElement).value).toBe('draft')
  })

  it('renders every status option plus the "all" entry', () => {
    const wrapper = mountToolbar()
    const options = wrapper.findAll('option')

    expect(options.map((option) => option.attributes('value'))).toEqual(['', 'published', 'draft', 'closed', 'expired'])
    expect(options.map((option) => option.text())).toEqual(['All', 'Published', 'Draft', 'Closed', 'Expired'])
  })

  it('updates the search model while typing', async () => {
    const wrapper = mountToolbar()

    await wrapper.find('input').setValue('intern')

    expect(wrapper.emitted('update:search')).toEqual([['intern']])
    expect(wrapper.emitted('status-change')).toBeUndefined()
  })

  it('updates the status model and signals the change when a status is picked', async () => {
    const wrapper = mountToolbar()

    await wrapper.find('select').setValue('closed')

    expect(wrapper.emitted('update:status')).toEqual([['closed']])
    expect(wrapper.emitted('status-change')).toHaveLength(1)
  })

  it('hides the clear button while the search is empty', () => {
    const wrapper = mountToolbar()

    expect(wrapper.find('button').exists()).toBe(false)
  })

  it('clears the search through the clear button', async () => {
    const wrapper = mountToolbar({ search: 'intern' })
    const clearButton = wrapper.find('button')

    expect(clearButton.attributes('aria-label')).toBe('Clear search')

    await clearButton.trigger('click')

    expect(wrapper.emitted('update:search')).toEqual([['']])
  })
})
