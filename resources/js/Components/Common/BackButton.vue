<script setup>
import { useI18n } from 'vue-i18n'
import { IconArrowLeft } from '@tabler/icons-vue'

const { t } = useI18n()

const props = defineProps({
  as: { type: String, default: 'button' },
})
</script>

<template>
  <component
    :is="as"
    v-bind="$attrs"
    class="inline-flex items-center gap-2 rounded text-additional text-sm transition hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 cursor-pointer"
  >
    <IconArrowLeft stroke="2.5" class="w-4 h-4" aria-hidden="true" />
    {{ t('buttons.back') }}
  </component>
</template>
