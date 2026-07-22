<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
  description: { type: String, default: null },
})
</script>

<template>
  <div class="flex flex-col gap-4">
    <h2 class="text-xl font-bold text-text">{{ t('profiles.aboutUs') }}</h2>
    
    <div 
      v-if="description" 
      class="text-gray-600 leading-relaxed whitespace-pre-line text-sm sm:text-base wrap-break-word"
    >
      {{ description }}
    </div>
    <div v-else class="text-gray-400 italic text-sm">
      {{ t('profiles.company.noDescription') }}
    </div>
  </div>
</template>
