import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import About from '@/Components/Profiles/About.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

describe('About.vue', () => {
  it('shows the company description when provided', () => {
    const descriptionText = 'Company that creates software.'
    
    const wrapper = mount(About, {
      props: { description: descriptionText }
    })

    expect(wrapper.text()).toContain('profiles.aboutUs')
    expect(wrapper.text()).toContain(descriptionText)
    expect(wrapper.text()).not.toContain('profiles.company.noDescription')
  })

  it('shows the placeholder text when the description is empty', () => {
    const wrapper = mount(About, {
      props: { description: undefined }
    })

    expect(wrapper.text()).toContain('profiles.company.noDescription')
  })
})