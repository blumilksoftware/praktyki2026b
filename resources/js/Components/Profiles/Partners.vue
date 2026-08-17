<script setup>
import { useI18n } from 'vue-i18n'
import { IconSchool } from '@tabler/icons-vue'

const { t } = useI18n()

defineProps({
  partners: { type: Array, default: () => [] },
})
</script>

<template>
  <div class="flex flex-col gap-4">
    <h2 class="text-xl font-bold text-text">{{ t('profiles.partners.title') }}</h2>

    <div class="flex flex-wrap gap-2">
      <span
        v-for="partner in partners"
        :key="partner.id"
        class="inline-flex items-center gap-1.5 rounded-full border border-border bg-background px-3 py-1.5 text-sm text-text"
      >
        <IconSchool class="h-4 w-4 text-additional" aria-hidden="true" />
        {{ partner.name }}
      </span>
    </div>
  </div>
</template>
