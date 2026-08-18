<script setup>
import { useI18n } from 'vue-i18n'

defineProps({
  open: { type: Boolean, required: true },
  title: { type: String, required: true },
  maxWidthClass: { type: String, default: 'max-w-2xl' },
})

const emit = defineEmits(['close'])
const { t } = useI18n()
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
      role="dialog"
      aria-modal="true"
      :aria-label="title"
      @click.self="emit('close')"
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
