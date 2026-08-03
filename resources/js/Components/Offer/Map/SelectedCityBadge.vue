<script setup>
import { useI18n } from 'vue-i18n'

defineProps({
  city: { type: String, required: true },
  count: { type: Number, required: true },
})

defineEmits(['clear'])

const { t } = useI18n()
</script>

<template>
  <div class="absolute top-4 left-4 bg-card/95 backdrop-blur px-4 py-2.5 rounded-2xl border border-border shadow-md flex items-center gap-3 z-10 transition-all">
    <div class="flex items-center gap-2">
      <span class="text-sm font-semibold text-text">
        {{ city }}
      </span>
      <span class="text-xs font-medium bg-primary/10 text-primary px-2 py-0.5 rounded-full">
        {{ count }}
      </span>
    </div>

    <div class="h-3.5 w-[1px] bg-border" />

    <button
      type="button"
      class="text-xs text-additional hover:text-text font-medium transition-colors cursor-pointer flex items-center gap-1"
      @click="$emit('clear')"
    >
      {{ t('student.offers.map.showAll') }}
    </button>
  </div>
</template>
