<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconCircleCheckFilled } from '@tabler/icons-vue'

const props = defineProps({
  verified: { type: Boolean, default: false },
  label: { type: String, default: null },
  size: { type: String, default: 'sm' },
})

const { t } = useI18n()

const accessibleLabel = computed(() => props.label ?? t('common.verifiedBadge'))
const iconClass = computed(() => props.size === 'md' ? 'h-5 w-5' : 'h-4 w-4')
</script>

<template>
  <span
    v-if="verified"
    class="inline-flex shrink-0 items-center text-primary"
    role="img"
    :aria-label="accessibleLabel"
    :title="accessibleLabel"
  >
    <IconCircleCheckFilled :class="iconClass" aria-hidden="true" />
  </span>
</template>
