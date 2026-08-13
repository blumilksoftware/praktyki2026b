<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  id: { type: String, required: true },
  modelValue: { type: String, default: '' },
  options: { type: Array, default: () => [] },
  icon: { type: [Object, Function], required: true },
  placeholder: { type: String, default: '' },
  ariaLabel: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const highlightedIndex = ref(-1)

const matches = computed(() => {
  const query = props.modelValue.trim().toLowerCase()
  if (!query) return []
  return props.options.filter((option) => option.toLowerCase().includes(query)).slice(0, 8)
})

const showDropdown = computed(() => isOpen.value && matches.value.length > 0)

function onInput(event) {
  emit('update:modelValue', event.target.value)
  highlightedIndex.value = -1
  isOpen.value = true
}

function select(option) {
  emit('update:modelValue', option)
  isOpen.value = false
}

function onBlur() {
  setTimeout(() => { isOpen.value = false }, 150)
}

function onKeydown(event) {
  if (!showDropdown.value) return

  if (event.key === 'ArrowDown') {
    event.preventDefault()
    highlightedIndex.value = Math.min(highlightedIndex.value + 1, matches.value.length - 1)
  } else if (event.key === 'ArrowUp') {
    event.preventDefault()
    highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0)
  } else if (event.key === 'Enter' && highlightedIndex.value >= 0) {
    event.preventDefault()
    select(matches.value[highlightedIndex.value])
  } else if (event.key === 'Escape') {
    isOpen.value = false
  }
}
</script>

<template>
  <div class="relative">
    <component :is="icon" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-additional" aria-hidden="true" />
    <input
      :id="id"
      type="text"
      autocomplete="off"
      :value="modelValue"
      :placeholder="placeholder"
      :aria-label="ariaLabel"
      class="w-full rounded-lg border border-border py-1.5 pl-8 pr-2 text-sm text-text placeholder:text-additional focus:outline-none focus:ring-2 focus:ring-primary/40 sm:w-48"
      @input="onInput"
      @focus="isOpen = true"
      @blur="onBlur"
      @keydown="onKeydown"
    >

    <ul
      v-if="showDropdown"
      class="absolute left-0 right-0 top-full z-20 mt-1 overflow-hidden rounded-lg border border-border bg-white shadow-lg"
    >
      <li
        v-for="(option, index) in matches"
        :key="option"
        class="cursor-pointer px-4 py-2 text-sm text-text"
        :class="index === highlightedIndex ? 'bg-primary/10' : 'hover:bg-background'"
        @mousedown.prevent="select(option)"
      >
        {{ option }}
      </li>
    </ul>
  </div>
</template>
