import { mount } from '@vue/test-utils'
import { reactive } from 'vue'
import { describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import IndustryTags from '@/Pages/Admin/IndustryTags.vue'
import BaseModal from '@/Components/Base/BaseModal.vue'
import en from '@/lang/en.json'

const submits: Record<string, unknown[]> = { post: [], patch: [], delete: [] }

vi.mock('@inertiajs/vue3', () => ({
  Head: { props: ['title'], template: '<div />' },
  useForm: (data: Record<string, unknown>) => reactive({
    ...data,
    processing: false,
    errors: {},
    clearErrors: vi.fn(),
    reset: vi.fn(),
    post: (url: string) => submits.post.push(url),
    patch: (url: string) => submits.patch.push(url),
    delete: (url: string) => submits.delete.push(url),
  }),
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const mountPage = (industryTags: unknown[] = [{ id: 'tag-1', name: 'IT' }]) => mount(IndustryTags, {
  props: { industryTags },
  global: {
    plugins: [i18n],
    stubs: { AdminLayout: { template: '<div><slot /></div>' }, teleport: true },
  },
})

describe('Admin/IndustryTags', () => {
  it('shows an empty state when there are no industry tags', () => {
    const wrapper = mountPage([])

    expect(wrapper.text()).toContain(en.admin.industryTags.empty)
  })

  it('lists the given industry tags', () => {
    const wrapper = mountPage([{ id: 'tag-1', name: 'IT' }, { id: 'tag-2', name: 'Marketing' }])

    expect(wrapper.text()).toContain('IT')
    expect(wrapper.text()).toContain('Marketing')
  })

  it('submits the create form', async () => {
    submits.post = []
    const wrapper = mountPage([])

    await wrapper.find('#new-industry-tag-name').setValue('Marketing')
    await wrapper.find('form').trigger('submit.prevent')

    expect(submits.post).toEqual(['/admin/industry-tags'])
  })

  it('submits a rename', async () => {
    submits.patch = []
    const wrapper = mountPage([{ id: 'tag-1', name: 'IT' }])

    await wrapper.findAll('button').find((btn) => btn.text() === en.admin.industryTags.edit)!.trigger('click')
    await wrapper.find('#edit-industry-tag-tag-1').setValue('Marketing')
    await wrapper.findAll('button').find((btn) => btn.text() === en.admin.industryTags.save)!.trigger('click')

    expect(submits.patch).toEqual(['/admin/industry-tags/tag-1'])
  })

  it('opens the delete confirmation modal and submits the deletion', async () => {
    submits.delete = []
    const wrapper = mountPage([{ id: 'tag-1', name: 'IT' }])

    await wrapper.findAll('button').find((btn) => btn.text() === en.admin.industryTags.delete)!.trigger('click')

    const modal = wrapper.findComponent(BaseModal)
    expect(modal.text()).toContain('IT')

    await modal.findAll('button').find((btn) => btn.text() === en.admin.industryTags.confirmDelete)!.trigger('click')

    expect(submits.delete).toEqual(['/admin/industry-tags/tag-1'])
  })
})
