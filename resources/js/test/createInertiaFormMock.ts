import { reactive } from 'vue'
import { vi } from 'vitest'

export const createInertiaFormMock = () => {
  const post = vi.fn()

  const form = reactive({
    email: '',
    password: '',
    errors: {} as Record<string, string>,
    processing: false,
    post,
  })

  return form
}
