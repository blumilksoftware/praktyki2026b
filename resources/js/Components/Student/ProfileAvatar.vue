<script setup>
import { computed } from 'vue'

const props = defineProps({
  photoUrl: { type: String, default: null },
  firstName: { type: String, default: '' },
  lastName: { type: String, default: '' },
  sizeClass: { type: String, default: 'w-24 h-24 text-2xl' },
  colorClass: { type: String, default: 'bg-primary/10 text-primary' },
})

const initials = computed(() => {
  const a = props.firstName?.[0] ?? ''
  const b = props.lastName?.[0] ?? ''
  return (a + b).toUpperCase() || '?'
})
</script>

<template>
  <div
    class="flex shrink-0 items-center justify-center overflow-hidden rounded-full font-semibold"
    :class="[sizeClass, colorClass]"
  >
    <img
      v-if="photoUrl"
      :src="photoUrl"
      alt=""
      class="h-full w-full object-cover"
    >
    <span v-else>{{ initials }}</span>
  </div>
</template>
