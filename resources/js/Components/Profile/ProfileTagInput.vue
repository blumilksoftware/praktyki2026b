<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  id: { type: String, required: true },
  modelValue: { type: Array, default: () => [] },
  label: { type: String, required: true },
  placeholder: { type: String, default: '' },
  error: { type: String, default: undefined },
})

const emit = defineEmits(['update:modelValue'])
const { t } = useI18n()
const inputValue = ref('')

function addTag(raw) {
  const tag = raw.trim().slice(0, 50)
  if (!tag || props.modelValue.length >= 20) return
  if (props.modelValue.some((item) => item.toLowerCase() === tag.toLowerCase())) return
  emit('update:modelValue', [...props.modelValue, tag])
  inputValue.value = ''
}

function removeTag(index) {
  const tags = [...props.modelValue]
  tags.splice(index, 1)
  emit('update:modelValue', tags)
}

function onKeydown(event) {
  if (event.key === 'Enter' || event.key === ',') {
    event.preventDefault()
    addTag(inputValue.value)
  }
  if (event.key === 'Backspace' && !inputValue.value && props.modelValue.length) {
    removeTag(props.modelValue.length - 1)
  }
}
</script>

<template>
  <div class="flex w-full flex-col gap-1">
    <label :for="id" class="mb-1 block text-additional text-sm">{{ label }}</label>
    <div
      class="flex min-h-[44px] cursor-text items-center gap-2 rounded-lg border bg-white px-3 py-2"
      :class="error ? 'border-error' : 'border-border'"
    >
      <div class="flex min-w-0 flex-1 flex-wrap items-center gap-1.5">
        <span
          v-for="(tag, index) in modelValue"
          :key="`${tag}-${index}`"
          class="inline-flex max-w-full items-center gap-1 rounded-full border border-primary/20 bg-primary/10 px-2.5 py-1 text-sm font-medium text-primary"
        >
          <span class="truncate">{{ tag }}</span>
          <button
            type="button"
            class="shrink-0 rounded-md p-0.5 text-additional transition-colors hover:bg-red-500/10 hover:text-red-500"
            :aria-label="t('student.profile.tags.remove')"
            @click="removeTag(index)"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" clip-rule="evenodd" />
            </svg>
          </button>
        </span>
        <input
          :id="id"
          v-model="inputValue"
          type="text"
          class="min-w-[8rem] flex-1 border-0 bg-transparent py-1 text-sm text-text outline-none placeholder:text-additional focus:ring-0"
          :placeholder="placeholder"
          @keydown="onKeydown"
          @blur="addTag(inputValue)"
        >
      </div>
    </div>
    <p v-if="error" class="mt-1 text-error text-sm" role="alert">{{ error }}</p>
  </div>
</template>
