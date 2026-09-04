<script setup>
import { useI18n } from 'vue-i18n'
import { IconCheck, IconX, IconTrash } from '@tabler/icons-vue'
import AdminActionsMenu from '@/Components/Admin/AdminActionsMenu.vue'

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
  <AdminActionsMenu :label="t('admin.verification.actionsMenu', { name: props.item.name })">
    <button
      v-if="props.item.verification_status === 'pending'"
      type="button"
      :disabled="processing"
      class="flex items-center gap-2 w-full px-3 py-2 text-left text-green-700 hover:bg-green-50 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
      role="menuitem"
      @click="emit('accept')"
    >
      <IconCheck class="w-4 h-4" aria-hidden="true" />
      {{ t('admin.verification.accept') }}
    </button>

    <button
      v-if="props.item.verification_status === 'pending'"
      type="button"
      :disabled="processing"
      class="flex items-center gap-2 w-full px-3 py-2 text-left text-red-600 hover:bg-red-50 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
      role="menuitem"
      @click="emit('reject', $event)"
    >
      <IconX class="w-4 h-4" aria-hidden="true" />
      {{ t('admin.verification.reject') }}
    </button>

    <button
      type="button"
      class="flex items-center gap-2 w-full px-3 py-2 text-left text-red-600 hover:bg-red-50 cursor-pointer"
      role="menuitem"
      @click="emit('delete', $event)"
    >
      <IconTrash class="w-4 h-4" aria-hidden="true" />
      {{ t('admin.verification.delete') }}
    </button>
  </AdminActionsMenu>
</template>
