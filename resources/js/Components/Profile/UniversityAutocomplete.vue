<script setup>
import { ref, computed, watch } from 'vue'
import { useUniversitySearch } from '@/Composables/useUniversitySearch'
import BaseInput from '@/Components/Base/BaseInput.vue'

const props = defineProps({
  id: { type: String, required: true },
  modelValue: { type: String, default: '' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  error: { type: String, default: null },
})

const emit = defineEmits(['update:modelValue', 'select'])

const { suggestions, fetchSuggestions, clearSuggestions } =
  useUniversitySearch()

const isOpen = ref(false)
const highlightedIndex = ref(-1)
const displayValue = ref(props.modelValue)

watch(() => props.modelValue, (value) => {
  if (value !== displayValue.value) {
    displayValue.value = value
  }
})

const showDropdown = computed(() => isOpen.value && suggestions.value.length >
  0)

const onInput = (value) => {
  displayValue.value = value
  emit('update:modelValue', value)
  highlightedIndex.value = -1
  isOpen.value = true
  fetchSuggestions(value)
}

const selectUniversity = (university) => {
  displayValue.value = university.name
  emit('update:modelValue', university.name)
  emit('select', university)
  isOpen.value = false
  clearSuggestions()
}

const onBlur = () => {
  setTimeout(() => { isOpen.value = false }, 150)
}

const onKeydown = (event) => {
  if (!showDropdown.value) return

  if (event.key === 'ArrowDown') {
    event.preventDefault()
    highlightedIndex.value = Math.min(highlightedIndex.value + 1,
      suggestions.value.length - 1)
  } else if (event.key === 'ArrowUp') {
    event.preventDefault()
    highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0)
  } else if (event.key === 'Enter' && highlightedIndex.value >= 0) {
    event.preventDefault()
    selectUniversity(suggestions.value[highlightedIndex.value])
  } else if (event.key === 'Escape') {
    isOpen.value = false
  }
}
</script>

<template>
  <BaseInput
    :id="id" :model-value="displayValue" :label="label"
    :placeholder="placeholder" :error="error"
    dropdown :dropdown-open="isOpen" autocomplete="off"
    @update:model-value="onInput" @focus="isOpen = true" @blur="onBlur"
    @keydown="onKeydown"
  />
  <ul v-if="showDropdown" class="absolute z-20 mt-1 w-full overflow-hidden
  rounded-xl border border-border bg-white shadow-lg">
    <li
      v-for="(university, index) in suggestions" :key="university.id"
      class="cursor-pointer px-4 py-2 text-sm"
      :class="index === highlightedIndex ? 'bg-secondary/10' :
  'hover:bg-secondary/5'"
      @mousedown.prevent="selectUniversity(university)"
    >
      {{ university.name }}
    </li>
  </ul>
</template>
