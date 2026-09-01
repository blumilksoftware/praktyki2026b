import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import { createI18n } from 'vue-i18n'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

describe('AuthLayout', () => {
  it('renders a skip link pointing at the main content', () => {
    const wrapper = mount(AuthLayout, { global: { plugins: [i18n] } })

    const skipLink = wrapper.find('a[href="#main-content"]')
    expect(skipLink.exists()).toBe(true)
    expect(skipLink.text()).toBe('Skip to content')
  })

  it('gives the wrapped content the id the skip link targets', () => {
    const wrapper = mount(AuthLayout, {
      slots: { default: '<p>Form</p>' },
      global: { plugins: [i18n] },
    })

    const target = wrapper.find('#main-content')
    expect(target.exists()).toBe(true)
    expect(target.text()).toContain('Form')
  })
})
