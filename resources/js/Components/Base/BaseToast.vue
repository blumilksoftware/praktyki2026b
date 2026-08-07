<script setup lang="ts">
import { computed, ref, onBeforeUnmount } from 'vue'

const message = ref('')
const visible = ref(false)
const timeoutId = ref<number | null>(null)
type ToastVariant = 'success' | 'fail' | 'warning' | 'info'
const variant = ref<ToastVariant>('success')

const clearTimeoutIfSet = () => {
  if (timeoutId.value !== null) {
    clearTimeout(timeoutId.value)
    timeoutId.value = null
  }
}

const toastClasses = computed(() => {
  return {
    success: 'bg-green-100 border border-green-200 text-green-900',
    fail: 'bg-red-100 border border-red-200 text-red-900',
    warning: 'bg-amber-50 border border-amber-200 text-amber-900',
    info: 'bg-slate-100 border border-slate-200 text-slate-900',
  }[variant.value]
})

const buttonClasses = computed(() => {
  return {
    success: 'text-green-700/80 hover:text-green-900 focus:ring-green-300',
    fail: 'text-red-700/80 hover:text-red-900 focus:ring-red-300',
    warning: 'text-amber-700/80 hover:text-amber-900 focus:ring-amber-300',
    info: 'text-slate-700/80 hover:text-slate-900 focus:ring-slate-300',
  }[variant.value]
})

function show(text: string, duration = 3000, nextVariant: ToastVariant = 'success') {
  clearTimeoutIfSet()
  message.value = text
  variant.value = nextVariant
  visible.value = true
  timeoutId.value = window.setTimeout(() => {
    visible.value = false
    timeoutId.value = null
  }, duration)
}

function hide() {
  clearTimeoutIfSet()
  visible.value = false
}

onBeforeUnmount(() => {
  clearTimeoutIfSet()
})

defineExpose({ show, hide })
</script>

<template>
  <div
    v-if="visible"
    :class="[`m-4 right-4 top-20 z-50 fixed shadow-sm px-4 py-3 rounded-xl`, toastClasses]"
  >
    <button
      type="button"
      aria-label="Close notification"
      :class="[`absolute right-2 top-2 cursor-pointer focus:outline-none focus:ring-2 rounded`, buttonClasses]"
      @click="hide"
    >
      ×
    </button>

    <div class="pr-6">
      {{ message }}
    </div>
  </div>
</template>
