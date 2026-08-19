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
    default: () => [],
  },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  error: { type: String, default: undefined },
  max: { type: Number, default: null },
  allowCustom: { type: Boolean, default: false },
  remote: { type: Boolean, default: false },
  id: {
    type: String,
    default: () => `multiselect-${Math.random().toString(36).substr(2, 9)}`,
  },
  stacked: { type: Boolean, default: true },
  hideSelectedWhileTyping: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'search'])

const inputRef = ref(null)
const itemRefs = ref([])
const searchQuery = ref('')
const errorMsg = ref('')
const isOpen = ref(false)
const activeIndex = ref(0)
const { t } = useI18n()

let errorTimeout = null
let blurTimeout = null
const errorShowTime = 3000

function normalizeOption(option) {
  if (typeof option === 'string') {
    return { value: option, label: option }
  }

  const value = option.value ?? option.id ?? option.name ?? option.label
  const label = option.label ?? option.name ?? option.title ?? option.value ?? option.id

  return {
    value: value ?? '',
    label: label ?? '',
  }
}

const normalizedOptions = computed(() => props.options.map(normalizeOption))

const shouldHideSelected = computed(() =>
  props.hideSelectedWhileTyping && searchQuery.value.length > 0,
)

const inputPlaceholder = computed(() => {
  if (props.modelValue.length > 0 && !shouldHideSelected.value) return ''
  if (!props.stacked && !isFloating.value) return ''
  return props.placeholder || t('dynamicList.placeholder')
})

const hasError = computed(() => Boolean(props.error || errorMsg.value))

const filteredOptions = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  return normalizedOptions.value.filter((option) => {
    const matchesSearch = props.remote || String(option.label ?? '').toLowerCase().includes(query)
    const isAlreadySelected = props.modelValue.includes(option.value)
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

function optionLabel(value) {
  return normalizedOptions.value.find((option) => option.value === value)?.label ?? value
}

function triggerError(msg = '') {
  clearTimeout(errorTimeout)
  errorMsg.value = ''
  nextTick(() => {
    errorMsg.value = msg
    errorTimeout = setTimeout(() => { errorMsg.value = '' }, errorShowTime)
  })
}

function selectOption(option) {
  const value = typeof option === 'string' ? option.trim() : option.value
  const label = typeof option === 'string' ? option.trim() : option.label

  if (props.max && props.modelValue.length >= props.max) {
    triggerError(t('dynamicList.errors.maxReached', { max: props.max }))
    return
  }

  if (!value) {
    triggerError(t('dynamicList.errors.empty'))
    return
  }

  if (props.modelValue.includes(value)) {
    triggerError(t('dynamicList.errors.exists'))
    return
  }

  if (!props.allowCustom && !normalizedOptions.value.some((item) => item.value === value)) {
    triggerError(t('dynamicList.errors.invalidSelection'))
    return
  }

  emit('update:modelValue', [...props.modelValue, value])
  searchQuery.value = ''
  emit('search', '')
  nextTick(() => inputRef.value?.focus())
}

function onSearchInput() {
  isOpen.value = true
  emit('search', searchQuery.value)
}

function onKeyDownArrow(direction) {
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
    itemRefs.value[activeIndex.value]?.scrollIntoView({ block: 'nearest' })
  })
}

function onKeyDownEnter() {
  if (isOpen.value && filteredOptions.value.length > 0) {
    selectOption(filteredOptions.value[activeIndex.value])
    return
  }
  if (props.allowCustom && searchQuery.value.trim()) {
    selectOption(searchQuery.value.trim())
    return
  }
  if (!props.allowCustom && searchQuery.value.trim()) {
    triggerError(t('dynamicList.errors.invalidSelection'))
  }
}

function removeItem(itemToRemove) {
  emit('update:modelValue', props.modelValue.filter((item) => item !== itemToRemove))
  nextTick(() => inputRef.value?.focus())
}

function handleBackspace() {
  if (searchQuery.value === '' && props.modelValue.length > 0) {
    removeItem(props.modelValue[props.modelValue.length - 1])
  }
}

function handleFocus() {
  clearTimeout(blurTimeout)
  isOpen.value = true
}

function handleBlur() {
  blurTimeout = setTimeout(() => { isOpen.value = false }, 150)
}

function toggleDropdown() {
  if (isOpen.value) {
    isOpen.value = false
    return
  }
  isOpen.value = true
  nextTick(() => inputRef.value?.focus())
}

onBeforeUnmount(() => {
  clearTimeout(errorTimeout)
  clearTimeout(blurTimeout)
})

const isFloating = computed(() =>
  isOpen.value || props.modelValue.length > 0 || searchQuery.value.length > 0,
)

const labelClasses = computed(() => {
  const base = 'pointer-events-none absolute z-10 origin-left transition-all duration-200 font-medium'

  if (isFloating.value) {
    return `${base} -top-6 inset-s-0 translate-y-0 scale-90 text-sm ${hasError.value ? 'text-error' : 'text-text'}`
  }

  return `${base} top-1/2 -translate-y-1/2 inset-s-4 scale-100 text-base ${hasError.value ? 'text-error' : 'text-additional'}`
})
</script>

