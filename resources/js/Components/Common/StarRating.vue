<script setup>
import { IconStarFilled, IconStar } from '@tabler/icons-vue'

defineProps({
  rating: { type: Number, required: true },
  interactive: { type: Boolean, default: false },
})

const emit = defineEmits(['update:rating'])

const stars = [1, 2, 3, 4, 5]
</script>

<template>
  <div
    class="flex items-center gap-0.5"
    v-bind="interactive ? {} : { role: 'img', 'aria-label': `${rating} / 5` }"
  >
    <component
      :is="interactive ? 'button' : 'span'"
      v-for="star in stars"
      :key="star"
      :type="interactive ? 'button' : undefined"
      :aria-label="interactive ? `${star} / 5` : undefined"
      :aria-pressed="interactive ? star <= rating : undefined"
      class="text-amber-400"
      :class="interactive ? 'cursor-pointer hover:scale-110 transition-transform' : ''"
      @click="interactive && emit('update:rating', star)"
    >
      <IconStarFilled v-if="star <= rating" class="h-5 w-5" aria-hidden="true" />
      <IconStar v-else class="h-5 w-5" aria-hidden="true" />
    </component>
  </div>
</template>
