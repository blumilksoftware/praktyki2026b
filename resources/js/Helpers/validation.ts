export const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
export const URL_PATTERN = /^https?:\/\/.+/i

export type ValidationRule =
  | { type: 'required' }
  | { type: 'email' }
  | { type: 'confirmed'; field: string }
  | { type: 'accepted' }
  | { type: 'custom'; rule: string; validate: (value: unknown, form: Record<string, unknown>) => boolean }

export type FieldRules = ValidationRule[]

export type ValidationMessages = Record<string, string>

export function buildValidationMessage(
  messages: ValidationMessages,
  attributes: ValidationMessages,
  rule: string,
  field: string,
  replacements: Record<string, string> = {},
): string {
  const template = messages[rule] ?? rule
  const attribute = attributes[field] ?? field

  return Object.entries({ attribute, ...replacements }).reduce(
    (message, [key, value]) => message.replace(`:${key}`, value),
    template,
  )
}

export function validateForm(
  form: Record<string, unknown>,
  rules: Record<string, FieldRules>,
  getMessage: (rule: string, field: string) => string,
): Record<string, string> {
  const errors: Record<string, string> = {}

  for (const [field, fieldRules] of Object.entries(rules)) {
    const value = form[field]

    for (const rule of fieldRules) {
      let failed = false
      let messageRule = rule.type

      switch (rule.type) {
        case 'required':
          failed = isEmpty(value)
          break
        case 'email':
          failed = typeof value === 'string' && value.trim() !== '' && !EMAIL_PATTERN.test(value)
          messageRule = 'email'
          break
        case 'confirmed':
          failed = form[rule.field] !== value
          messageRule = 'confirmed'
          break
        case 'accepted':
          failed = value !== true
          messageRule = 'accepted'
          break
        case 'custom':
          failed = !rule.validate(value, form)
          messageRule = rule.rule
          break
      }

      if (failed) {
        errors[field] = getMessage(messageRule, field)
        break
      }
    }
  }

  return errors
}

function isEmpty(value: unknown): boolean {
  if (typeof value === 'string') {
    return value.trim() === ''
  }

  if (typeof value === 'boolean') {
    return false
  }

  return value === null || value === undefined || value === ''
}
