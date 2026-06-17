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
    class="rounded-2xl bg-white/20 backdrop-blur-sm shadow-lg ring-1 ring-black/5 border-t-4 p-5 flex flex-col items-center text-center"
    :class="accent"
  >
    <div class="text-xs uppercase tracking-wide text-slate-500">{{ label }}</div>
    <div class="text-3xl font-bold text-text mt-2">{{ value }}</div>
  </article>
</template>
