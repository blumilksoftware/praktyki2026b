<script setup>
defineProps({
  centered: { type: Boolean, default: false },
  compact: { type: Boolean, default: false },
})
</script>

<template>
  <section
    class="rounded-xl border border-secondary/20 bg-white shadow-sm"
    :class="[
      compact ? 'p-5' : 'p-6 sm:p-8',
      centered ? 'flex flex-col items-center text-center' : '',
    ]"
  >
    <slot />
  </section>
</template>
