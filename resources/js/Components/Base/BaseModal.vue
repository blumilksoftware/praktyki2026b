<script setup>
import { nextTick, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  open: { type: Boolean, required: true },
  title: { type: String, required: true },
  maxWidthClass: { type: String, default: 'max-w-2xl' },
})

const emit = defineEmits(['close'])
const { t } = useI18n()

const dialog = ref(null)
let elementBeforeOpen = null

function focusableElements() {
  return Array.from(dialog.value?.querySelectorAll(
    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
  ) ?? [])
}

function trapTab(event) {
  const elements = focusableElements()

  if (elements.length === 0) {
    event.preventDefault()
    return
  }

  const first = elements[0]
  const last = elements[elements.length - 1]
  const active = document.activeElement

  if (event.shiftKey && (active === first || active === dialog.value)) {
    event.preventDefault()
    last.focus()
    return
  }

  if (!event.shiftKey && active === last) {
    event.preventDefault()
    first.focus()
  }
}

function onKeydown(event) {
  if (event.key === 'Escape') {
    emit('close')
    return
  }

  if (event.key === 'Tab') {
    trapTab(event)
  }
}

watch(() => props.open, (open) => {
  if (open) {
    elementBeforeOpen = document.activeElement
    nextTick(() => dialog.value?.focus())
    return
  }

  elementBeforeOpen?.focus()
  elementBeforeOpen = null
}, { immediate: true })
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      ref="dialog"
      class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm focus:outline-none"
      role="dialog"
      aria-modal="true"
      tabindex="-1"
      :aria-label="title"
      @click.self="emit('close')"
      @keydown="onKeydown"
    >
      <div
        class="max-h-[calc(100vh-2rem)] w-full min-w-0 overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl"
        :class="maxWidthClass"
      >
        <div class="mb-4 flex items-start justify-between gap-4">
          <h2 class="font-semibold text-text text-xl">
            {{ title }}
          </h2>
          <button
            type="button"
            class="flex h-9 w-9 items-center justify-center rounded-lg text-2xl leading-none text-additional hover:bg-background hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
            :aria-label="t('student.profile.modal.close')"
            @click="emit('close')"
          >
            ×
          </button>
        </div>

        <slot />
      </div>
    </div>
  </Teleport>
</template>
