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
  <div>
    <label :for="id" class="mb-1 block text-additional text-sm">{{ label }}</label>
    <div
      class="flex flex-wrap gap-2 rounded-lg border bg-white px-3 py-2"
      :class="error ? 'border-error' : 'border-border'"
    >
      <span
        v-for="(tag, index) in modelValue"
        :key="`${tag}-${index}`"
        class="inline-flex items-center gap-1 rounded-full border border-slate-400 bg-slate-100 px-3 py-1 text-sm font-medium text-text"
      >
        {{ tag }}
        <button
          type="button"
          class="rounded-full px-1 text-additional hover:bg-white hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          :aria-label="t('student.profile.tags.remove')"
          @click="removeTag(index)"
        >
          ×
        </button>
      </span>
      <input
        :id="id"
        v-model="inputValue"
        type="text"
        class="min-w-[8rem] flex-1 border-0 bg-transparent p-0 text-sm focus:outline-none"
        :placeholder="placeholder"
        @keydown="onKeydown"
        @blur="addTag(inputValue)"
      >
    </div>
    <p v-if="error" class="mt-1 text-error text-sm" role="alert">{{ error }}</p>
  </div>
</template>
