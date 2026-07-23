import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import BaseApplyButton from '@/Components/Base/BaseApplyButton.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key: string, params?: Record<string, string>) => {
      if (params && params.date) {
        return `${key} ${params.date}`
      }
      return key
    }
  })
}))

describe('BaseApplyButton.vue', () => {
  
  it('renders default apply button when user has CV', () => {
    const wrapper = mount(BaseApplyButton, {
      props: { hasCv: true }
    })
    
    const button = wrapper.find('button')
    expect(button.exists()).toBe(true)
    expect(button.text()).toBe('buttons.apply.applyNow')
    expect(button.attributes('disabled')).toBeUndefined()
  })

  it('emits "apply" event when clicked', async () => {
    const wrapper = mount(BaseApplyButton, {
      props: { hasCv: true }
    })
    
    await wrapper.find('button').trigger('click')
    
    expect(wrapper.emitted('apply')).toBeTruthy()
    expect(wrapper.emitted('apply')?.length).toBe(1)
  })

  it('shows loading state and disables the button when isLoading is true', () => {
    const wrapper = mount(BaseApplyButton, {
      props: { hasCv: true, isLoading: true }
    })
    
    const button = wrapper.find('button')
    expect(button.text()).toContain('buttons.apply.loading')
    expect(button.element.disabled).toBe(true)
  })

  it('shows "Applied" state with date, disables button, and has success background', () => {
    const testDate = '2026-07-20'
    const wrapper = mount(BaseApplyButton, {
      props: { 
        hasCv: true, 
        isApplied: true, 
        appliedDate: testDate 
      }
    })
    
    const button = wrapper.find('button')
    
    expect(button.text()).toContain(`buttons.apply.appliedOn ${testDate}`)
    expect(button.element.disabled).toBe(true)
    expect(button.classes()).toContain('bg-success')
  })

  it('renders prompt to upload CV when user does not have it (hasCv: false)', () => {
    const wrapper = mount(BaseApplyButton, {
      props: { hasCv: false }
    })
    
    expect(wrapper.html()).toContain('buttons.apply.noCvMessage')
    
    const button = wrapper.find('button')
    expect(button.text()).toContain('buttons.apply.uploadCvPrompt')
  })

  it('emits "uploadCv" event when the prompt button is clicked', async () => {
    const wrapper = mount(BaseApplyButton, {
      props: { hasCv: false }
    })
    
    await wrapper.find('button').trigger('click')
    
    expect(wrapper.emitted('uploadCv')).toBeTruthy()
    expect(wrapper.emitted('uploadCv')?.length).toBe(1)
    expect(wrapper.emitted('apply')).toBeFalsy()
  })
})
