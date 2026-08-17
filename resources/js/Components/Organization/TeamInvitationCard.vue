<script setup>
import { IconTrash } from '@tabler/icons-vue'

const props = defineProps({
  invitation: {
    type: Object,
    required: true,
  },
  statusLabel: {
    type: String,
    required: true,
  },
  revokeLabel: {
    type: String,
    required: true,
  },
  canRevoke: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['revoke'])
</script>

<template>
  <article class="group overflow-hidden rounded-3xl border border-border bg-white shadow-[0_4px_16px_rgba(11,26,48,0.04)] transition">
    <div class="p-5">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex flex-1 items-center gap-3">
          <div class="min-w-0 flex flex-wrap items-center gap-2">
            <span
              class="inline-flex w-fit shrink-0 items-center rounded-full border border-border bg-background px-2.5 py-1 text-xs font-medium text-additional sm:text-sm"
              :title="props.statusLabel"
            >
              {{ props.statusLabel }}
            </span>
            <h3 class="min-w-0 break-all text-lg font-semibold tracking-tight text-text sm:text-2xl">
              {{ props.invitation.email }}
            </h3>
          </div>
        </div>
        <button
          v-if="props.canRevoke"
          type="button"
          class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
          :aria-label="props.revokeLabel"
          @click="emit('revoke')"
        >
          <IconTrash class="h-4 w-4" aria-hidden="true" />
          <span>{{ props.revokeLabel }}</span>
        </button>
      </div>
    </div>
  </article>
</template>
