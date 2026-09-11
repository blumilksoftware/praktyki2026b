import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import FilterDropdown from '@/Components/Common/FilterDropdown.vue'

const options = [
  { value: '', label: 'All companies' },
  { value: 'c1', label: 'Acme' },
  { value: 'c2', label: 'Beta Soft' },
]

const mountDropdown = (props = {}) => mount(FilterDropdown, {
  props: { modelValue: '', options, ...props },
  attachTo: document.body,
})

async function open(wrapper) {
  await wrapper.get('button').trigger('click')

  return wrapper
}

function optionLabels(wrapper) {
  return wrapper.findAll('button').slice(1).map((b) => b.text())
}

describe('FilterDropdown', () => {
  it('has no search field unless asked for one', async () => {
    const wrapper = await open(mountDropdown())

    expect(wrapper.find('input').exists()).toBe(false)
    expect(optionLabels(wrapper)).toEqual(['All companies', 'Acme', 'Beta Soft'])
  })

  it('narrows the options to the search phrase', async () => {
    const wrapper = await open(mountDropdown({ searchable: true }))

    await wrapper.get('input').setValue('bet')

    expect(optionLabels(wrapper)).toEqual(['Beta Soft'])
  })

  it('matches the phrase regardless of case and position', async () => {
    const wrapper = await open(mountDropdown({ searchable: true }))

    await wrapper.get('input').setValue('SOFT')

    expect(optionLabels(wrapper)).toEqual(['Beta Soft'])
  })

  it('says when nothing matches', async () => {
    const wrapper = await open(mountDropdown({ searchable: true, emptyLabel: 'No companies found' }))

    await wrapper.get('input').setValue('nothing like this')

    expect(wrapper.get('[role="status"]').text()).toBe('No companies found')
  })

  it('forgets the phrase when reopened', async () => {
    const wrapper = await open(mountDropdown({ searchable: true }))

    await wrapper.get('input').setValue('bet')
    await wrapper.get('button').trigger('click')
    await wrapper.get('button').trigger('click')

    expect(wrapper.get('input').element.value).toBe('')
    expect(optionLabels(wrapper)).toEqual(['All companies', 'Acme', 'Beta Soft'])
  })

  it('emits the value of the chosen option', async () => {
    const wrapper = await open(mountDropdown({ searchable: true }))

    await wrapper.findAll('button').find((b) => b.text() === 'Beta Soft')!.trigger('click')

    expect(wrapper.emitted('update:modelValue')).toEqual([['c2']])
  })
})
