<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import BaseNavigationButton from './BaseNavigationButton.vue'

const page = usePage()

const props = defineProps({
  showButtons: {
    type: Boolean,
    default: false,
  },
  buttons: {
    type: Array,
    default: () => [],
    validator: (value) => {
      return value.every(item => 
        item.label && 
        typeof item.label === 'string' &&
        (item.href === undefined || typeof item.href === 'string'),
      )
    },
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'outline', 'ghost'].includes(value),
  },
})

const emit = defineEmits(['buttonClick'])

const handleButtonClick = (item) => {
  emit('buttonClick', item)
}

const currentPath = computed(() => page.url)

const processedButtons = computed(() => {
  return props.buttons.map(item => ({
    ...item,
    isActive: item.isActive !== undefined 
      ? item.isActive 
      : (item.href ? currentPath.value === item.href : false),
  }))
})
</script>

<template>
  <div v-if="showButtons" class="flex items-center gap-1 sm:gap-2 h-full">
    <BaseNavigationButton 
      v-for="item in buttons" 
      :key="item.id || item.label"
      :label="item.label"
      :icon="item.icon"
      :href="item.href"
      :is-active="item.isActive"
      :variant="item.variant || 'default'"
      @click="handleButtonClick(item)"
    />
  </div>
</template>
