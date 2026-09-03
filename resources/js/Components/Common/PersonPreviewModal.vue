<script setup>
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseModal from '@/Components/Base/BaseModal.vue'

defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    default: null,
  },
  subtitle: {
    type: String,
    default: null,
  },
  details: {
    type: Array,
    default: () => [],
  },
  closeLabel: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['close'])
</script>

<template>
  <BaseModal
    :open="open"
    :title="title"
    @close="emit('close')"
  >
    <div v-if="name" class="flex flex-col gap-5">
      <div class="flex items-center gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-background text-lg font-semibold text-additional">
          {{ name.charAt(0) || 'U' }}
        </div>
        <div class="min-w-0">
          <p class="truncate text-lg font-semibold text-text">
            {{ name }}
          </p>
          <p v-if="subtitle" class="text-sm text-additional">
            {{ subtitle }}
          </p>
        </div>
      </div>

      <dl class="space-y-3">
        <div v-for="detail in details" :key="detail.label">
          <dt class="text-xs font-medium uppercase tracking-wide text-additional">
            {{ detail.label }}
          </dt>
          <dd class="mt-1 text-sm text-text">
            {{ detail.value }}
          </dd>
        </div>
      </dl>

      <div class="flex justify-end">
        <BaseButton type="button" variant="secondary" @click="emit('close')">
          {{ closeLabel }}
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>
