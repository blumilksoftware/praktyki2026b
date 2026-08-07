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
  showStatusAction: {
    type: Boolean,
    default: true,
  },
  labels: {
    type: Object,
    default: () => ({
      menu: 'company.dashboard.offers.actions.menu',
      edit: 'company.dashboard.offers.actions.edit',
      activate: 'company.dashboard.offers.actions.activate',
      deactivate: 'company.dashboard.offers.actions.deactivate',
      delete: 'company.dashboard.offers.actions.delete',
    }),
  },
})

const emit = defineEmits(['toggle', 'edit', 'toggle-status', 'delete'])
</script>

<template>
  <div class="relative inline-flex items-center text-left" :data-offer-menu="offer.id">
    <button
      type="button"
      class="flex h-9 w-9 items-center justify-center rounded-lg text-additional hover:bg-gray-100 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      :aria-label="t(props.labels.menu)"
      @click="emit('toggle', props.offer.id)"
    >
      <IconDotsVertical class="w-5 h-5" />
    </button>

    <div
      v-if="isOpen"
      class="absolute right-full top-0 z-50 mt-1 w-40 rounded-lg border border-border bg-white shadow-lg py-1"
      role="menu"
    >
      <button
        type="button"
        class="flex items-center gap-2 w-full px-3 py-2 text-left"
        :class="offer.status === 'closed' || offer.status === 'expired'
          ? 'text-gray-400 cursor-not-allowed'
          : 'text-text hover:bg-gray-50 cursor-pointer'"
        :disabled="offer.status === 'closed' || offer.status === 'expired'"
        @click="emit('edit', props.offer)"
      >
        <IconPencil class="w-4 h-4" />
        {{ t(props.labels.edit) }}
      </button>

      <button
        v-if="showStatusAction"
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
            ? t(props.labels.deactivate)
            : t(props.labels.activate)
        }}
      </button>

      <button
        type="button"
        class="flex items-center gap-2 w-full px-3 py-2 text-left text-red-600 hover:bg-red-50 cursor-pointer"
        @click="emit('delete', props.offer)"
      >
        <IconTrash class="w-4 h-4" />
        {{ t(props.labels.delete) }}
      </button>
    </div>
  </div>
</template>
