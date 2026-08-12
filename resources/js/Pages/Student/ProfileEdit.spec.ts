import { mount } from '@vue/test-utils'
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
    useForm: (data: Record<string, unknown>) => ({
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
  first_name: 'Jan', last_name: 'Kowalski', email: 'jan.kowalski@pwr.edu.pl',
  age: '', street: '', postal_code: '', city: '',
  university: '', study_field: '', study_year: '', specialization: '',
  study_field_ids: [], preferred_cities: [], skills: [], work_modes: [],
  cv_path: null, photo_url: null,
}

const suggestedUniversity = { id: 'uni-1', name: 'Politechnika Wrocławska' }

const mountEdit = (props = {}) => mount(ProfileEdit, {
  props: {
    user: baseUser,
    studyFields: [],
    university_organization: null,
    suggested_university: null,
    ...props,
  },
  global: {
    plugins: [i18n],
    stubs: {
      StudentPanelLayout: { template: '<div><slot /></div>' },
      ProfilePhotoUpload: true,
    },
  },
})

describe('Student/ProfileEdit — university auto-detection', () => {
  it('pre-fills the university field with the suggestion when the student has none yet', () => {
    const wrapper = mountEdit({ suggested_university: suggestedUniversity })

    expect((wrapper.find('#edit_university').element as HTMLInputElement).value).toBe('Politechnika Wrocławska')
  })

  it('shows the detection note when the field still matches the suggestion', () => {
    const wrapper = mountEdit({ suggested_university: suggestedUniversity })

    expect(wrapper.text()).toContain(en.student.profile.edit.universitySuggestedNote)
  })

  it('does not overwrite an existing university value with the suggestion', () => {
    const wrapper = mountEdit({
      user: { ...baseUser, university: 'Uniwersytet Wrocławski' },
      suggested_university: suggestedUniversity,
    })

    expect((wrapper.find('#edit_university').element as HTMLInputElement).value).toBe('Uniwersytet Wrocławski')
    expect(wrapper.text()).not.toContain(en.student.profile.edit.universitySuggestedNote)
  })

  it('does not show the note once the student is already linked to a university organization', () => {
    const wrapper = mountEdit({
      suggested_university: suggestedUniversity,
      university_organization: { id: 'uni-1', name: 'Politechnika Wrocławska' },
    })

    expect(wrapper.text()).not.toContain(en.student.profile.edit.universitySuggestedNote)
  })

  it('shows nothing special when there is no suggestion at all', () => {
    const wrapper = mountEdit()

    expect((wrapper.find('#edit_university').element as HTMLInputElement).value).toBe('')
    expect(wrapper.text()).not.toContain(en.student.profile.edit.universitySuggestedNote)
  })
})



