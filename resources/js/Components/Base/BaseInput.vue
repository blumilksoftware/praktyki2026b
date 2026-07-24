<script setup>
import { computed } from 'vue'
import { IconEye, IconEyeOff } from '@tabler/icons-vue'
import { useI18n } from 'vue-i18n'
import { useTogglePassword } from '@/composables/useTogglePassword'

const props = defineProps({
  id: { type: String, required: true },
  label: { type: String, required: true },
  type: { type: String, default: 'text' },
  error: { type: String, default: undefined },
  invalid: { type: Boolean, default: false },
  autocomplete: { type: String, default: undefined },
  required: { type: Boolean, default: false },
  maxlength: { type: [Number, String], default: undefined },
  compact: { type: Boolean, default: false },
  stacked: { type: Boolean, default: true },
  placeholder: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
})

const model = defineModel({ type: String, required: true })

const { t } = useI18n()

const isPassword = computed(() => props.type === 'password')
const { showPassword, togglePassword } = useTogglePassword()

const hasError = computed(() => !!props.error || props.invalid)

const inputType = computed(() => {
  if (!isPassword.value) {
    return props.type
  }
  return showPassword.value ? 'text' : 'password'
})

const wrapperClass = computed(() => (props.compact ? 'pt-5' : 'pt-6'))
</script>

<template>
  <div class="flex w-full flex-col gap-1.5" :class="stacked ? '' : wrapperClass">
    <label
  v-if="stacked"
  :for="id"
  class="mb-1 block text-base font-medium text-additional"
  :class="{ 'text-error': hasError }"
>
  {{ label }}
</label>

<div class="relative">
  <input
    :id="id"
    v-model="model"
    :type="inputType"
    :autocomplete="autocomplete"
    :required="required"
    :maxlength="maxlength"
    :aria-invalid="hasError ? true : undefined"
    :aria-describedby="error ? `${id}-error` : undefined"
    :placeholder="stacked ? placeholder : ' '"
    class="w-full rounded-lg border border-border bg-white px-4 py-3 transition-all focus:border-text focus:outline-none focus:ring-2 focus:ring-primary/30"
    :class="[
      hasError ? 'border-error focus:border-error focus:ring-error/30' : '',
      isPassword ? 'pr-11' : '',
      stacked ? '' : 'peer',
    ]"
  >

      <label
        v-if="!stacked"
        :for="id"
        class="absolute z-10 origin-left cursor-text transition-all duration-200 text-base font-medium text-additional
               -top-6 inset-s-0 translate-y-0 scale-90
               peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:inset-s-4 peer-placeholder-shown:scale-100
               peer-focus:-top-6 peer-focus:translate-y-0 peer-focus:inset-s-0 peer-focus:scale-90 peer-focus:text-text
               peer-disabled:cursor-not-allowed peer-disabled:opacity-60"
        :class="{ 'text-error peer-focus:text-error': hasError }"
      >
        {{ label }}
      </label>

      <button
        v-if="isPassword"
        type="button"
        :disabled="disabled"
        class="absolute right-3 top-1/2 -translate-y-1/2 text-additional hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded p-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:text-additional"
        :aria-label="showPassword
          ? t('auth.fields.hidePassword')
          : t('auth.fields.showPassword')"
        @click="togglePassword"
      >
        <IconEyeOff v-if="showPassword" class="w-5 h-5" aria-hidden="true" />
        <IconEye v-else class="w-5 h-5" aria-hidden="true" />
      </button>
    </div>

    <p
      v-if="error"
      :id="`${id}-error`"
      class="text-sm text-error"
      role="alert"
    >
      {{ error }}
    </p>
  </div>
</template>
