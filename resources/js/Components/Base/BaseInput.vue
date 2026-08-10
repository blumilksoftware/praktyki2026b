<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useTogglePassword } from '@/Composables/useTogglePassword'
import { IconChevronDown, IconEye, IconEyeOff } from '@tabler/icons-vue'

defineOptions({ inheritAttrs: false })

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
  dropdown: { type: Boolean, default: false },
  dropdownOpen: { type: Boolean, default: false },
})

const model = defineModel({ type: String, required: true })

const { t } = useI18n()

const isPassword = computed(() => props.type === 'password')
const { showPassword, togglePassword } = useTogglePassword()

const hasError = computed(() => Boolean(props.error) || props.invalid)

const inputType = computed(() =>
  isPassword.value ? (showPassword.value ? 'text' : 'password') : props.type,
)

const wrapperClass = computed(() => (
  props.compact ? 'pt-5' : 'pt-6'
))
</script>

<template>
  <div
    class="flex w-full flex-col gap-1.5"
    :class="stacked ? '' : wrapperClass"
  >
    <label
      v-if="stacked"
      :for="id"
      class="mb-1 block text-sm font-medium text-text"
      :class="{
        'text-error': hasError,
        'cursor-not-allowed opacity-60': disabled,
      }"
    >
      {{ label }}
      <span v-if="required" aria-hidden="true" class="text-error">*</span>
    </label>

    <div class="relative">
      <input
        :id="id"
        v-model="model"
        v-bind="$attrs"
        :type="inputType"
        :autocomplete="autocomplete"
        :required="required"
        :maxlength="maxlength"
        :aria-invalid="hasError ? true : undefined"
        :aria-describedby="error ? `${id}-error` : undefined"
        :placeholder="stacked ? placeholder : ' '"
        class="w-full rounded-lg border border-border bg-white px-4 py-3 text-base text-text transition-all focus:border-text focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-additional"
        :class="[
          hasError ? 'border-error focus:border-error focus:ring-error/30' : '',
          isPassword || dropdown ? 'pr-11' : '',
          !stacked ? 'peer' : '',
        ]"
      >

      <label
        v-if="!stacked"
        :for="id"
        class="absolute z-10 origin-left cursor-text text-base font-medium text-additional transition-all duration-200
        -top-6 inset-s-0 scale-90
        peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:inset-s-4 peer-placeholder-shown:scale-100
        peer-focus:-top-6 peer-focus:translate-y-0 peer-focus:inset-s-0 peer-focus:scale-90 peer-focus:text-text"
        :class="{ 'text-error peer-focus:text-error': hasError }"
      >
        {{ label }}
        <span v-if="required" class="text-error" aria-hidden="true">*</span>
      </label>

      <IconChevronDown
        v-if="dropdown"
        class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-additional transition-transform"
        :class="{ 'rotate-180': dropdownOpen }"
        aria-hidden="true"
      />

      <button
        v-if="isPassword"
        type="button"
        :disabled="disabled"
        class="absolute right-3 top-1/2 -translate-y-1/2 rounded p-0.5 text-additional hover:text-text disabled:cursor-not-allowed disabled:opacity-50"
        :aria-label="showPassword
          ? t('common.fields.hidePassword')
          : t('common.fields.showPassword')"
        @click="togglePassword"
      >
        <IconEyeOff v-if="showPassword" class="h-5 w-5" aria-hidden="true" />
        <IconEye v-else class="h-5 w-5" aria-hidden="true" />
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
