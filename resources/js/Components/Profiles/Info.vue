<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
  domain: {
    type: String,
    default: null,
  },
  externalFormUrl: {
    type: String,
    default: null,
  },
})
</script>

<template>
  <div class="flex flex-col gap-6">
    <div v-if="domain" class="flex flex-col gap-1">
      <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
        {{ t('common.fields.domain') }}
      </span>
      <span class="text-gray-800 font-medium">
        {{ domain }}
      </span>
    </div>

    <div v-if="externalFormUrl" class="flex flex-col gap-1.5">
      <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
        {{ t('common.fields.externalFormUrl') }}
      </span>
      <a 
        :href="externalFormUrl" 
        target="_blank" 
        rel="noopener noreferrer"
        class="text-link hover:text-link/80 font-medium underline underline-offset-4 transition-colors break-words"
      >
        {{ externalFormUrl }}
      </a>
    </div>
  </div>
</template>
