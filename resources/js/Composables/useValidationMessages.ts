import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { buildValidationMessage } from '@/Helpers/validation'

type SharedValidation = {
  messages: Record<string, string>
  attributes: Record<string, string>
}

export function useValidationMessages() {
  const page = usePage<{ validation: SharedValidation }>()
  const validation = computed(() => page.props.validation)

  const message = (rule: string, field: string) =>
    buildValidationMessage(
      validation.value.messages,
      validation.value.attributes,
      rule,
      field,
    )

  return { message }
}
