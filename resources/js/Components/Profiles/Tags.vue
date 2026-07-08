<script setup>
import { useI18n } from 'vue-i18n'
import { ref, computed } from 'vue'

const { t } = useI18n()

const props = defineProps({
  tags: { type: Array, default: () => [] },
})

const limit = 6
const isExpanded = ref(false)

const visibleTags = computed(() => {
  if (!props.tags) return []
  if (isExpanded.value || props.tags.length <= limit) {
    return props.tags
  }
  return props.tags.slice(0, limit)
})

const hasMore = computed(() => props.tags && props.tags.length > limit)
</script>

<template>
  <div v-if="tags && tags.length > 0" class="flex flex-col items-center mt-6">
    <div class="flex flex-wrap items-center justify-center gap-3">
      <span 
        v-for="(tag, index) in visibleTags" 
        :key="index"
        class="px-5 py-1.5 text-[13px] sm:text-sm font-semibold text-tag-text bg-tag border border-secondary rounded-full transition-all"
      >
        {{ tag }}
      </span>

      <button
        v-if="hasMore && !isExpanded"
        class="px-5 py-1.5 text-[13px] sm:text-sm font-semibold text-tag bg-tag-text border border-secondary rounded-full hover:bg-tag-text/90 transition-colors shadow-sm cursor-pointer"
        @click="isExpanded = true"
      >
        {{ t('buttons.showAll') }} ({{ tags.length }})
      </button>
    </div>

    <button
      v-if="hasMore && isExpanded"
      class="mt-5 text-xs font-bold text-gray-400 hover:text-gray-600 uppercase tracking-wider transition-colors"
      @click="isExpanded = false"
    >
      {{ t('buttons.collapseTags') }}
    </button>
  </div>
</template>
