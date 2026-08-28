import { mount } from '@vue/test-utils'
import { reactive } from 'vue'
import { describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import Cities from '@/Pages/Admin/Cities.vue'
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

const mountPage = (cities: unknown[] = [{ id: 'city-1', name: 'Warszawa' }]) => mount(Cities, {
  props: { cities },
  global: {
    plugins: [i18n],
    stubs: { AdminLayout: { template: '<div><slot /></div>' }, teleport: true },
  },
})

describe('Admin/Cities', () => {
  it('shows an empty state when there are no cities', () => {
    const wrapper = mountPage([])

    expect(wrapper.text()).toContain(en.admin.cities.empty)
  })

  it('lists the given cities', () => {
    const wrapper = mountPage([{ id: 'city-1', name: 'Warszawa' }, { id: 'city-2', name: 'Kraków' }])

    expect(wrapper.text()).toContain('Warszawa')
    expect(wrapper.text()).toContain('Kraków')
  })

  it('submits the create form', async () => {
    submits.post = []
    const wrapper = mountPage([])

    await wrapper.find('#new-city-name').setValue('Wrocław')
    await wrapper.find('form').trigger('submit.prevent')

    expect(submits.post).toEqual(['/admin/cities'])
  })

  it('submits a rename', async () => {
    submits.patch = []
    const wrapper = mountPage([{ id: 'city-1', name: 'Warszawa' }])

    await wrapper.findAll('button').find((btn) => btn.text() === en.admin.cities.edit)!.trigger('click')
    await wrapper.find('#edit-city-city-1').setValue('Kraków')
    await wrapper.findAll('button').find((btn) => btn.text() === en.admin.cities.save)!.trigger('click')

    expect(submits.patch).toEqual(['/admin/cities/city-1'])
  })

  it('opens the delete confirmation modal and submits the deletion', async () => {
    submits.delete = []
    const wrapper = mountPage([{ id: 'city-1', name: 'Warszawa' }])

    await wrapper.findAll('button').find((btn) => btn.text() === en.admin.cities.delete)!.trigger('click')

    const modal = wrapper.findComponent(BaseModal)
    expect(modal.text()).toContain('Warszawa')

    await modal.findAll('button').find((btn) => btn.text() === en.admin.cities.confirmDelete)!.trigger('click')

    expect(submits.delete).toEqual(['/admin/cities/city-1'])
  })
})
