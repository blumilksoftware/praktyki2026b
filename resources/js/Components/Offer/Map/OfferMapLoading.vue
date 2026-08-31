<script setup>
import { IconLoader2 } from '@tabler/icons-vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
  <div class="space-y-4 py-4 sm:px-4 sm:py-6">
    <div class="flex items-center justify-center gap-2 w-full h-[450px] rounded-3xl overflow-hidden border border-border shadow-sm text-additional text-sm">
      <IconLoader2 class="w-5 h-5 animate-spin" aria-hidden="true" />
      {{ t('student.offers.map.loading') }}
    </div>
  </div>
</template>
