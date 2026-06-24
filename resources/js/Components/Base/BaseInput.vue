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
  autocomplete: { type: String, default: undefined },
  required: { type: Boolean, default: false },
})

const model = defineModel({ type: String, required: true })

const { t } = useI18n()

const isPassword = computed(() => props.type === 'password')
const { showPassword, togglePassword } = useTogglePassword()

const inputType = computed(() => {
  if (!isPassword.value) {
    return props.type
  }
  return showPassword.value ? 'text' : 'password'
})
</script>

<template>
  <div class="flex flex-col gap-1.5 w-full">
    <label :for="id" class="text-sm font-medium text-text">
      {{ label }}
    </label>

    <div class="relative">
      <input
        :id="id"
        v-model="model"
        :type="inputType"
        :autocomplete="autocomplete"
        :required="required"
        :aria-invalid="error ? true : undefined"
        :aria-describedby="error ? `${id}-error` : undefined"
        class="w-full rounded-lg border border-border bg-white px-4 py-3 text-base text-text placeholder:text-additional focus:border-text focus:outline-none focus:ring-2 focus:ring-primary/30 transition"
        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-200': error }"
      >

      <button
        v-if="isPassword"
        type="button"
        class="absolute right-3 top-1/2 -translate-y-1/2 text-additional hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded p-0.5"
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
      class="text-sm text-red-600"
      role="alert"
    >
      {{ error }}
    </p>
  </div>
</template>
