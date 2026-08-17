<script setup>
import { useI18n } from 'vue-i18n'
import { IconInfoCircle, IconCheck, IconX } from '@tabler/icons-vue'

const { t } = useI18n()

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  processing: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['details', 'accept', 'reject'])
</script>

<template>
  <div class="flex items-center justify-end gap-1">
    <button
      type="button"
      class="p-1.5 rounded-md text-additional hover:bg-gray-100 hover:text-text cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      :title="t('admin.verification.details')"
      :aria-label="t('admin.verification.detailsAriaLabel', { name: props.item.name })"
      @click="emit('details', $event)"
    >
      <IconInfoCircle class="w-4 h-4" aria-hidden="true" />
    </button>

    <button
      v-if="props.item.verification_status === 'pending'"
      type="button"
      :disabled="processing"
      class="p-1.5 rounded-md text-green-700 hover:bg-green-50 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:opacity-50 disabled:cursor-not-allowed"
      :title="t('admin.verification.accept')"
      :aria-label="t('admin.verification.acceptAriaLabel', { name: props.item.name })"
      @click="emit('accept')"
    >
      <IconCheck class="w-4 h-4" aria-hidden="true" />
    </button>

    <button
      v-if="props.item.verification_status === 'pending'"
      type="button"
      :disabled="processing"
      class="p-1.5 rounded-md text-red-600 hover:bg-red-50 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:opacity-50 disabled:cursor-not-allowed"
      :title="t('admin.verification.reject')"
      :aria-label="t('admin.verification.rejectAriaLabel', { name: props.item.name })"
      @click="emit('reject', $event)"
    >
      <IconX class="w-4 h-4" aria-hidden="true" />
    </button>
  </div>
</template>
