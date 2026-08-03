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
  <div class="absolute top-4 left-4 bg-white/95 backdrop-blur px-4 py-2 rounded-2xl border border-border shadow-md flex items-center gap-3 z-10">
    <span class="text-sm font-semibold text-text">
      {{ city }} ({{ count }})
    </span>
    <button
      type="button"
      class="text-xs text-additional hover:text-text font-medium underline cursor-pointer"
      @click="$emit('clear')"
    >
      {{ t('student.offers.map.showAll') }}
    </button>
  </div>
</template>
