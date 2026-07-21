import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import ContactCardEdit from '@/Components/Profiles/Edit/ContactCardEdit.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

describe('ContactCardEdit.vue', () => {
  const createWrapper = (props = {}) => {
    return mount(ContactCardEdit, {
      props,
      global: {
        stubs: {
          BaseInput: true,
          IconWorld: true,
          IconMapPin: true,
          IconPhone: true
        }
      }
    })
  }

  it('passes the correct initial values to the input models', () => {
    const wrapper = createWrapper({
      website: 'example.com',
      city: 'Warsaw',
      nip: '9876543210'
    })

    const websiteInput = wrapper.find('[id="website"]')
    const cityInput = wrapper.find('[id="city"]')

    expect(websiteInput.attributes('modelvalue')).toBe('example.com')
    expect(cityInput.attributes('modelvalue')).toBe('Warsaw')
    
    expect(wrapper.text()).toContain('9876543210')
  })

  it('emits the correct update events when input values change', async () => {
    const wrapper = createWrapper({
      phone: '123'
    })

    const phoneInput = wrapper.findComponent('[id="phone"]')
    
    await phoneInput.setValue('123456789')

    expect(wrapper.emitted('update:phone')).toBeTruthy()
    expect(wrapper.emitted('update:phone')![0]).toEqual(['123456789'])
  })

  it('displays validation errors passed from the backend (props.errors)', () => {
    const wrapper = createWrapper({
      errors: {
        website: 'validation.invalidUrl',
        city: 'validation.cityTooLong'
      }
    })

    const websiteInput = wrapper.find('[id="website"]')
    const cityInput = wrapper.find('[id="city"]')

    expect(websiteInput.attributes('error')).toBe('validation.invalidUrl')
    expect(cityInput.attributes('error')).toBe('validation.cityTooLong')
  })

  it('shows a real-time validation error when a required field is cleared', () => {
    const wrapper = createWrapper({
      city: '   '
    })

    const cityInput = wrapper.find('[id="city"]')
    const websiteInput = wrapper.find('[id="website"]')

    expect(cityInput.attributes('error')).toBe('validation.requiredField')
    
    expect(websiteInput.attributes('error')).toBeUndefined()
  })

  it('prioritizes backend errors over real-time empty field errors', () => {
    const wrapper = createWrapper({
      city: '',
      errors: {
        city: 'backend.customError'
      }
    })

    const cityInput = wrapper.find('[id="city"]')

    expect(cityInput.attributes('error')).toBe('backend.customError')
  })
})