import { reactive } from 'vue'
import { vi } from 'vitest'

export const createStudentRegistrationFormMock = () => {
  const post = vi.fn()

  const form = reactive({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    university: '',
    terms: false,
    errors: {} as Record<string, string>,
    processing: false,
    post,
  })

  return form
}
