<script setup>
import { computed } from 'vue'
import { IconArrowsExchange, IconTrash } from '@tabler/icons-vue'

const props = defineProps({
  member: {
    type: Object,
    required: true,
  },
  roleLabel: {
    type: String,
    required: true,
  },
  joinDateLabel: {
    type: String,
    required: true,
  },
  removeLabel: {
    type: String,
    required: true,
  },
  canRemove: {
    type: Boolean,
    default: true,
  },
  disableRemove: {
    type: Boolean,
    default: false,
  },
  showTransfer: {
    type: Boolean,
    default: false,
  },
  transferLabel: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['remove', 'preview', 'transfer'])
const canRenderRemoveAction = computed(() => !props.showTransfer && (props.canRemove || props.disableRemove))
const cardClass = computed(() => (props.canRemove || props.disableRemove || props.showTransfer
  ? 'group cursor-pointer overflow-hidden rounded-3xl border border-border bg-white shadow-[0_8px_30px_rgba(11,26,48,0.08)] transition hover:-translate-y-0.5 hover:shadow-[0_16px_45px_rgba(11,26,48,0.14)]'
  : 'cursor-pointer overflow-hidden rounded-3xl border border-border bg-white shadow-[0_8px_30px_rgba(11,26,48,0.08)] transition'))

function handleRemove(event) {
  event.stopPropagation()

  if (props.disableRemove) {
    return
  }

  emit('remove')
}

function handleTransfer(event) {
  event.stopPropagation()

  emit('transfer')
}

function handlePreview(event) {
  if (event && event.target && event.target.closest('button')) {
    return
  }

  emit('preview')
}
</script>

<template>
  <article
    :class="cardClass"
    tabindex="0"
    role="region"
    @click="handlePreview"
    @keydown.enter.prevent="emit('preview')"
    @keydown.space.prevent="emit('preview')"
  >
    <div class="p-5">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex flex-1 items-center gap-3">
          <div class="min-w-0 flex flex-wrap items-center gap-2">
            <h3 class="min-w-0 break-words text-lg font-semibold tracking-tight text-text sm:text-2xl">
              {{ props.member.name }}
            </h3>
            <span class="inline-flex shrink-0 max-w-full items-center rounded-full border border-primary/20 bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary sm:text-sm">
              {{ props.roleLabel }}
            </span>
          </div>
        </div>
        <button
          v-if="showTransfer"
          type="button"
          class="inline-flex items-center gap-1.5 rounded-lg border border-primary/30 bg-primary/10 px-2 py-1.5 text-sm font-medium text-primary transition hover:bg-primary/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
          :aria-label="transferLabel"
          @click="handleTransfer"
        >
          <IconArrowsExchange class="h-4 w-4" aria-hidden="true" />
          <span>{{ transferLabel }}</span>
        </button>
        <button
          v-if="canRenderRemoveAction"
          type="button"
          :disabled="props.disableRemove"
          class="inline-flex items-center gap-1.5 rounded-lg border px-2 py-1.5 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
          :class="props.disableRemove
            ? 'cursor-not-allowed border-transparent bg-transparent text-additional opacity-55'
            : 'cursor-pointer border-red-200 bg-red-50 text-red-600 hover:bg-red-100'"
          :aria-label="props.removeLabel"
          :aria-disabled="props.disableRemove ? true : undefined"
          @click="handleRemove"
        >
          <IconTrash class="h-4 w-4" aria-hidden="true" />
          <span>{{ props.removeLabel }}</span>
        </button>
      </div>
      <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
        <p class="min-w-0 break-all text-sm font-semibold text-additional">
          {{ props.member.email }}
        </p>
        <span class="shrink-0 text-right text-sm text-additional">
          {{ props.joinDateLabel }}
        </span>
      </div>
    </div>
  </article>
</template>
