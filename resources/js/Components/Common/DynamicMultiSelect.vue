<script setup>
import { ref, computed, nextTick, onBeforeUnmount, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  modelValue: {
    type: Array,
    required: true,
    default: () => [],
  },
  options: {
    type: Array,
    required: false,
    default: () => [],
  },
  max: {
    type: Number,
    default: null,
  },
  allowCustom: {
    type: Boolean,
    default: false,
  },
  id: {
    type: String,
    default: () => `multiselect-${Math.random().toString(36).substr(2, 9)}`,
  },
})

const emit = defineEmits(['update:modelValue'])

const inputRef = ref(null)
const dropdownRef = ref(null)
const itemRefs = ref([])
const searchQuery = ref('')
const errorMsg = ref('')
const isOpen = ref(false)
const activeIndex = ref(0)
const t = useI18n().t

let errorTimeout = null
let blurTimeout = null
const errorShowTime = ref(3000)

const filteredOptions = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  return props.options.filter(option => {
    const matchesSearch = option.toLowerCase().includes(query)
    const isAlreadySelected = props.modelValue.includes(option)
    return matchesSearch && !isAlreadySelected
  })
})

const activeOptionId = computed(() => {
  if (!isOpen.value || filteredOptions.value.length === 0) return undefined
  return `${props.id}-opt-${activeIndex.value}`
})

watch(filteredOptions, () => {
  activeIndex.value = 0
})

const triggerError = (msg = '') => {
  clearTimeout(errorTimeout)
  errorMsg.value = ''
  nextTick(() => {
    errorMsg.value = msg
    errorTimeout = setTimeout(() => { errorMsg.value = '' }, errorShowTime.value)
  })
}

const selectOption = (option) => {
  const cleanOption = option.trim()
  
  if (props.max && props.modelValue.length >= props.max) {
    triggerError(t('dynamicList.errors.maxReached', { max: props.max }))
    return
  }

  if (!cleanOption) {
    triggerError(t('dynamicList.errors.empty'))
    return
  }

  if (props.modelValue.includes(cleanOption)) {
    triggerError(t('dynamicList.errors.exists'))
    return
  }

  emit('update:modelValue', [...props.modelValue, cleanOption])
  searchQuery.value = ''
  nextTick(() => inputRef.value?.focus())
}

const onKeyDownArrow = (direction) => {
  if (!isOpen.value) {
    isOpen.value = true
    return
  }
  
  const maxIndex = filteredOptions.value.length - 1
  if (maxIndex < 0) return

  if (direction === 'down') {
    activeIndex.value = activeIndex.value >= maxIndex ? 0 : activeIndex.value + 1
  } else {
    activeIndex.value = activeIndex.value <= 0 ? maxIndex : activeIndex.value - 1
  }

  nextTick(() => {
    const activeElement = itemRefs.value[activeIndex.value]
    if (activeElement) {
      activeElement.scrollIntoView({ block: 'nearest' })
    }
  })
}

const onKeyDownEnter = () => {
  if (isOpen.value && filteredOptions.value.length > 0) {
    selectOption(filteredOptions.value[activeIndex.value])
  } else if (props.allowCustom && searchQuery.value.trim()) {
    selectOption(searchQuery.value.trim())
  } else if (!props.allowCustom && searchQuery.value.trim()) {
    triggerError(t('dynamicList.errors.invalidSelection'))
  }
}

const removeItem = (itemToRemove) => {
  const updatedList = props.modelValue.filter(item => item !== itemToRemove)
  emit('update:modelValue', updatedList)
  nextTick(() => inputRef.value?.focus())
}

const handleBackspace = () => {
  if (searchQuery.value === '' && props.modelValue.length > 0) {
    removeItem(props.modelValue[props.modelValue.length - 1])
  }
}

const handleFocus = () => {
  clearTimeout(blurTimeout)
  isOpen.value = true
}

const handleBlur = () => {
  blurTimeout = setTimeout(() => { isOpen.value = false }, 150)
}

const toggleDropdown = () => {
  if (isOpen.value) {
    isOpen.value = false
  } else {
    isOpen.value = true
    nextTick(() => inputRef.value?.focus())
  }
}

onBeforeUnmount(() => {
  clearTimeout(errorTimeout)
  clearTimeout(blurTimeout)
})
</script>

