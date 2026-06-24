import { describe, it, expect } from 'vitest'
import { useTogglePassword } from '@/composables/useTogglePassword'

describe('useTogglePassword', () => {
  it('starts with password hidden', () => {
    const { showPassword } = useTogglePassword()
    expect(showPassword.value).toBe(false)
  })

  it('toggles visibility', () => {
    const { showPassword, togglePassword } = useTogglePassword()
    togglePassword()
    expect(showPassword.value).toBe(true)
    togglePassword()
    expect(showPassword.value).toBe(false)
  })

  it('creates independent state per call', () => {
    const first = useTogglePassword()
    const second = useTogglePassword()

    first.togglePassword()

    expect(first.showPassword.value).toBe(true)
    expect(second.showPassword.value).toBe(false)
  })
})
