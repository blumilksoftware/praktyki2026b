import { mount } from '@vue/test-utils'
import { reactive } from 'vue'
import { describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import ProfileEdit from '@/Pages/Student/ProfileEdit.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

vi.mock('@inertiajs/vue3', async () => {
  const actual = await vi.importActual('@inertiajs/vue3')
  return {
    ...actual,
    Head: { template: '<div />' },
    Link: { template: '<a><slot /></a>' },
    router: { visit: vi.fn() },
    useForm: (data: Record<string, unknown>) => reactive({
      ...data,
      processing: false,
      errors: {},
      isDirty: false,
      patch: vi.fn(),
      post: vi.fn(),
    }),
  }
})

const baseUser = {
  first_name: 'Anna', last_name: 'Miller', email: 'anna.miller@northgate.edu',
  age: '', street: '', postal_code: '', city: '',
  university: '', university_id: '', faculty_id: '', study_field_id: '',
  study_year: '', specialization: '',
  study_field_ids: [], preferred_cities: [], skills: [], work_modes: [],
  cv_path: null, photo_url: null,
}

const suggestedUniversity = { id: 'uni-1', name: 'Northgate University' }

const faculties = [
  {
    id: 'fac-1',
    name: 'Faculty of Engineering',
    study_fields: [
      { id: 'sf-1', name: 'Robotics' },
      { id: 'sf-2', name: 'Materials Science' },
    ],
  },
  { id: 'fac-2', name: 'Faculty of Law', study_fields: [] },
]

const mountEdit = (props = {}) => mount(ProfileEdit, {
  props: {
    user: baseUser,
    studyFields: [],
    faculties: [],
    universityOrganization: null,
    suggestedUniversity: null,
    ...props,
  },
  global: {
    plugins: [i18n],
    stubs: {
      AppLayout: { template: '<div><slot /></div>' },
      ProfilePhotoUpload: true,
    },
  },
})

describe('Student/ProfileEdit — university auto-detection', () => {
  it('pre-fills the university field with the suggestion when the student has none yet', () => {
    const wrapper = mountEdit({ suggestedUniversity })

    expect((wrapper.find('#edit_university').element as HTMLInputElement).value).toBe('Northgate University')
  })

  it('shows the detection note when the field still matches the suggestion', () => {
    const wrapper = mountEdit({ suggestedUniversity })

    expect(wrapper.text()).toContain(en.student.profile.edit.universitySuggestedNote)
  })

  it('does not overwrite an existing university value with the suggestion', () => {
    const wrapper = mountEdit({
      user: { ...baseUser, university: 'Eastbrook University' },
      suggestedUniversity,
    })

    expect((wrapper.find('#edit_university').element as HTMLInputElement).value).toBe('Eastbrook University')
    expect(wrapper.text()).not.toContain(en.student.profile.edit.universitySuggestedNote)
  })

  it('does not show the note once the student is already linked to a university organization', () => {
    const wrapper = mountEdit({
      suggestedUniversity,
      universityOrganization: { id: 'uni-1', name: 'Northgate University' },
    })

    expect(wrapper.text()).not.toContain(en.student.profile.edit.universitySuggestedNote)
  })

  it('shows nothing special when there is no suggestion at all', () => {
    const wrapper = mountEdit()

    expect((wrapper.find('#edit_university').element as HTMLInputElement).value).toBe('')
    expect(wrapper.text()).not.toContain(en.student.profile.edit.universitySuggestedNote)
  })
})

describe('Student/ProfileEdit — faculty and field of study', () => {
  it('keeps both selects empty and explains why when no university is chosen', () => {
    const wrapper = mountEdit()

    expect(wrapper.findAll('#edit_faculty option')).toHaveLength(1)
    expect(wrapper.text()).toContain(en.student.profile.edit.selectUniversityFirst)
    expect(wrapper.text()).toContain(en.student.profile.edit.selectFacultyFirst)
  })

  it('tells the student when the chosen university has no faculties yet', () => {
    const wrapper = mountEdit({
      user: { ...baseUser, university: 'Northgate University', university_id: 'uni-1' },
      faculties: [],
    })

    expect(wrapper.text()).toContain(en.student.profile.edit.noFacultiesForUniversity)
  })

  it('lists the faculties of the chosen university', () => {
    const wrapper = mountEdit({
      user: { ...baseUser, university: 'Northgate University', university_id: 'uni-1' },
      faculties,
    })

    const labels = wrapper.findAll('#edit_faculty option').map((option) => option.text())

    expect(labels).toContain('Faculty of Engineering')
    expect(labels).toContain('Faculty of Law')
  })

  it('offers only the fields of the selected faculty', () => {
    const wrapper = mountEdit({
      user: { ...baseUser, university: 'Northgate University', university_id: 'uni-1', faculty_id: 'fac-1' },
      faculties,
    })

    const labels = wrapper.findAll('#edit_study_field option').map((option) => option.text())

    expect(labels).toContain('Robotics')
    expect(labels).toContain('Materials Science')
  })

  it('tells the student when the selected faculty has no fields yet', async () => {
    const wrapper = mountEdit({
      user: { ...baseUser, university: 'Northgate University', university_id: 'uni-1', faculty_id: 'fac-1' },
      faculties,
    })

    await wrapper.find('#edit_faculty').setValue('fac-2')

    expect(wrapper.text()).toContain(en.student.profile.edit.noStudyFieldsForFaculty)
  })

  it('clears the selected field when the faculty changes', async () => {
    const wrapper = mountEdit({
      user: {
        ...baseUser,
        university: 'Northgate University',
        university_id: 'uni-1',
        faculty_id: 'fac-1',
        study_field_id: 'sf-1',
      },
      faculties,
    })

    expect((wrapper.find('#edit_study_field').element as HTMLSelectElement).value).toBe('sf-1')

    await wrapper.find('#edit_faculty').setValue('fac-2')

    expect((wrapper.find('#edit_study_field').element as HTMLSelectElement).value).toBe('')
  })

  it('clears the university link and both selects when the university name is edited by hand', async () => {
    const wrapper = mountEdit({
      user: {
        ...baseUser,
        university: 'Northgate University',
        university_id: 'uni-1',
        faculty_id: 'fac-1',
        study_field_id: 'sf-1',
      },
      faculties,
    })

    await wrapper.find('#edit_university').setValue('Eastbrook University')

    expect((wrapper.find('#edit_study_field').element as HTMLSelectElement).value).toBe('')
    expect(wrapper.findAll('#edit_faculty option')).toHaveLength(1)
    expect(wrapper.text()).toContain(en.student.profile.edit.selectUniversityFirst)
  })
})



