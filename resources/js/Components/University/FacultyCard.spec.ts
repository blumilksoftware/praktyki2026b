import { mount } from '@vue/test-utils'
import { reactive } from 'vue'
import { describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import FacultyCard from '@/Components/University/FacultyCard.vue'
import en from '@/lang/en.json'

const submits: Record<string, unknown[]> = { post: [], patch: [] }

vi.mock('@inertiajs/vue3', () => ({
  useForm: (data: Record<string, unknown>) => reactive({
    ...data,
    processing: false,
    errors: {},
    clearErrors: vi.fn(),
    reset: vi.fn(),
    post: (url: string) => submits.post.push(url),
    patch: (url: string) => submits.patch.push(url),
  }),
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const faculty = {
  id: 'fac-1',
  name: 'Faculty of Engineering',
  study_fields: [
    { id: 'sf-1', name: 'Robotics', students_count: 4, offers_count: 2 },
  ],
}

const mountCard = (props = {}) => mount(FacultyCard, {
  props: { faculty, ...props },
  global: { plugins: [i18n] },
})

const buttonWithLabel = (wrapper: ReturnType<typeof mountCard>, label: string) =>
  wrapper.findAll('button').find((button) => button.attributes('aria-label') === label)!

describe('University/FacultyCard', () => {
  it('shows how many students and offers each field is used by', () => {
    const wrapper = mountCard()

    expect(wrapper.text()).toContain(en.university.faculties.usage
      .replace('{students}', '4')
      .replace('{offers}', '2'))
  })

  it('shows an empty state for a faculty without fields of study', () => {
    const wrapper = mountCard({ faculty: { ...faculty, study_fields: [] } })

    expect(wrapper.text()).toContain(en.university.faculties.noFields)
  })

  it('labels the icon buttons for screen readers', () => {
    const wrapper = mountCard()

    expect(buttonWithLabel(wrapper, 'Rename faculty Faculty of Engineering').exists()).toBe(true)
    expect(buttonWithLabel(wrapper, 'Delete field of study Robotics').exists()).toBe(true)
  })

  it('renames the faculty through its own endpoint', async () => {
    submits.patch.length = 0
    const wrapper = mountCard()

    await buttonWithLabel(wrapper, 'Rename faculty Faculty of Engineering').trigger('click')
    await wrapper.find('#faculty-name-fac-1').setValue('Faculty of Applied Engineering')
    await wrapper.find('form').trigger('submit')

    expect(submits.patch).toEqual(['/university/faculties/fac-1'])
  })

  it('renames a field of study through its own endpoint', async () => {
    submits.patch.length = 0
    const wrapper = mountCard()

    await buttonWithLabel(wrapper, 'Rename field of study Robotics').trigger('click')
    await wrapper.find('#study-field-name-sf-1').setValue('Applied Robotics')
    await wrapper.findAll('form')[0]!.trigger('submit')

    expect(submits.patch).toEqual(['/university/study-fields/sf-1'])
  })

  it('adds a field of study to this faculty', async () => {
    submits.post.length = 0
    const wrapper = mountCard()

    await wrapper.find('#new-study-field-fac-1').setValue('Mechatronics')
    await wrapper.findAll('form').at(-1)!.trigger('submit')

    expect(submits.post).toEqual(['/university/faculties/fac-1/study-fields'])
  })

  it('lets the page decide what happens on delete', async () => {
    const wrapper = mountCard()

    await buttonWithLabel(wrapper, 'Delete faculty Faculty of Engineering').trigger('click')
    await buttonWithLabel(wrapper, 'Delete field of study Robotics').trigger('click')

    expect(wrapper.emitted('deleteFaculty')).toHaveLength(1)
    expect(wrapper.emitted('deleteField')).toHaveLength(1)
  })
})
