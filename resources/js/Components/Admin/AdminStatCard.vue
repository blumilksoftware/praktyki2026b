<script setup>
defineProps({
  label: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  accent: {
    type: String,
    default: 'border-t-primary',
  },
})
</script>

<template>
  <article
    class="flex flex-col items-center bg-white/20 shadow-lg backdrop-blur-sm p-5 border-t-4 rounded-2xl ring-1 ring-black/5 text-center"
    :class="accent"
  >
    <div class="text-slate-700 text-xs uppercase tracking-wide">{{ label }}</div>
    <div class="mt-2 font-bold text-text text-3xl">{{ value }}</div>
  </article>
</template>
