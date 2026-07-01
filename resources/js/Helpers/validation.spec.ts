import { describe, expect, it } from 'vitest'
import { buildValidationMessage, EMAIL_PATTERN, validateForm } from '@/Helpers/validation'

const messages = {
  required: 'The :attribute field is required.',
  email: 'The :attribute field must be a valid email address.',
  confirmed: 'The :attribute field confirmation does not match.',
  accepted: 'The :attribute field must be accepted.',
}

const attributes = {
  email: 'email address',
  password: 'password',
  password_confirmation: 'password confirmation',
  terms: 'terms',
}

describe('validation helper', () => {
  it('builds messages with attribute names from backend translations', () => {
    expect(buildValidationMessage(messages, attributes, 'required', 'email')).toBe(
      'The email address field is required.',
    )
  })

  it('validates required fields and email format', () => {
    const errors = validateForm(
      { email: 'invalid', terms: false },
      {
        email: [{ type: 'required' }, { type: 'email' }],
        terms: [{ type: 'accepted' }],
      },
      (rule, field) => buildValidationMessage(messages, attributes, rule, field),
    )

    expect(errors.email).toBe('The email address field must be a valid email address.')
    expect(errors.terms).toBe('The terms field must be accepted.')
  })

  it('validates password confirmation', () => {
    const errors = validateForm(
      { password: 'secret', password_confirmation: 'other' },
      {
        password_confirmation: [{ type: 'required' }, { type: 'confirmed', field: 'password' }],
      },
      (rule, field) => buildValidationMessage(messages, attributes, rule, field),
    )

    expect(errors.password_confirmation).toBe(
      'The password confirmation field confirmation does not match.',
    )
  })

  it('exports email pattern used by email rule', () => {
    expect(EMAIL_PATTERN.test('user@example.com')).toBe(true)
    expect(EMAIL_PATTERN.test('invalid')).toBe(false)
  })
})