<template>
  <div class="flex flex-col gap-1 w-full">
    <label :for="props.id" class="sr-only">
      {{ $t('dynamicList.accessibility.label') }}
    </label>

    <div class="relative w-full">
      <div
        class="flex flex-wrap items-center gap-2 px-3 py-2 border rounded-md bg-background border-border transition-colors min-h-[48px] cursor-text"
        @click="inputRef?.focus()"
      >
        <TransitionGroup
          tag="div"
          class="flex flex-wrap gap-1.5 items-center"
          aria-live="polite"
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 scale-75"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition duration-200 ease-in absolute"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-75"
          move-class="transition duration-300 ease-in-out"
        >
          <span
            v-for="item in modelValue"
            :key="item"
            class="flex items-center gap-2 pl-4 pr-1.5 py-1 text-sm font-medium border rounded-md text-text bg-background border-border"
          >
            <span class="break-all max-w-[150px] sm:max-w-[200px]">{{ item }}</span>
            <button
              type="button"
              class="flex-shrink-0 rounded-md p-1 text-additional hover:text-red-500 hover:bg-red-500/10 transition-colors"
              :aria-label="$t('dynamicList.accessibility.removeItem', { item })"
              @click.stop="removeItem(item)"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" clip-rule="evenodd" />
              </svg>
            </button>
          </span>
        </TransitionGroup>

        <input
          :id="props.id"
          ref="inputRef"
          v-model="searchQuery"
          type="text"
          role="combobox"
          :aria-expanded="isOpen"
          :aria-controls="`${props.id}-listbox`"
          :aria-activedescendant="activeOptionId"
          aria-haspopup="listbox"
          :aria-invalid="!!errorMsg"
          :aria-describedby="errorMsg ? `${props.id}-error` : undefined"
          :disabled="props.max && modelValue.length >= props.max"
          :placeholder="modelValue.length === 0 ? $t('dynamicList.placeholder') : ''"
          class="flex-1 min-w-[60px] bg-transparent text-sm focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed py-0.5"
          @focus="handleFocus"
          @blur="handleBlur"
          @input="isOpen = true"
          @keydown.down.prevent="onKeyDownArrow('down')"
          @keydown.up.prevent="onKeyDownArrow('up')"
          @keydown.enter.prevent="onKeyDownEnter"
          @keydown.esc.prevent="isOpen = false"
          @keydown.backspace="handleBackspace"
        >
        
        <button
          type="button"
          class="text-additional hover:text-text transition-colors px-1 cursor-pointer focus:outline-none"
          :aria-label="isOpen ? $t('dynamicList.accessibility.closeMenu') : $t('dynamicList.accessibility.openMenu')"
          @mousedown.prevent.stop="toggleDropdown"
        >
          <svg xmlns="http://www.w3.org/2000/svg" :class="['w-4 h-4 transition-transform duration-200', isOpen ? 'rotate-180' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
      </div>

      <Transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="opacity-0 scale-95 -translate-y-2"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition duration-75 ease-in"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-95 -translate-y-2"
      >
        <div
          v-if="isOpen"
          ref="dropdownRef"
          class="absolute z-50 left-0 right-0 top-full mt-1 border rounded-md shadow-lg bg-background border-border max-h-60 overflow-y-auto"
        >
          <ul :id="`${props.id}-listbox`" role="listbox" :aria-label="$t('dynamicList.accessibility.suggestions')" class="py-1">
            <li
              v-for="(option, index) in filteredOptions"
              :id="`${props.id}-opt-${index}`"
              :key="option"
              :ref="el => { if (el) itemRefs[index] = el }"
              role="option"
              :aria-selected="index === activeIndex"
              :class="[
                'px-4 py-2 text-sm cursor-pointer transition-colors flex justify-between items-center',
                index === activeIndex ? 'bg-text/10 text-text font-medium' : 'text-text hover:bg-text/5'
              ]"
              @mousedown.prevent="selectOption(option)"
            >
              <span>{{ option }}</span>
              <span v-if="index === activeIndex" aria-hidden="true" class="text-[10px] opacity-40 px-1.5 py-0.5 border rounded bg-background">
                Enter
              </span>
            </li>
            <li v-if="filteredOptions.length === 0" role="status" aria-live="polite" class="px-4 py-3 text-sm text-additional text-center">
              <div v-if="searchQuery && allowCustom">
                {{ $t('dynamicList.pressEnterToCreate') }}
              </div>
              <div v-else>
                {{ searchQuery ? $t('dynamicList.noResults') : $t('dynamicList.emptyState') }}
              </div>
            </li>
          </ul>
        </div>
      </Transition>
    </div> <div class="min-h-[20px] px-1">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-1"
      >
        <span v-if="errorMsg" :id="`${props.id}-error`" role="alert" class="block text-xs text-red-500">
          {{ errorMsg }}
        </span>
      </Transition>
    </div>

    <button
      v-if="modelValue.length > 1"
      type="button"
      class="text-xs text-red-500 hover:underline self-start mt-1"
      :aria-label="$t('dynamicList.accessibility.clearAll')"
      @click="emit('update:modelValue', [])"
    >
      {{ $t('dynamicList.clearAll') }}
    </button>
  </div>
</template>
