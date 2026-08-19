<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  id: { type: String, required: true },
  label: { type: String, required: true },
  options: { type: Array, required: true },
  error: { type: String, default: undefined },
  required: { type: Boolean, default: false },
  placeholder: { type: String, default: undefined },
  stacked: { type: Boolean, default: true },
  disabled: { type: Boolean, default: false },
  hint: { type: String, default: undefined },
})

const model = defineModel({ type: String, required: true })

const selected = computed({
  get: () => model.value,
  set: (value) => {
    if (!props.disabled) {
      model.value = value
    }
  },
})

const hasError = computed(() => !!props.error)

function blockPointer(event) {
  if (props.disabled) {
    event.preventDefault()
  }
}

function blockKeys(event) {
  if (props.disabled && event.key !== 'Tab' && event.key !== 'Escape') {
    event.preventDefault()
  }
}

const describedBy = computed(() => [
  props.error ? `${props.id}-error` : null,
  props.hint ? `${props.id}-hint` : null,
].filter(Boolean).join(' ') || undefined)

const isFocused = ref(false)
const isFloating = computed(() => isFocused.value || !!model.value)

const labelClasses = computed(() => {
  const base = 'pointer-events-none absolute z-10 origin-left transition-all duration-200 font-medium'

  if (isFloating.value) {
    return `${base} -top-6 inset-s-0 translate-y-0 scale-90 text-sm ${hasError.value ? 'text-error' : 'text-text'}`
  }

  return `${base} top-1/2 -translate-y-1/2 inset-s-4 scale-100 text-base ${hasError.value ? 'text-error' : 'text-additional'}`
})
</script>

<template>
  <div class="flex w-full flex-col gap-1.5" :class="stacked ? '' : 'pt-6'">
    <label
      v-if="stacked"
      :for="id"
      class="text-sm font-medium text-text"
      :class="{ 'cursor-not-allowed opacity-60': disabled }"
    >
      {{ label }}
      <span v-if="required" aria-hidden="true" class="text-error">*</span>
    </label>

    <div class="relative">
      <select
        :id="id"
        v-model="selected"
        :required="required"
        :aria-disabled="disabled ? true : undefined"
        :aria-invalid="hasError ? true : undefined"
        :aria-describedby="describedBy"
        class="w-full rounded-lg border border-border bg-white px-4 py-3 text-base text-text focus:border-text focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all"
        :class="[
          hasError ? 'border-error focus:border-error focus:ring-error/30' : '',
          disabled ? 'cursor-not-allowed bg-gray-50 text-additional' : '',
        ]"
        @focus="isFocused = true"
        @blur="isFocused = false"
        @mousedown="blockPointer"
        @keydown="blockKeys"
      >
        <option v-if="placeholder" value="" disabled>
          {{ placeholder }}
        </option>
        <option v-else-if="!stacked" value="" disabled />
        <option v-for="option in options" :key="option.value" :value="option.value">
          {{ option.label }}
        </option>
      </select>

      <label v-if="!stacked" :for="id" :class="labelClasses">
        {{ label }}
        <span v-if="required" aria-hidden="true" class="text-error">*</span>
      </label>
    </div>

    <p v-if="error" :id="`${id}-error`" class="text-sm text-error" role="alert">
      {{ error }}
    </p>

    <p v-if="hint" :id="`${id}-hint`" class="text-sm text-additional">
      {{ hint }}
    </p>
  </div>
</template>
