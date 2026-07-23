import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import HeaderEdit from '@/Components/Profiles/Edit/HeaderEdit.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

describe('HeaderEdit.vue', () => {
  let createObjectURLMock: any

  beforeEach(() => {
    createObjectURLMock = vi.fn(() => 'blob:http://localhost/mock-preview-url')
    global.URL.createObjectURL = createObjectURLMock
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  it('renders the company name and default upload UI', () => {
    const wrapper = mount(HeaderEdit, {
      props: {
        name: 'Tech Innovators'
      },
      global: {
        stubs: ['IconPlus']
      }
    })

    expect(wrapper.text()).toContain('Tech Innovators')
    expect(wrapper.text()).toContain('profiles.uploadLogo')
    expect(wrapper.find('img').exists()).toBe(false)
  })

  it('renders the initial logo correctly if provided', () => {
    const wrapper = mount(HeaderEdit, {
      props: {
        name: 'Tech Innovators',
        logoUrl: '/existing-logo.png'
      },
      global: {
        stubs: ['IconPlus']
      }
    })

    const img = wrapper.find('img')
    expect(img.exists()).toBe(true)
    expect(img.attributes('src')).toBe('/existing-logo.png')
  })

  it('triggers the hidden file input when the container is clicked', async () => {
    const wrapper = mount(HeaderEdit, {
      props: { name: 'Test' },
      global: { stubs: ['IconPlus'] }
    })

    const fileInput = wrapper.find<HTMLInputElement>('input[type="file"]')
    const clickSpy = vi.spyOn(fileInput.element, 'click')

    const dropzone = wrapper.find('.cursor-pointer')
    await dropzone.trigger('click')

    expect(clickSpy).toHaveBeenCalledTimes(1)
  })

  it('updates visual styling during drag and drop events', async () => {
    const wrapper = mount(HeaderEdit, {
      props: { name: 'Test' },
      global: { stubs: ['IconPlus'] }
    })

    const dropzone = wrapper.find('.cursor-pointer')

    // Start drag
    await dropzone.trigger('dragover')
    expect(dropzone.classes()).toContain('border-primary')
    expect(dropzone.classes()).toContain('border-dashed')

    // End drag
    await dropzone.trigger('dragleave')
    expect(dropzone.classes()).not.toContain('border-dashed')
  })

  it('handles file drop, emits event, and displays preview', async () => {
    const wrapper = mount(HeaderEdit, {
      props: { name: 'Test' },
      global: { stubs: ['IconPlus'] }
    })

    const mockFile = new File([''], 'logo.png', { type: 'image/png' })
    const dropzone = wrapper.find('.cursor-pointer')

    await dropzone.trigger('drop', {
      dataTransfer: {
        files: [mockFile]
      }
    })

    expect(wrapper.emitted('update:logo')).toBeTruthy()
    expect(wrapper.emitted('update:logo')![0]).toEqual([mockFile])

    expect(createObjectURLMock).toHaveBeenCalledWith(mockFile)
    const img = wrapper.find('img')
    expect(img.exists()).toBe(true)
    expect(img.attributes('src')).toBe('blob:http://localhost/mock-preview-url')
  })

  it('handles standard file input change, emits event, and displays preview', async () => {
    const wrapper = mount(HeaderEdit, {
      props: { name: 'Test' },
      global: { stubs: ['IconPlus'] }
    })

    const mockFile = new File([''], 'logo.jpg', { type: 'image/jpeg' })
    const fileInput = wrapper.find<HTMLInputElement>('input[type="file"]')

    Object.defineProperty(fileInput.element, 'files', {
      value: [mockFile]
    })

    await fileInput.trigger('change')

    // Verifications
    expect(wrapper.emitted('update:logo')).toBeTruthy()
    expect(wrapper.emitted('update:logo')![0]).toEqual([mockFile])

    expect(wrapper.find('img').attributes('src')).toBe('blob:http://localhost/mock-preview-url')
  })

  it('ignores non-image files upon selection', async () => {
    const wrapper = mount(HeaderEdit, {
      props: { name: 'Test' },
      global: { stubs: ['IconPlus'] }
    })

    const mockPdf = new File([''], 'document.pdf', { type: 'application/pdf' })
    const dropzone = wrapper.find('.cursor-pointer')

    await dropzone.trigger('drop', {
      dataTransfer: {
        files: [mockPdf]
      }
    })

    expect(wrapper.emitted('update:logo')).toBeFalsy()
    expect(createObjectURLMock).not.toHaveBeenCalled()
  })
})