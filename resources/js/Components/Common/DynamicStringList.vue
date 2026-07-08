<script setup>
import { ref, computed, nextTick, onBeforeUnmount } from 'vue'
import { useI18n } from 'vue-i18n'

let blurTimeout = null
let errorTimeout = null

const props = defineProps({
  modelValue: {
    type: Array,
    required: true,
    default: () => [],
  },
})

const emit = defineEmits(['update:modelValue'])
const inputRef = ref(null)
const newItem = ref('')
const errorMsg = ref('')
const t = useI18n().t
const blurTime = ref(150)
const errorShowTime = ref(3000)
const isEditing = ref(false)

const isNewItemValid = computed(() => newItem.value.trim().length > 0)

const triggerError = (msg = '') => {
  clearTimeout(errorTimeout)
  errorMsg.value = ''

  nextTick(() => {
    errorMsg.value = msg
    errorTimeout = setTimeout(() => { errorMsg.value = '' }, errorShowTime.value)
  })
}

const addItem = () => {
  const value = newItem.value.trim()
  
  if (!value) {
    triggerError(t('dynamicList.errors.empty'))
    return
  }

  if (props.modelValue.includes(value)) {
    triggerError(t('dynamicList.errors.exists'))
    return
  }

  emit('update:modelValue', [...props.modelValue, value])
  newItem.value = ''
  isEditing.value = false
  
  nextTick(() => inputRef.value?.focus())
}

const handleBlur = () => {
  blurTimeout = setTimeout(() => {
    if (!isEditing.value) return

    const value = newItem.value.trim()
    if (value && !props.modelValue.includes(value)) {
      emit('update:modelValue', [...props.modelValue, value])
      newItem.value = ''
    }
    
    isEditing.value = false
  }, blurTime.value)
}

const handlePaste = (e) => {
  const pasteData = e.clipboardData.getData('text')
  const separator = ','

  if (pasteData.includes(separator)) {
    e.preventDefault()
    const rawItems = pasteData.split(separator).map(item => item.trim())
    const uniquePastedItems = [...new Set(rawItems)]
    
    const itemsToAdd = uniquePastedItems.filter(
      item => item && !props.modelValue.includes(item),
    )
    
    if (itemsToAdd.length > 0) {
      emit('update:modelValue', [...props.modelValue, ...itemsToAdd])
    }
    newItem.value = ''
  }
}

const removeItem = (indexToRemove) => {
  const updatedList = props.withProps || props.modelValue.filter((_, index) => index !== indexToRemove)
  emit('update:modelValue', updatedList)
  inputRef.value?.focus()
}

const editItem = (indexToEdit) => {
  const itemValue = props.modelValue[indexToEdit]
  removeItem(indexToEdit)
  newItem.value = itemValue
  isEditing.value = true
  inputRef.value?.focus()
}

const handleBackspace = () => {
  if (newItem.value === '' && props.modelValue.length > 0) {
    editItem(props.modelValue.length - 1)
  }
}

onBeforeUnmount(() => {
  clearTimeout(blurTimeout)
  clearTimeout(errorTimeout)
})
</script>

<template>
  <div class="flex flex-col gap-2">
    <div class="flex flex-col gap-1">
      <div
        class="flex items-center gap-3"
      >
        <input
          ref="inputRef"
          v-model="newItem"
          type="text"
          :placeholder="$t('dynamicList.placeholder')"
          :aria-label="$t('dynamicList.placeholder')"
          :aria-invalid="!!errorMsg"
          :aria-describedby="errorMsg ? 'dynamic-list-error' : undefined"
          class="flex-1 px-4 py-2 text-sm transition-shadow border rounded-md text-text bg-background border-border focus:border-text focus:ring-1 focus:ring-text focus:outline-none"
          @keydown.enter.prevent="addItem"
          @keydown.backspace="handleBackspace"
          @paste="handlePaste"
          @blur="handleBlur"
        >
        <button
          type="button"
          class="px-4 py-2 text-sm font-medium transition-colors border rounded-md text-background bg-text border-text hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="!isNewItemValid"
          @click="addItem"
        >
          {{ $t('dynamicList.addBtn') }}
        </button>
      </div>

<div class="min-h-[20px] px-1">
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0 -translate-y-1"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-1"
  >
    <span 
      v-if="errorMsg" 
      id="dynamic-list-error"
      role="alert"
      class="block text-xs text-red-500 transform transition-all"
    >
      {{ errorMsg }}
    </span>
  </Transition>
</div>
    </div>

    <TransitionGroup
      v-if="modelValue.length > 0"
      tag="ul"
      aria-live="polite"
      class="flex flex-wrap gap-2"
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 scale-90"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-100 ease-in absolute"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-90"
      move-class="transition duration-150 ease-out"
    >
<li
  v-for="(item, index) in modelValue"
  :key="`${item}-${index}`"
  tabindex="0"
  class="flex items-center gap-2 pl-3 pr-2 py-1.5 text-sm border rounded-full text-text bg-background border-border cursor-pointer hover:border-text focus:outline-none focus:ring-2 focus:ring-text transition-colors group"
  :title="`${item} — ${$t('dynamicList.dblClickToEdit')}`"
  @dblclick="editItem(index)"
  @keydown.enter.prevent="editItem(index)"
>
  <span class="truncate max-w-[150px] sm:max-w-[250px] md:max-w-[350px]">
    {{ item }}
  </span>
  
  </li>
    </TransitionGroup>

    <div v-else class="text-sm text-additional">
      {{ $t('dynamicList.emptyState') }}
    </div>
  </div>
</template>