<template>
  <div class="flex w-full flex-col gap-1" :class="stacked ? '' : 'pt-6'">
    <label
      v-if="label && stacked"
      :for="props.id"
      class="mb-1 block text-text text-sm"
    >
      {{ label }}
    </label>

    <div class="relative w-full">
      <label
        v-if="label && !stacked"
        :for="props.id"
        :class="labelClasses"
      >
        {{ label }}
      </label>

      <div
        class="flex min-h-11 cursor-text flex-nowrap items-center gap-2 rounded-lg border bg-white px-3 py-2"
        :class="hasError ? 'border-error' : 'border-border'"
        @click="inputRef?.focus()"
      >
        <div class="flex min-w-0 flex-1 flex-wrap items-center gap-1.5">
          <template v-if="!shouldHideSelected">
            <span
              v-for="item in modelValue"
              :key="item"
              class="inline-flex max-w-full items-center gap-1 rounded-full border border-primary/20 bg-primary/10 px-2.5 py-1 text-sm font-medium text-primary"
            >
              <span class="truncate">{{ optionLabel(item) }}</span>
              <button
                type="button"
                class="shrink-0 rounded-md p-0.5 text-additional transition-colors hover:bg-red-500/10 hover:text-red-500"
                :aria-label="t('dynamicList.accessibility.removeItem', { item: optionLabel(item) })"
                @click.stop="removeItem(item)"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" clip-rule="evenodd" />
                </svg>
              </button>
            </span>
          </template>

          <input
            :id="props.id"
            ref="inputRef"
            v-model="searchQuery"
            type="text"
            role="combobox"
            :aria-expanded="isOpen"
            :aria-controls="`${props.id}-listbox`"
            :aria-activedescendant="activeOptionId"
            :aria-label="t('dynamicList.accessibility.inputLabel')"
            aria-haspopup="listbox"
            :aria-invalid="hasError ? true : undefined"
            :aria-describedby="hasError ? `${props.id}-error` : undefined"
            :disabled="props.max && modelValue.length >= props.max"
            :placeholder="inputPlaceholder"
            class="min-w-[3ch] flex-1 border-0 bg-transparent py-1 text-sm text-text outline-none placeholder:text-additional focus:ring-0"
            @focus="handleFocus"
            @blur="handleBlur"
            @input="onSearchInput"
            @keydown.down.prevent="onKeyDownArrow('down')"
            @keydown.up.prevent="onKeyDownArrow('up')"
            @keydown.enter.prevent="onKeyDownEnter"
            @keydown.esc.prevent="isOpen = false"
            @keydown.backspace="handleBackspace"
          >
        </div>

        <button
          type="button"
          class="shrink-0 px-1 text-additional transition-colors hover:text-text focus:outline-none"
          :aria-label="isOpen ? t('dynamicList.accessibility.closeMenu') : t('dynamicList.accessibility.openMenu')"
          @mousedown.prevent.stop="toggleDropdown"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="isOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
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
          class="absolute left-0 right-0 top-full z-50 mt-1 max-h-60 overflow-y-auto rounded-md border border-border bg-background shadow-lg"
        >
          <ul :id="`${props.id}-listbox`" role="listbox" :aria-label="t('dynamicList.accessibility.suggestions')" class="py-1">
            <li
              v-for="(option, index) in filteredOptions"
              :id="`${props.id}-opt-${index}`"
              :key="option.value"
              :ref="(el) => { if (el) itemRefs[index] = el }"
              role="option"
              :aria-selected="index === activeIndex"
              :class="[
                'flex cursor-pointer items-center justify-between px-4 py-2 text-sm transition-colors',
                index === activeIndex ? 'bg-primary/10 font-medium text-primary' : 'text-text hover:bg-primary/5',
              ]"
              @mousedown.prevent="selectOption(option)"
            >
              <span>{{ option.label }}</span>
              <span v-if="index === activeIndex" aria-hidden="true" class="rounded border bg-background px-1.5 py-0.5 text-[10px] opacity-40">
                Enter
              </span>
            </li>
            <li v-if="filteredOptions.length === 0" role="status" aria-live="polite" class="px-4 py-3 text-center text-additional text-sm">
              {{ searchQuery ? t('dynamicList.noResults') : t('dynamicList.emptyState') }}
            </li>
          </ul>
        </div>
      </Transition>
    </div>

    <p
      v-if="error || errorMsg"
      :id="`${props.id}-error`"
      class="mt-1 text-error text-sm"
      role="alert"
    >
      {{ error || errorMsg }}
    </p>

    <button
      v-if="modelValue.length > 1"
      type="button"
      class="mt-1 self-start text-black text-xs hover:underline cursor-pointer"
      :aria-label="t('dynamicList.accessibility.clearAll')"
      @click="emit('update:modelValue', [])"
    >
      {{ t('dynamicList.clearAll') }}
    </button>
  </div>
</template>
