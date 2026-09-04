<script setup>
import { ref, computed, nextTick, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    required: true,
    default: () => [],
  },
  options: {
    type: Array,
    required: true,
  },
  id: {
    type: String,
    default: () => `multiselect-${Math.random().toString(36).substr(2, 9)}`,
  },
  label: { type: String, required: true },
  placeholder: { type: String, default: '' },
  emptyStateLabel: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const inputRef = ref(null)
const searchQuery = ref('')
const isOpen = ref(false)
let blurTimeout = null

const selectedOptions = computed(() =>
  props.options.filter(option => props.modelValue.includes(option.id)),
)

const filteredOptions = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  return props.options.filter(option =>
    !props.modelValue.includes(option.id)
    && option.name.toLowerCase().includes(query),
  )
})

const selectOption = (option) => {
  emit('update:modelValue', [...props.modelValue, option.id])
  searchQuery.value = ''
  nextTick(() => inputRef.value?.focus())
}

const removeOption = (optionId) => {
  emit('update:modelValue', props.modelValue.filter(id => id !== optionId))
  nextTick(() => inputRef.value?.focus())
}

const handleBackspace = () => {
  if (searchQuery.value === '' && props.modelValue.length > 0) {
    removeOption(props.modelValue[props.modelValue.length - 1])
  }
}

const handleFocus = () => {
  clearTimeout(blurTimeout)
  isOpen.value = true
}

const handleBlur = () => {
  blurTimeout = setTimeout(() => { isOpen.value = false }, 150)
}

onBeforeUnmount(() => {
  clearTimeout(blurTimeout)
})
</script>

<template>
  <div class="flex flex-col gap-1.5 w-full">
    <label :for="id" class="text-sm font-medium text-text">
      {{ label }}
    </label>

    <div class="relative w-full">
      <div
        class="flex flex-wrap items-center gap-2 px-3 py-2 border rounded-lg bg-white border-border transition-colors min-h-[48px] cursor-text"
        @click="inputRef?.focus()"
      >
        <TransitionGroup
          tag="div"
          class="flex flex-wrap gap-1.5 items-center"
          enter-active-class="transition duration-150 ease-out"
          enter-from-class="opacity-0 scale-75"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition duration-150 ease-in absolute"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-75"
        >
          <span
            v-for="option in selectedOptions"
            :key="option.id"
            class="flex items-center gap-2 pl-3 pr-1.5 py-1 text-sm font-medium border rounded-md text-text bg-secondary/10 border-border"
          >
            <span class="break-words max-w-[180px]">{{ option.name }}</span>
            <button
              type="button"
              class="flex-shrink-0 rounded-md p-0.5 text-additional hover:text-error hover:bg-error/10 transition-colors"
              :aria-label="option.name"
              @click.stop="removeOption(option.id)"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" clip-rule="evenodd" />
              </svg>
            </button>
          </span>
        </TransitionGroup>

        <input
          :id="id"
          ref="inputRef"
          v-model="searchQuery"
          type="text"
          role="combobox"
          :aria-expanded="isOpen"
          :placeholder="modelValue.length === 0 ? placeholder : ''"
          class="flex-1 min-w-[60px] bg-transparent text-sm focus:outline-none py-0.5"
          @focus="handleFocus"
          @blur="handleBlur"
          @input="isOpen = true"
          @keydown.esc.prevent="isOpen = false"
          @keydown.backspace="handleBackspace"
        >
      </div>

      <div
        v-if="isOpen"
        class="absolute z-50 left-0 right-0 top-full mt-1 border rounded-lg shadow-lg bg-white border-border max-h-60 overflow-y-auto"
      >
        <ul role="listbox" class="py-1">
          <li
            v-for="option in filteredOptions"
            :key="option.id"
            role="option"
            class="px-4 py-2 text-sm cursor-pointer text-text hover:bg-secondary/10 transition-colors"
            @mousedown.prevent="selectOption(option)"
          >
            {{ option.name }}
          </li>
          <li v-if="filteredOptions.length === 0" role="status" class="px-4 py-3 text-sm text-additional text-center">
            {{ emptyStateLabel }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
