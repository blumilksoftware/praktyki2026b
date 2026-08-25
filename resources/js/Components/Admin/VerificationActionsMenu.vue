<script setup>
import { useI18n } from 'vue-i18n'
import { IconCheck, IconX, IconTrash } from '@tabler/icons-vue'

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

const emit = defineEmits(['accept', 'reject', 'delete'])
</script>

<template>
  <div class="flex items-center justify-end gap-1">
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

    <button
      type="button"
      class="p-1.5 rounded-md text-red-600 hover:bg-red-50 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      :title="t('admin.verification.delete')"
      :aria-label="t('admin.verification.deleteAriaLabel', { name: props.item.name })"
      @click="emit('delete', $event)"
    >
      <IconTrash class="w-4 h-4" aria-hidden="true" />
    </button>
  </div>
</template>
