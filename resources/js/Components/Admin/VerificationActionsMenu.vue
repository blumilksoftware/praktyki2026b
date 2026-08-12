<script setup>
import { useI18n } from 'vue-i18n'
import { IconDotsVertical, IconInfoCircle, IconCheck, IconX } from '@tabler/icons-vue'

const { t } = useI18n()

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  isOpen: {
    type: Boolean,
    default: false,
  },
  processing: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['toggle', 'details', 'accept', 'reject'])
</script>

<template>
  <div class="relative inline-block text-left" data-verification-menu>
    <button
      type="button"
      class="p-1.5 rounded-md text-additional hover:bg-gray-100 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      :aria-label="t('admin.verification.actions')"
      @click="emit('toggle', props.item.id)"
    >
      <IconDotsVertical class="w-4 h-4" />
    </button>

    <div
      v-if="isOpen"
      class="absolute right-0 z-10 mt-1 w-44 rounded-lg border border-border bg-white shadow-lg py-1"
    >
      <button
        type="button"
        class="flex items-center gap-2 w-full px-3 py-2 text-left text-text hover:bg-gray-50 cursor-pointer"
        :aria-label="t('admin.verification.detailsAriaLabel', { name: item.name })"
        @click="emit('details', $event)"
      >
        <IconInfoCircle class="w-4 h-4" />
        {{ t('admin.verification.details') }}
      </button>

      <button
        v-if="item.verification_status === 'pending'"
        type="button"
        :disabled="processing"
        class="flex items-center gap-2 w-full px-3 py-2 text-left text-green-700 hover:bg-green-50 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
        :aria-label="t('admin.verification.acceptAriaLabel', { name: item.name })"
        @click="emit('accept')"
      >
        <IconCheck class="w-4 h-4" />
        {{ t('admin.verification.accept') }}
      </button>

      <button
        v-if="item.verification_status === 'pending'"
        type="button"
        :disabled="processing"
        class="flex items-center gap-2 w-full px-3 py-2 text-left text-red-600 hover:bg-red-50 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
        :aria-label="t('admin.verification.rejectAriaLabel', { name: item.name })"
        @click="emit('reject', $event)"
      >
        <IconX class="w-4 h-4" />
        {{ t('admin.verification.reject') }}
      </button>
    </div>
  </div>
</template>
