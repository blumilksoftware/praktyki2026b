<script setup>
defineProps({
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' },
  disabled: { type: Boolean, default: false },
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    class="w-fit rounded-lg px-4 py-3 text-sm font-semibold cursor-pointer tracking-wide transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
    :class="variant === 'primary'
      ? 'bg-primary text-white hover:bg-primary/90'
      : 'border border-border bg-white text-text hover:bg-secondary'"
  >
    <slot />
  </button>
</template>
