<script setup>
import { useI18n } from 'vue-i18n'
import {
  IconDotsVertical,
  IconPencil,
  IconPlayerPause,
  IconPlayerPlay,
  IconTrash,
} from '@tabler/icons-vue'

const { t } = useI18n()

const props = defineProps({
  offer: {
    type: Object,
    required: true,
  },
  isOpen: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['toggle', 'edit', 'toggle-status', 'delete'])
</script>

<template>
  <div class="relative inline-block text-left" data-offer-menu>
    <button
      type="button"
      class="p-1.5 rounded-md text-additional hover:bg-gray-100 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      :aria-label="t('common.actions.openActionMenu')"
      @click="emit('toggle', props.offer.id)"
    >
      <IconDotsVertical class="w-4 h-4" />
    </button>

    <div
      v-if="isOpen"
      class="absolute right-0 z-50 mt-1 w-40 rounded-lg border border-border bg-white shadow-lg py-1"
    >
      <button
        type="button"
        class="flex items-center gap-2 w-full px-3 py-2 text-left"
        :class="offer.status === 'closed'
          ? 'text-gray-400 cursor-not-allowed'
          : 'text-text hover:bg-gray-50 cursor-pointer'"
        :disabled="offer.status === 'closed'"
        @click="emit('edit', props.offer)"
      >
        <IconPencil class="w-4 h-4" />
        {{ t('common.actions.edit') }}
      </button>

      <button
        type="button"
        class="flex items-center gap-2 w-full px-3 py-2 text-left"
        :class="offer.status === 'closed'
          ? 'text-gray-400 cursor-not-allowed'
          : 'text-text hover:bg-gray-50 cursor-pointer'"
        :disabled="offer.status === 'closed'"
        @click="emit('toggle-status', props.offer)"
      >
        <IconPlayerPause v-if="offer.status === 'published'" class="w-4 h-4" />
        <IconPlayerPlay v-else class="w-4 h-4" />

        {{
          offer.status === 'published'
            ? t('common.actions.deactivate')
            : t('common.actions.activate')
        }}
      </button>

      <button
        type="button"
        class="flex items-center gap-2 w-full px-3 py-2 text-left text-red-600 hover:bg-red-50 cursor-pointer"
        @click="emit('delete', props.offer)"
      >
        <IconTrash class="w-4 h-4" />
        {{ t('common.actions.delete') }}
      </button>
    </div>
  </div>
</template>
