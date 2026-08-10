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
      props: { id: 'apply-offer-1', hasCv: true }
    })

    const button = wrapper.find('button')
    expect(button.exists()).toBe(true)
    expect(button.text()).toBe('common.actions.apply.applyNow')
    expect(button.attributes('aria-disabled')).toBeUndefined()
  })

  it('emits "apply" event when clicked', async () => {
    const wrapper = mount(BaseApplyButton, {
      props: { id: 'apply-offer-1', hasCv: true }
    })

    await wrapper.find('button').trigger('click')
    
    expect(wrapper.emitted('apply')).toBeTruthy()
    expect(wrapper.emitted('apply')?.length).toBe(1)
  })

  it('shows loading state and disables the button when isLoading is true', () => {
    const wrapper = mount(BaseApplyButton, {
      props: { id: 'apply-offer-1', hasCv: true, isLoading: true }
    })

    const button = wrapper.find('button')
    expect(button.text()).toContain('common.actions.apply.loading')
    expect(button.attributes('aria-disabled')).toBe('true')
  })

  it('shows "Applied" state with date, disables button, and has success background', () => {
    const testDate = '2026-07-20'
    const wrapper = mount(BaseApplyButton, {
      props: {
        id: 'apply-offer-1',
        hasCv: true,
        isApplied: true,
        appliedDate: testDate
      }
    })

    const button = wrapper.find('button')

    expect(button.text()).toContain(`common.actions.apply.appliedOn ${testDate}`)
    expect(button.attributes('aria-disabled')).toBe('true')
    expect(button.classes()).toContain('bg-success')
  })

  it('keeps the apply button focusable and explains why it is blocked when the user has no CV', () => {
    const wrapper = mount(BaseApplyButton, {
      props: { id: 'apply-offer-1', hasCv: false }
    })

    const button = wrapper.find('button')
    expect(button.text()).toBe('common.actions.apply.applyNow')
    expect(button.attributes('aria-disabled')).toBe('true')
    expect(button.attributes('disabled')).toBeUndefined()
    expect(button.attributes('aria-describedby')).toBe('apply-offer-1-reason')
    expect(wrapper.find('#apply-offer-1-reason').text()).toBe('common.actions.apply.noCvMessage')
  })

  it('does not emit "apply" when the user has no CV', async () => {
    const wrapper = mount(BaseApplyButton, {
      props: { id: 'apply-offer-1', hasCv: false }
    })

    await wrapper.find('button').trigger('click')

    expect(wrapper.emitted('apply')).toBeFalsy()
  })

  it('disables the apply button when the disabled prop is set', () => {
    const wrapper = mount(BaseApplyButton, {
      props: { id: 'apply-offer-1', hasCv: true, disabled: true }
    })

    expect(wrapper.find('button').attributes('aria-disabled')).toBe('true')
  })
})

