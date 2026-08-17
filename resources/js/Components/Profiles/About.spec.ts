import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import About from '@/Components/Profiles/About.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

describe('About.vue', () => {
  it('shows the description when provided', () => {
    const descriptionText = 'An organisation that creates software.'

    const wrapper = mount(About, {
      props: { description: descriptionText, emptyMessage: 'No description yet.' }
    })

    expect(wrapper.text()).toContain('profiles.aboutUs')
    expect(wrapper.text()).toContain(descriptionText)
    expect(wrapper.text()).not.toContain('No description yet.')
  })

  it('shows the given empty message when the description is empty', () => {
    const wrapper = mount(About, {
      props: { description: undefined, emptyMessage: 'No description yet.' }
    })

    expect(wrapper.text()).toContain('No description yet.')
  })

  it('renders no empty state when the caller does not provide a message', () => {
    const wrapper = mount(About, {
      props: { description: undefined }
    })

    expect(wrapper.findAll('div')).toHaveLength(1)
  })
})
