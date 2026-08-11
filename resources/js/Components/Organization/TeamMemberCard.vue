<script setup>
import { computed } from 'vue'
import { IconX } from '@tabler/icons-vue'
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
})
const emit = defineEmits(['remove'])
const memberInitial = computed(() => props.member.name?.charAt(0) || 'U')
</script>

<template>
  <article class="group overflow-hidden rounded-3xl border border-border bg-white shadow-[0_8px_30px_rgba(11,26,48,0.08)] transition hover:-translate-y-0.5 hover:shadow-[0_16px_45px_rgba(11,26,48,0.14)]">
    <div class="p-5">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex flex-1 gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-background text-sm font-semibold text-additional">
            {{ memberInitial }}
          </div>
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
          v-if="props.canRemove"
          type="button"
          class="inline-flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-lg text-additional transition hover:bg-background hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
          :aria-label="props.removeLabel"
          @click="emit('remove')"
        >
          <IconX class="h-4 w-4" aria-hidden="true" />
        </button>
      </div>
      <div class="mt-2 flex flex-wrap items-center justify-between gap-2 pl-[52px]">
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
