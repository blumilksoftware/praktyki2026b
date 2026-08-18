import { mount } from '@vue/test-utils'
import { reactive } from 'vue'
import { describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import Faculties from '@/Pages/University/Faculties.vue'
import FacultyCard from '@/Components/University/FacultyCard.vue'
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

const unusedFaculty = {
  id: 'fac-1',
  name: 'Faculty of Engineering',
  study_fields: [
    { id: 'sf-1', name: 'Robotics', students_count: 0, offers_count: 0 },
  ],
}

const usedFaculty = {
  id: 'fac-2',
  name: 'Faculty of Law',
  study_fields: [
    { id: 'sf-2', name: 'Civil Law', students_count: 3, offers_count: 1 },
    { id: 'sf-3', name: 'Criminal Law', students_count: 0, offers_count: 0 },
  ],
}

const mountPage = (faculties: unknown[] = [unusedFaculty, usedFaculty]) => mount(Faculties, {
  props: { faculties },
  global: {
    plugins: [i18n],
    stubs: { UniversityLayout: { template: '<div><slot /></div>' }, teleport: true },
  },
})

const openFacultyDelete = async (wrapper: ReturnType<typeof mountPage>, faculty: typeof usedFaculty) => {
  const card = wrapper.findAllComponents(FacultyCard).find((item) => item.props('faculty').id === faculty.id)

  await card!.vm.$emit('deleteFaculty', faculty)
}

describe('University/Faculties', () => {
  it('shows an empty state when the university has no faculties', () => {
    const wrapper = mountPage([])

    expect(wrapper.text()).toContain(en.university.faculties.empty)
    expect(wrapper.findAllComponents(FacultyCard)).toHaveLength(0)
  })

  it('renders one card per faculty', () => {
    const wrapper = mountPage()

    expect(wrapper.findAllComponents(FacultyCard)).toHaveLength(2)
  })

  it('deletes an unused faculty without asking for a replacement', async () => {
    const wrapper = mountPage()

    await openFacultyDelete(wrapper, unusedFaculty)

    expect(wrapper.text()).toContain('Faculty of Engineering')
    expect(wrapper.find('#reassign-target').exists()).toBe(false)
  })

  it('asks where to move the data before deleting a faculty in use', async () => {
    const wrapper = mountPage()

    await openFacultyDelete(wrapper, usedFaculty)

    expect(wrapper.find('#reassign-target').exists()).toBe(true)
    expect(wrapper.text()).toContain(en.university.faculties.usage
      .replace('{students}', '3')
      .replace('{offers}', '1'))
  })

  it('excludes the faculty being deleted from the replacement list', async () => {
    const wrapper = mountPage()

    await openFacultyDelete(wrapper, usedFaculty)

    const labels = wrapper.findAll('#reassign-target option').map((option) => option.text())

    expect(labels).toContain('Faculty of Engineering')
    expect(labels).not.toContain('Faculty of Law')
  })

  it('blocks the deletion when there is nowhere to move the data', async () => {
    const wrapper = mountPage([usedFaculty])

    await openFacultyDelete(wrapper, usedFaculty)

    expect(wrapper.text()).toContain(en.university.faculties.reassignUnavailable)
    expect(wrapper.find('#reassign-target').exists()).toBe(false)
  })

  it('lists only the sibling fields of the same faculty as replacements for a field in use', async () => {
    const wrapper = mountPage()

    await wrapper.findAllComponents(FacultyCard)[1]!.vm.$emit('deleteField', {
      field: usedFaculty.study_fields[0],
      faculty: usedFaculty,
    })

    const labels = wrapper.findAll('#reassign-target option').map((option) => option.text())

    expect(labels).toContain('Criminal Law')
    expect(labels).not.toContain('Civil Law')
    expect(labels).not.toContain('Robotics')
  })

  it('sends the deletion to the faculty endpoint', async () => {
    submits.delete.length = 0
    const wrapper = mountPage()

    await openFacultyDelete(wrapper, unusedFaculty)

    const confirmForm = wrapper.findAll('form')
      .find((form) => form.text().includes(en.university.faculties.confirmDelete))

    await confirmForm!.trigger('submit.prevent')

    expect(submits.delete).toEqual(['/university/faculties/fac-1'])
  })
})
